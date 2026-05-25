<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Lupa Kata Sandi')]
class ForgotPassword extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public bool $sent = false;

    public function sendLink(): void
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        // Selalu tampilkan pesan berhasil untuk mencegah enumerasi email
        $this->sent = true;
    }

    public function render(): mixed
    {
        return view('livewire.auth.forgot-password');
    }
}
