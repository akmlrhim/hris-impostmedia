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

    public function resend(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(route('mobile.home'), navigate: true);

            return;
        }

        $user->sendEmailVerificationNotification();
        $this->sent = true;
    }

    public function render(): mixed
    {
        return view('livewire.auth.verify-email');
    }
}
