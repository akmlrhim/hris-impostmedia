<?php

namespace App\Livewire\Admin\Employee;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $position = '';

    #[Url]
    public string $status = '';

    // --- Form modal state ---
    public bool $showForm = false;

    public ?int $editingId = null;

    // Akun & Identitas
    public string $employee_number = '';

    public string $full_name = '';

    public string $nickname = '';

    public string $email = '';

    public string $phone = '';

    public string $gender = 'male';

    public ?string $date_of_birth = null;

    public string $place_of_birth = '';

    public string $religion = '';

    // Alamat
    public string $address = '';

    public string $city = '';

    public string $province = '';

    public string $postal_code = '';

    // Penempatan
    public ?int $job_position_id = null;

    public string $employment_status = 'permanent';

    public ?string $join_date = null;

    public ?string $probation_end_date = null;

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

    // Helpers
    public bool $showNewPosition = false;

    public string $new_position_name = '';

    public function mount(): void
    {
        if ($raw = request()->query('edit')) {
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

    public function open(?int $id = null): void
    {
        $this->reset([
            'editingId',
            'employee_number',
            'full_name',
            'nickname',
            'email',
            'phone',
            'gender',
            'date_of_birth',
            'place_of_birth',
            'religion',
            'address',
            'city',
            'province',
            'postal_code',
            'job_position_id',
            'employment_status',
            'join_date',
            'probation_end_date',
            'contract_end_date',
            'is_active',
            'basic_salary',
            'bank_name',
            'bank_account_number',
            'bank_account_holder',
            'avatar',
            'existing_avatar_path',
            'showNewPosition',
            'new_position_name',
        ]);
        $this->resetValidation();
        $this->gender = 'male';
        $this->employment_status = 'permanent';
        $this->is_active = true;
        $this->join_date = now()->toDateString();

        if ($id) {
            $emp = Employee::findOrFail($id);
            $this->editingId = $emp->id;
            $this->employee_number = $emp->employee_number;
            $this->full_name = $emp->full_name;
            $this->nickname = (string) $emp->nickname;
            $this->email = $emp->user?->email ?? '';
            $this->phone = (string) $emp->phone;
            $this->gender = (string) $emp->gender;
            $this->date_of_birth = $emp->date_of_birth?->format('Y-m-d');
            $this->place_of_birth = (string) $emp->place_of_birth;
            $this->religion = (string) $emp->religion;
            $this->address = (string) $emp->address;
            $this->city = (string) $emp->city;
            $this->province = (string) $emp->province;
            $this->postal_code = (string) $emp->postal_code;
            $this->job_position_id = $emp->job_position_id;
            $this->existing_avatar_path = $emp->avatar_path;
            $this->employment_status = $emp->employment_status?->value ?? 'permanent';
            $this->join_date = $emp->join_date?->format('Y-m-d');
            $this->probation_end_date = $emp->probation_end_date?->format('Y-m-d');
            $this->contract_end_date = $emp->contract_end_date?->format('Y-m-d');
            $this->is_active = (bool) $emp->is_active;
            $this->basic_salary = (float) $emp->basic_salary;
            $this->bank_name = (string) $emp->bank_name;
            $this->bank_account_number = (string) $emp->bank_account_number;
            $this->bank_account_holder = (string) $emp->bank_account_holder;
        }

        $this->showForm = true;
    }

    public function addPosition(): void
    {
        $this->validate([
            'new_position_name' => 'required|string|min:2|max:100',
        ]);

        $code = strtoupper(Str::slug($this->new_position_name, ''));
        if (strlen($code) > 28) {
            $code = substr($code, 0, 28);
        }
        $base = $code;
        $i = 1;
        while (JobPosition::where('code', $code)->exists()) {
            $code = substr($base, 0, 26).'-'.$i++;
        }

        $pos = JobPosition::create([
            'code' => $code,
            'name' => $this->new_position_name,
            'is_active' => true,
        ]);

        $this->job_position_id = $pos->id;
        $this->new_position_name = '';
        $this->showNewPosition = false;

        $this->dispatch('notify', type: 'success', message: 'Posisi baru ditambahkan.');
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
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'job_position_id' => 'nullable|exists:job_positions,id',
            'avatar' => 'nullable|image|max:2048',
            'employment_status' => 'required',
            'join_date' => 'required|date',
            'probation_end_date' => 'nullable|date|after_or_equal:join_date',
            'contract_end_date' => 'nullable|date|after_or_equal:join_date',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:60',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_account_holder' => 'nullable|string|max:200',
        ]);

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
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth ?: null,
                'place_of_birth' => $this->place_of_birth ?: null,
                'religion' => $this->religion ?: null,
                'address' => $this->address ?: null,
                'city' => $this->city ?: null,
                'province' => $this->province ?: null,
                'postal_code' => $this->postal_code ?: null,
                'job_position_id' => $this->job_position_id,
                'avatar_path' => $avatarPath,
                'employment_status' => $this->employment_status,
                'join_date' => $this->join_date,
                'probation_end_date' => $this->probation_end_date ?: null,
                'contract_end_date' => $this->contract_end_date ?: null,
                'is_active' => $this->is_active,
                'basic_salary' => $this->basic_salary,
                'bank_name' => $this->bank_name ?: null,
                'bank_account_number' => $this->bank_account_number ?: null,
                'bank_account_holder' => $this->bank_account_holder ?: null,
            ])->save();

        });

        $this->dispatch('notify', type: 'success', message: $this->editingId ? 'Karyawan diperbarui.' : 'Karyawan ditambahkan.');
        $this->showForm = false;
        $this->editingId = null;
    }

    public function render(): mixed
    {
        $employees = Employee::query()
            ->with(['position'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('employee_number', 'like', "%{$this->search}%");
            }))
            ->when($this->position, fn ($q) => $q->where('job_position_id', $this->position))
            ->when($this->status, fn ($q) => $q->where('employment_status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.employee.index', [
            'employees' => $employees,
            'positions' => JobPosition::where('is_active', true)->orderBy('name')->get(),
            'employmentStatuses' => EmploymentStatus::cases(),
        ]);
    }
}
