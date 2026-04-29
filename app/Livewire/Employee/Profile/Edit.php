<?php

namespace App\Livewire\Employee\Profile;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Edit extends Component
{
    // Account
    public string $name = '';

    public string $email = '';

    // Employee fields
    public string $nickname = '';

    public string $phone = '';

    public string $address = '';

    public string $city = '';

    public string $province = '';

    public string $postal_code = '';

    public string $emergency_contact_name = '';

    public string $emergency_contact_phone = '';

    public string $emergency_contact_relation = '';

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
            $this->nickname = (string) $employee->nickname;
            $this->phone = (string) $employee->phone;
            $this->address = (string) $employee->address;
            $this->city = (string) $employee->city;
            $this->province = (string) $employee->province;
            $this->postal_code = (string) $employee->postal_code;
            $this->emergency_contact_name = (string) $employee->emergency_contact_name;
            $this->emergency_contact_phone = (string) $employee->emergency_contact_phone;
            $this->emergency_contact_relation = (string) $employee->emergency_contact_relation;
            $this->bank_name = (string) $employee->bank_name;
            $this->bank_account_number = (string) $employee->bank_account_number;
            $this->bank_account_holder = (string) $employee->bank_account_holder;
        }
    }

    public function saveProfile(): void
    {
        $user = auth()->user();

        $this->validate([
            'name' => 'required|string|max:200',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'emergency_contact_relation' => 'nullable|string|max:50',
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
                    'nickname' => $this->nickname ?: null,
                    'phone' => $this->phone ?: null,
                    'address' => $this->address ?: null,
                    'city' => $this->city ?: null,
                    'province' => $this->province ?: null,
                    'postal_code' => $this->postal_code ?: null,
                    'emergency_contact_name' => $this->emergency_contact_name ?: null,
                    'emergency_contact_phone' => $this->emergency_contact_phone ?: null,
                    'emergency_contact_relation' => $this->emergency_contact_relation ?: null,
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
