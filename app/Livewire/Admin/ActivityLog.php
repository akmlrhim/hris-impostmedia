<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog as ActivityLogModel;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Log Aktivitas')]
#[Layout('components.layouts.admin')]
class ActivityLog extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $action = '';

    #[Url]
    public string $userId = '';

    public function mount(): void
    {
        Gate::authorize('manage_users');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAction(): void
    {
        $this->resetPage();
    }

    public function updatingUserId(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $logs = ActivityLogModel::query()
            ->with('user:id,name,email')
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->action, fn ($q) => $q->where('action', $this->action))
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->latest('created_at')
            ->paginate(30);

        $availableActions = ActivityLogModel::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $availableUsers = ActivityLogModel::query()
            ->with('user:id,name')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user')
            ->filter()
            ->sortBy('name')
            ->values();

        return view('livewire.admin.activity-log', compact('logs', 'availableActions', 'availableUsers'));
    }
}
