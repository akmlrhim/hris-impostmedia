<?php

namespace App\Livewire\Employee;

use App\Enums\OvertimeStatus;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
#[Title('Pengajuan Lembur')]
class Overtime extends Component
{
    use WithFileUploads;

    public string $overtime_date = '';

    public string $start_time = '';

    public string $end_time = '';

    public string $reason = '';

    public ?TemporaryUploadedFile $head_approval = null;

    public string $work_documentation = '';

    public bool $showForm = false;

    public bool $showConfirm = false;

    public bool $showDocForm = false;

    public ?int $documentingId = null;

    /** List filter — empty string means "all". */
    public string $statusFilter = '';

    public function mount(): void
    {
        $this->resetSchedule();
    }

    private function resetSchedule(): void
    {
        $this->overtime_date = now()->toDateString();
        $this->start_time = '18:00';
        $this->end_time = '21:00';
    }

    public function openForm(): void
    {
        $this->reset(['reason', 'head_approval', 'work_documentation']);
        $this->resetSchedule();
        $this->resetValidation();
        $this->showForm = true;
        $this->showConfirm = false;
    }

    /**
     * Compose the stored datetime range from the date + time fields.
     * An end time at or before the start time means the overtime runs past
     * midnight, so it rolls over to the next day.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function resolveRange(): ?array
    {
        if (! $this->overtime_date || ! $this->start_time || ! $this->end_time) {
            return null;
        }

        try {
            $start = Carbon::parse($this->overtime_date.' '.$this->start_time);
            $end = Carbon::parse($this->overtime_date.' '.$this->end_time);
        } catch (\Throwable) {
            return null;
        }

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    /**
     * @return array<string, string>
     */
    private function validationRules(): array
    {
        return [
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|different:start_time',
            'reason' => 'required|string|min:10|max:500',
            'head_approval' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'work_documentation' => 'nullable|string|max:5000',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'overtime_date.required' => 'Tanggal lembur wajib diisi.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid.',
            'end_time.different' => 'Jam selesai tidak boleh sama dengan jam mulai.',
            'head_approval.required' => 'Bukti approve dari head wajib diunggah.',
            'head_approval.mimes' => 'Bukti approve harus berupa gambar (JPG/PNG/WEBP) atau PDF.',
            'head_approval.max' => 'Ukuran bukti approve maksimal 4 MB.',
        ];
    }

    private function validateForm(): void
    {
        $this->validate($this->validationRules(), $this->validationMessages());
    }

    public function requestConfirm(): void
    {
        $this->validateForm();

        $this->showConfirm = true;
    }

    public function submit(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            $this->dispatch('notify', type: 'error', message: 'Akun belum terhubung ke data karyawan.');

            return;
        }

        $this->validateForm();

        $range = $this->resolveRange();

        if ($range === null) {
            $this->dispatch('notify', type: 'error', message: 'Waktu lembur tidak valid.');

            return;
        }

        [$start, $end] = $range;

        $path = $this->head_approval->store('overtime-approvals', 'local');

        OvertimeRequest::create([
            'employee_id' => $employee->id,
            'started_at' => $start,
            'ended_at' => $end,
            'reason' => $this->reason,
            'head_approval_path' => $path,
            'work_documentation' => $this->work_documentation ?: null,
            'status' => OvertimeStatus::Pending,
        ]);

        $this->reset(['reason', 'head_approval', 'work_documentation']);
        $this->showForm = false;
        $this->showConfirm = false;
        $this->dispatch('notify', type: 'success', message: 'Pengajuan lembur berhasil dikirim.');
    }

    public function openDocForm(int $id): void
    {
        $request = $this->ownedRequest($id);

        if (! $request->documentationIsEditable()) {
            $this->dispatch('notify', type: 'warning', message: 'Dokumentasi pengajuan yang ditolak tidak bisa diubah.');

            return;
        }

        $this->documentingId = $request->id;
        $this->work_documentation = (string) $request->work_documentation;
        $this->resetValidation();
        $this->showDocForm = true;
    }

    /** Work documentation stays editable after HR approval. */
    public function saveDocumentation(): void
    {
        $this->validate(
            ['work_documentation' => 'required|string|min:5|max:5000'],
            ['work_documentation.required' => 'Dokumentasi kerja tidak boleh kosong.'],
        );

        $request = $this->ownedRequest((int) $this->documentingId);

        if (! $request->documentationIsEditable()) {
            $this->dispatch('notify', type: 'warning', message: 'Dokumentasi pengajuan yang ditolak tidak bisa diubah.');

            return;
        }

        $request->update(['work_documentation' => $this->work_documentation]);

        $this->showDocForm = false;
        $this->documentingId = null;
        $this->work_documentation = '';
        $this->dispatch('notify', type: 'success', message: 'Dokumentasi kerja tersimpan.');
    }

    public function cancel(int $id): void
    {
        $request = $this->ownedRequest($id);

        if ($request->status !== OvertimeStatus::Pending) {
            $this->dispatch('notify', type: 'warning', message: 'Pengajuan yang sudah diproses tidak bisa dibatalkan.');

            return;
        }

        if ($request->head_approval_path) {
            Storage::disk('local')->delete($request->head_approval_path);
        }

        $request->delete();
        $this->dispatch('notify', type: 'success', message: 'Pengajuan lembur dibatalkan.');
    }

    private function ownedRequest(int $id): OvertimeRequest
    {
        return OvertimeRequest::where('id', $id)
            ->where('employee_id', auth()->user()?->employee?->id)
            ->firstOrFail();
    }

    public function render(): mixed
    {
        $employee = auth()->user()?->employee;

        $requests = $employee
            ? OvertimeRequest::where('employee_id', $employee->id)
                ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
            : collect();

        $range = $this->resolveRange();

        return view('livewire.employee.overtime', [
            'requests' => $requests,
            'previewStart' => $range[0] ?? null,
            'previewEnd' => $range[1] ?? null,
            'statuses' => OvertimeStatus::cases(),
        ]);
    }
}
