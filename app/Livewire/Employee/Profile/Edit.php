<?php

namespace App\Livewire\Employee\Profile;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.mobile')]
#[Title('Edit Profil')]
class Edit extends Component
{
    use WithFileUploads;

    // Account
    public string $name = '';

    public string $email = '';

    // Avatar
    public $avatar = null;

    public ?string $existing_avatar_path = null;

    // Identitas dasar
    public string $full_name = '';

    public string $nickname = '';

    public string $phone = '';

    public string $nik = '';

    // Data pribadi
    public string $gender = '';

    public ?string $date_of_birth = null;

    public string $place_of_birth = '';

    public string $address = '';

    // Pendidikan
    public string $last_education = '';

    public string $major_school_university = '';

    // Kontak Darurat
    public string $emergency_contact_name = '';

    public string $emergency_contact_number = '';

    // Bank
    public string $bank_name = '';

    public string $bank_account_number = '';

    public string $bank_account_holder = '';

    // Password change
    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        $employee = $user?->employee;

        $this->name = (string) $user?->name;
        $this->email = (string) $user?->email;

        if ($employee) {
            $this->existing_avatar_path = $employee->avatar_path;
            $this->full_name = (string) $employee->full_name;
            $this->nickname = (string) $employee->nickname;
            $this->phone = (string) $employee->phone;
            $this->nik = (string) $employee->nik;
            $this->gender = (string) $employee->gender;
            $this->date_of_birth = $employee->date_of_birth?->format('Y-m-d');
            $this->place_of_birth = (string) $employee->place_of_birth;
            $this->address = (string) $employee->address;
            $this->last_education = (string) $employee->last_education;
            $this->major_school_university = (string) $employee->major_school_university;
            $this->emergency_contact_name = (string) $employee->emergency_contact_name;
            $this->emergency_contact_number = (string) $employee->emergency_contact_number;
            $this->bank_name = (string) $employee->bank_name;
            $this->bank_account_number = (string) $employee->bank_account_number;
            $this->bank_account_holder = (string) $employee->bank_account_holder;
        }
    }

    public function updatedAvatar(): void
    {
        $this->validate(['avatar' => 'nullable|image|max:2048']);

        $user = auth()->user();
        $employee = $user?->employee;

        if (! $this->avatar || ! $employee) {
            return;
        }

        if ($this->existing_avatar_path) {
            Storage::disk('local')->delete($this->existing_avatar_path);
        }

        $avatarPath = $this->avatar->store('avatars', 'local');
        $employee->update(['avatar_path' => $avatarPath]);

        $this->existing_avatar_path = $avatarPath;
        $this->avatar = null;

        $this->dispatch('notify', type: 'success', message: 'Foto profil berhasil diperbarui.');
    }

    public function deleteAvatar(): void
    {
        $employee = auth()->user()?->employee;

        if (! $employee || ! $this->existing_avatar_path) {
            return;
        }

        Storage::disk('local')->delete($this->existing_avatar_path);
        $employee->update(['avatar_path' => null]);
        $this->existing_avatar_path = null;

        $this->dispatch('notify', type: 'success', message: 'Foto profil berhasil dihapus.');
    }

    public function saveProfile(): void
    {
        $user = auth()->user();

        $this->validate([
            'name' => 'required|string|max:200',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'full_name' => 'nullable|string|max:200',
            'nickname' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:30',
            'nik' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'place_of_birth' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'last_education' => 'required|string|max:100',
            'major_school_university' => 'required|string|max:200',
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_number' => 'required|string|max:24',
            'bank_name' => 'nullable|string|max:60',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_account_holder' => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($user): void {
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($emp = $user->employee) {
                $emp->update([
                    'full_name' => $this->full_name ?: $this->name,
                    'nickname' => $this->nickname ?: null,
                    'phone' => $this->phone ?: null,
                    'nik' => $this->nik,
                    'email' => $this->email,
                    'gender' => $this->gender ?: null,
                    'date_of_birth' => $this->date_of_birth ?: null,
                    'place_of_birth' => $this->place_of_birth ?: null,
                    'address' => $this->address ?: null,
                    'last_education' => $this->last_education,
                    'major_school_university' => $this->major_school_university,
                    'emergency_contact_name' => $this->emergency_contact_name,
                    'emergency_contact_number' => $this->emergency_contact_number,
                    'bank_name' => $this->bank_name ?: null,
                    'bank_account_number' => $this->bank_account_number ?: null,
                    'bank_account_holder' => $this->bank_account_holder ?: null,
                ]);
            }
        });

        $this->dispatch('notify', type: 'success', message: 'Profil berhasil disimpan.');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Kata sandi saat ini salah.');

            return;
        }

        $user->update(['password' => Hash::make($this->new_password)]);
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->dispatch('notify', type: 'success', message: 'Kata sandi berhasil diubah.');
    }

    public function render(): mixed
    {
        return view('livewire.employee.profile.edit', [
            'employee' => auth()->user()?->employee,
        ]);
    }
}
