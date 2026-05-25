<?php

namespace App\Livewire\Admin;

use App\Concerns\HandlesAdminActions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profil Saya')]
#[Layout('components.layouts.admin')]
class Profile extends Component
{
	use HandlesAdminActions;

	public string $name = '';

	public string $email = '';

	public string $current_password = '';

	public string $new_password = '';

	public string $new_password_confirmation = '';

	public function mount(): void
	{
		$user = auth()->user();
		$this->name = (string) $user?->name;
		$this->email = (string) $user?->email;
	}

	public function saveProfile(): void
	{
		$user = auth()->user();

		$this->validate([
			'name' => 'required|string|max:200',
			'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
		]);

		$this->safeAction(function () use ($user) {
			$user->update([
				'name' => $this->name,
				'email' => $this->email,
			]);

			$this->toast('success', 'Profil berhasil disimpan.');
		}, genericError: 'Gagal menyimpan profil.');
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

		$this->safeAction(function () use ($user) {
			$user->update(['password' => Hash::make($this->new_password)]);
			$this->reset(['current_password', 'new_password', 'new_password_confirmation']);

			$this->toast('success', 'Kata sandi berhasil diubah.');
		}, genericError: 'Gagal mengubah kata sandi.');
	}

	public function render(): mixed
	{
		return view('livewire.admin.profile');
	}
}
