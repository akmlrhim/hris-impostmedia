<?php

namespace App\Livewire\Admin\Employee;

use App\Concerns\HandlesAdminActions;
use App\Enums\UserRole;
use App\Enums\WorkType;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Karyawan')]
#[Layout('components.layouts.admin')]
class Index extends Component
{
    use HandlesAdminActions, WithFileUploads, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $work_type_filter = '';

    #[Url]
    public string $active_filter = '';

    // --- Form modal state ---
    public bool $showForm = false;

    public ?int $editingId = null;

    // Akun & Identitas
    public string $employee_number = '';

    public string $full_name = '';

    public string $nickname = '';

    public string $email = '';

    public string $phone = '';

    public string $nik = '';

    public string $gender = 'male';

    public ?string $date_of_birth = null;

    public string $place_of_birth = '';

    // Alamat
    public string $address = '';

    // Pendidikan
    public string $last_education = '';

    public string $major_school_university = '';

    // Penempatan
    public string $work_type = 'wfa';

    public ?string $contract_start_date = null;

    public ?string $contract_end_date = null;

    public bool $is_active = true;

    // Avatar
    public $avatar = null;

    public ?string $existing_avatar_path = null;

    // Penggajian & Bank
    public float $basic_salary = 0;

    public string $bank_name = '';

    public string $bank_account_number = '';

    public string $bank_account_holder = '';

    // Kontak Darurat
    public string $emergency_contact_name = '';

    public string $emergency_contact_number = '';

    public function mount(): void
    {
        Gate::authorize('manage_employees');

        $editQuery = request()->query('edit');

        if ($raw = $editQuery) {
            $id = (int) last(explode('_', (string) $raw));
            if ($id > 0) {
                $this->open($id);
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingWorkTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingActiveFilter(): void
    {
        $this->resetPage();
    }

    public function open(?int $id = null): void
    {
        $this->reset([
            'editingId',
            'employee_number',
            'full_name',
            'nickname',
            'email',
            'phone',
            'nik',
            'gender',
            'date_of_birth',
            'place_of_birth',
            'address',
            'last_education',
            'major_school_university',
            'work_type',
            'contract_start_date',
            'contract_end_date',
            'is_active',
            'basic_salary',
            'bank_name',
            'bank_account_number',
            'bank_account_holder',
            'emergency_contact_name',
            'emergency_contact_number',
            'avatar',
            'existing_avatar_path',
        ]);
        $this->resetValidation();
        $this->gender = 'male';
        $this->work_type = 'wfa';
        $this->is_active = true;
        $this->contract_start_date = now()->toDateString();

        if ($id) {
            $emp = Employee::findOrFail($id);
            $this->editingId = $emp->id;
            $this->employee_number = $emp->employee_number;
            $this->full_name = $emp->full_name;
            $this->nickname = (string) $emp->nickname;
            $this->email = $emp->user?->email ?? (string) $emp->email;
            $this->phone = (string) $emp->phone;
            $this->nik = (string) $emp->nik;
            $this->gender = (string) ($emp->gender ?: 'male');
            $this->date_of_birth = $emp->date_of_birth?->format('Y-m-d');
            $this->place_of_birth = (string) $emp->place_of_birth;
            $this->address = (string) $emp->address;
            $this->last_education = (string) $emp->last_education;
            $this->major_school_university = (string) $emp->major_school_university;
            $this->existing_avatar_path = $emp->avatar_path;
            $this->work_type = $emp->work_type?->value ?? 'wfa';
            $this->contract_start_date = $emp->contract_start_date?->format('Y-m-d');
            $this->contract_end_date = $emp->contract_end_date?->format('Y-m-d');
            $this->is_active = (bool) $emp->is_active;
            $this->basic_salary = (float) $emp->basic_salary;
            $this->bank_name = (string) $emp->bank_name;
            $this->bank_account_number = (string) $emp->bank_account_number;
            $this->bank_account_holder = (string) $emp->bank_account_holder;
            $this->emergency_contact_name = (string) $emp->emergency_contact_name;
            $this->emergency_contact_number = (string) $emp->emergency_contact_number;
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $userId = $this->editingId ? Employee::find($this->editingId)?->user_id : null;

        $this->validate([
            'employee_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('employees', 'employee_number')->ignore($this->editingId),
            ],
            'full_name' => 'required|string|max:200',
            'nickname' => 'nullable|string|max:80',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => 'nullable|string|max:30',
            'nik' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'place_of_birth' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'last_education' => 'required|string|max:100',
            'major_school_university' => 'required|string|max:200',
            'avatar' => 'nullable|image|max:2048',
            'work_type' => ['required', Rule::in(array_column(WorkType::cases(), 'value'))],
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:60',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_account_holder' => 'nullable|string|max:200',
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_number' => 'required|string|max:24',
        ]);

        $this->safeAction(function () {
            $avatarPath = $this->existing_avatar_path;
            if ($this->avatar) {
                if ($this->existing_avatar_path) {
                    Storage::disk('local')->delete($this->existing_avatar_path);
                }
                $avatarPath = $this->avatar->store('avatars', 'local');
            }

            DB::transaction(function () use ($avatarPath): void {
                if ($this->editingId) {
                    $emp = Employee::findOrFail($this->editingId);
                    $emp->user?->update([
                        'name' => $this->full_name,
                        'email' => $this->email,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $this->full_name,
                        'email' => $this->email,
                        'password' => Hash::make('password'),
                        'role' => UserRole::Employee,
                        'email_verified_at' => now(),
                    ]);
                    $emp = new Employee(['user_id' => $user->id]);
                }

                $emp->fill([
                    'employee_number' => $this->employee_number,
                    'full_name' => $this->full_name,
                    'nickname' => $this->nickname ?: null,
                    'phone' => $this->phone ?: null,
                    'nik' => $this->nik,
                    'email' => $this->email,
                    'gender' => $this->gender,
                    'date_of_birth' => $this->date_of_birth ?: null,
                    'place_of_birth' => $this->place_of_birth ?: null,
                    'address' => $this->address ?: null,
                    'last_education' => $this->last_education,
                    'major_school_university' => $this->major_school_university,
                    'avatar_path' => $avatarPath,
                    'work_type' => $this->work_type,
                    'contract_start_date' => $this->contract_start_date ?: null,
                    'contract_end_date' => $this->contract_end_date ?: null,
                    'is_active' => $this->is_active,
                    'basic_salary' => $this->basic_salary,
                    'bank_name' => $this->bank_name ?: null,
                    'bank_account_number' => $this->bank_account_number ?: null,
                    'bank_account_holder' => $this->bank_account_holder ?: null,
                    'emergency_contact_name' => $this->emergency_contact_name,
                    'emergency_contact_number' => $this->emergency_contact_number,
                ])->save();
            });

            $this->toast('success', $this->editingId ? 'Karyawan diperbarui.' : 'Karyawan ditambahkan.');
            $this->showForm = false;
            $this->editingId = null;
        }, permission: 'manage_employees', genericError: 'Gagal menyimpan data karyawan.');
    }

    public function toggleActive(int $id): void
    {
        $this->safeAction(function () use ($id) {
            $emp = Employee::findOrFail($id);
            $emp->update(['is_active' => ! $emp->is_active]);
            $this->logActivity(
                $emp->is_active ? 'employee.activated' : 'employee.deactivated',
                ($emp->is_active ? 'Mengaktifkan' : 'Menonaktifkan')." karyawan {$emp->full_name}",
                $emp,
            );
            $this->toast('success', $emp->is_active ? 'Karyawan diaktifkan.' : 'Karyawan dinonaktifkan.');
        }, permission: 'manage_employees', genericError: 'Gagal mengubah status karyawan.');
    }

    public function render(): mixed
    {
        $employees = Employee::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('employee_number', 'like', "%{$this->search}%")
                    ->orWhere('nik', 'like', "%{$this->search}%");
            }))
            ->when($this->work_type_filter !== '', fn ($q) => $q->where('work_type', $this->work_type_filter))
            ->when($this->active_filter !== '', fn ($q) => $q->where('is_active', $this->active_filter === '1'))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.employee.index', [
            'employees' => $employees,
            'workTypes' => WorkType::cases(),
        ]);
    }
}
