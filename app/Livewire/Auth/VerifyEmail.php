<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Verifikasi Email')]
class VerifyEmail extends Component
{
	public bool $sent = false;

	public function mount(): void
	{
		/** @var User $user */
		$user = Auth::user();

		if ($user->hasVerifiedEmail()) {
			$this->redirect($this->resolveRedirectUrl($user), navigate: false);
		}
	}

	public function resend(): void
	{
		/** @var User $user */
		$user = Auth::user();

		if ($user->hasVerifiedEmail()) {
			$this->redirect($this->resolveRedirectUrl($user), navigate: false);

			return;
		}

		$user->sendEmailVerificationNotification();
		$this->sent = true;
	}

	private function resolveRedirectUrl(User $user): string
	{
		return $user->isAdminPanel() && $user->employee === null
			? route('admin.dashboard')
			: route('mobile.home');
	}

	public function render(): mixed
	{
		return view('livewire.auth.verify-email');
	}
}
