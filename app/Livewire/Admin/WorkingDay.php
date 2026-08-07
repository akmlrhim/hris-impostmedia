<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Models\WorkingDay as WorkingDayModel;
use App\Services\WorkScheduleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Hari Kerja')]
#[Layout('components.layouts.admin')]
class WorkingDay extends Component
{
    use HandlesAdminActions;

    /**
     * ISO weekday => is working. Bound to the toggles so the whole week is
     * saved in one go rather than one round trip per day.
     *
     * @var array<int, bool>
     */
    public array $schedule = [];

    public function mount(): void
    {
        Gate::authorize('manage_holidays');

        $this->schedule = WorkingDayModel::query()
            ->orderBy('weekday')
            ->pluck('is_working', 'weekday')
            ->map(fn ($isWorking) => (bool) $isWorking)
            ->all();

        // Isi weekday yang belum pernah tersimpan supaya semua toggle tetap muncul.
        foreach (range(1, 7) as $weekday) {
            $this->schedule[$weekday] ??= $weekday !== 7;
        }

        ksort($this->schedule);
    }

    public function save(): void
    {
        $this->safeAction(function () {
            $working = array_keys(array_filter($this->schedule));

            if ($working === []) {
                $this->toast('warning', 'Minimal satu hari kerja harus dipilih.');

                return;
            }

            foreach ($this->schedule as $weekday => $isWorking) {
                WorkingDayModel::updateOrCreate(
                    ['weekday' => (int) $weekday],
                    ['is_working' => (bool) $isWorking],
                );
            }

            app(WorkScheduleService::class)->forgetCache();

            $this->logActivity(
                'working_days.updated',
                'Mengubah hari kerja perusahaan menjadi '.$this->summary(),
                null,
                ['working_weekdays' => array_values($working)],
            );

            $this->toast('success', 'Hari kerja diperbarui.');
        }, permission: 'manage_holidays', genericError: 'Gagal menyimpan hari kerja.');
    }

    /** Comma-separated weekday names, e.g. "Senin, Selasa, …, Sabtu". */
    public function summary(): string
    {
        $names = collect($this->schedule)
            ->filter()
            ->keys()
            ->map(fn ($weekday) => $this->weekdayName((int) $weekday));

        return $names->isEmpty() ? 'Belum ada hari kerja' : $names->implode(', ');
    }

    public function weekdayName(int $weekday): string
    {
        return Carbon::now()->startOfWeek()->addDays($weekday - 1)->translatedFormat('l');
    }

    public function render(): mixed
    {
        return view('livewire.admin.working-day', [
            'weekdays' => range(1, 7),
        ]);
    }
}
