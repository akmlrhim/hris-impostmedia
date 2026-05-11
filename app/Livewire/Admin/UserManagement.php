<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterRole = '';

    // Create / edit user form
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public bool $is_active = true;

    public string $password = '';

    public string $password_confirmation = '';

    // Change password form
    public bool $showPasswordForm = false;

    public ?int $passwordUserId = null;

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public function mount(): void
    {
        Gate::authorize('manage_users');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function open(?int $id = null): void
    {
        $this->reset(['name', 'email', 'role', 'is_active', 'password', 'password_confirmation', 'editingId']);
        $this->resetValidation();
        $this->is_active = true;
        $this->role = UserRole::Employee->value;

        if ($id) {
            $user = User::findOrFail($id);
            $this->editingId = $id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role?->value ?? UserRole::Employee->value;
            $this->is_active = $user->is_active;
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $isNew = ! $this->editingId;

        $rules = [
            'name' => 'required|string|max:200',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => 'boolean',
        ];

        if ($isNew) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $this->validate($rules);

        // Prevent demoting the last Admin
        if ($this->editingId && $this->role !== UserRole::Admin->value) {
            $current = User::find($this->editingId);
            if ($current?->role === UserRole::Admin) {
                $adminCount = User::where('role', UserRole::Admin->value)->count();
                if ($adminCount <= 1) {
                    $this->addError('role', 'Tidak dapat mengubah peran Admin terakhir.');

                    return;
                }
            }
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
        ];

        if ($isNew) {
            $data['password'] = Hash::make($this->password);
            User::create($data);
            $this->dispatch('notify', type: 'success', message: 'Pengguna berhasil dibuat.');
        } else {
            User::findOrFail($this->editingId)->update($data);
            $this->dispatch('notify', type: 'success', message: 'Pengguna berhasil diperbarui.');
        }

        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->dispatch('notify', type: 'warning', message: 'Tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->dispatch('notify', type: 'success', message: $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function openPasswordForm(int $id): void
    {
        $this->passwordUserId = $id;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->resetValidation();
        $this->showPasswordForm = true;
    }

    public function savePassword(): void
    {
        $this->validate([
            'newPassword' => 'required|string|min:8|confirmed:newPasswordConfirmation',
        ]);

        User::findOrFail($this->passwordUserId)->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->showPasswordForm = false;
        $this->dispatch('notify', type: 'success', message: 'Kata sandi berhasil diubah.');
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('notify', type: 'warning', message: 'Tidak dapat menghapus akun sendiri.');

            return;
        }

        $user = User::findOrFail($id);

        if ($user->role === UserRole::Admin && User::where('role', UserRole::Admin->value)->count() <= 1) {
            $this->dispatch('notify', type: 'warning', message: 'Tidak dapat menghapus Admin terakhir.');

            return;
        }

        $user->delete();
        $this->dispatch('notify', type: 'success', message: 'Pengguna dihapus.');
    }

    public function render(): mixed
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->with('employee:id,user_id,full_name,employee_number')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }
}
