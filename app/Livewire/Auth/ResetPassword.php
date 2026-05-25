<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Reset Kata Sandi')]
class ResetPassword extends Component
{
	#[Locked]
	public string $token = '';

	#[Validate('required|email')]
	public string $email = '';

	#[Validate('required|string|min:8|confirmed')]
	public string $password = '';

	public string $password_confirmation = '';

	public function mount(string $token): void
	{
		$this->token = $token;
		$this->email = request()->string('email')->toString();
	}

	public function resetPassword(): void
	{
		$this->validate();

		$status = Password::reset(
			[
				'email' => $this->email,
				'password' => $this->password,
				'password_confirmation' => $this->password_confirmation,
				'token' => $this->token,
			],
			function ($user) {
				$user->forceFill([
					'password' => Hash::make($this->password),
					'remember_token' => Str::random(60),
				])->save();

				event(new PasswordReset($user));
			}
		);

		if ($status === Password::PASSWORD_RESET) {
			$this->redirectRoute('login', navigate: true);

			return;
		}

		$this->addError('email', __($status));
	}

	public function render(): mixed
	{
		return view('livewire.auth.reset-password');
	}
}
