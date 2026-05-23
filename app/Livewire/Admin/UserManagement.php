<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Pengguna')]
#[Layout('components.layouts.admin')]
class UserManagement extends Component
{
    use HandlesAdminActions, WithPagination;

    public string $search = '';

    public string $filterRole = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    /** @var string[] */
    public array $selectedRoles = [];

    public bool $is_active = true;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $showPasswordForm = false;

    public ?int $passwordUserId = null;

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public bool $showLinkForm = false;

    public ?int $linkingUserId = null;

    public ?int $selectedEmployeeId = null;

    public string $employeeSearch = '';

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
        $this->reset(['name', 'email', 'selectedRoles', 'is_active', 'password', 'password_confirmation', 'editingId']);
        $this->resetValidation();
        $this->is_active = true;
        $this->selectedRoles = [UserRole::Employee->value];

        if ($id) {
            $user = User::findOrFail($id);
            $this->editingId = $id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->selectedRoles = $user->roles ?? [UserRole::Employee->value];
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
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => [Rule::enum(UserRole::class)],
            'is_active' => 'boolean',
        ];

        if ($isNew) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $this->validate($rules);

        // Cegah menghapus admin terakhir
        if ($this->editingId && ! in_array(UserRole::Admin->value, $this->selectedRoles, true)) {
            $current = User::find($this->editingId);
            if ($current?->hasRole(UserRole::Admin)) {
                $adminCount = User::whereJsonContains('roles', UserRole::Admin->value)->count();
                if ($adminCount <= 1) {
                    $this->addError('selectedRoles', 'Tidak dapat menghapus peran Admin dari satu-satunya admin.');

                    return;
                }
            }
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'roles' => array_values($this->selectedRoles),
            'is_active' => $this->is_active,
        ];

        $this->safeAction(function () use ($data, $isNew) {
            if ($isNew) {
                $data['password'] = Hash::make($this->password);
                $user = User::create($data);
                $this->logActivity('user.created', "Membuat pengguna {$user->name} ({$user->email})", $user, ['roles' => $user->roles]);
                $this->toast('success', 'Pengguna berhasil dibuat.');
            } else {
                $user = User::findOrFail($this->editingId);
                $user->update($data);
                $this->logActivity('user.updated', "Mengubah pengguna {$user->name}", $user, ['roles' => $user->roles, 'is_active' => $user->is_active]);
                $this->toast('success', 'Pengguna berhasil diperbarui.');
            }

            $this->showForm = false;
        }, permission: 'manage_users', genericError: 'Gagal menyimpan pengguna.');
    }

    public function toggleActive(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                $this->toast('warning', 'Tidak dapat menonaktifkan akun sendiri.');

                return;
            }

            $user->update(['is_active' => ! $user->is_active]);
            $this->logActivity(
                $user->is_active ? 'user.activated' : 'user.deactivated',
                ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan')." akun {$user->name}",
                $user,
            );
            $this->toast('success', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
        }, permission: 'manage_users', genericError: 'Gagal mengubah status pengguna.');
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

        $this->safeAction(function () {
            $user = User::findOrFail($this->passwordUserId);
            $user->update(['password' => Hash::make($this->newPassword)]);

            $this->logActivity('user.password_changed', "Mengubah kata sandi pengguna {$user->name}", $user);
            $this->showPasswordForm = false;
            $this->toast('success', 'Kata sandi berhasil diubah.');
        }, permission: 'manage_users', genericError: 'Gagal mengubah kata sandi.');
    }

    public function delete(int $id): void
    {
        $this->safeAction(function () use ($id) {
            if ($id === auth()->id()) {
                $this->toast('warning', 'Tidak dapat menghapus akun sendiri.');

                return;
            }

            $user = User::findOrFail($id);

            if ($user->hasRole(UserRole::Admin) && User::whereJsonContains('roles', UserRole::Admin->value)->count() <= 1) {
                $this->toast('warning', 'Tidak dapat menghapus Admin terakhir.');

                return;
            }

            $snapshot = ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => $user->roles];
            $user->delete();
            $this->logActivity('user.deleted', "Menghapus pengguna {$snapshot['name']} ({$snapshot['email']})", null, $snapshot);
            $this->toast('success', 'Pengguna dihapus.');
        }, permission: 'manage_users', genericError: 'Gagal menghapus pengguna.');
    }

    public function openLinkForm(int $userId): void
    {
        $this->linkingUserId = $userId;
        $this->selectedEmployeeId = null;
        $this->employeeSearch = '';
        $this->resetValidation();
        $this->showLinkForm = true;
    }

    public function linkEmployee(): void
    {
        $this->validate(['selectedEmployeeId' => 'required|exists:employees,id']);

        $this->safeAction(function () {
            $user = User::findOrFail($this->linkingUserId);
            $employee = Employee::findOrFail($this->selectedEmployeeId);

            if ($employee->user_id && $employee->user_id !== $user->id) {
                $this->addError('selectedEmployeeId', 'Karyawan ini sudah terhubung ke akun lain.');

                return;
            }

            $employee->update(['user_id' => $user->id]);

            $this->showLinkForm = false;
            $this->toast('success', "Akun {$user->name} berhasil dihubungkan ke {$employee->full_name}.");
        }, permission: 'manage_users', genericError: 'Gagal menghubungkan karyawan.');
    }

    public function unlinkEmployee(int $userId): void
    {
        $this->safeAction(function () use ($userId) {
            $user = User::with('employee')->findOrFail($userId);

            if (! $user->employee) {
                return;
            }

            $employeeName = $user->employee->full_name;
            $user->employee->update(['user_id' => null]);

            $this->toast('success', "Koneksi ke {$employeeName} berhasil dilepas.");
        }, permission: 'manage_users', genericError: 'Gagal melepas koneksi karyawan.');
    }

    public function render(): mixed
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterRole, fn ($q) => $q->whereJsonContains('roles', $this->filterRole))
            ->with('employee:id,user_id,full_name,employee_number')
            ->orderBy('name')
            ->paginate(20);

        $availableEmployees = Employee::whereNull('user_id')
            ->when($this->employeeSearch, fn ($q) => $q->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->employeeSearch}%")
                    ->orWhere('employee_number', 'like', "%{$this->employeeSearch}%");
            }))
            ->orderBy('full_name')
            ->get(['id', 'employee_number', 'full_name']);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'allRoles' => UserRole::cases(),
            'availableEmployees' => $availableEmployees,
        ]);
    }
}
