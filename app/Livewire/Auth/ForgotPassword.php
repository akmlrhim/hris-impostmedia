<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }

    public function sendLink(): void
    {
        $this->validate();

        $key = 'forgot-password:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak permintaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        RateLimiter::hit($key, 300);

        $user = User::where('email', Str::lower($this->email))->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => 'Email tidak terdaftar. Periksa kembali alamat email Anda.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => $status === Password::RESET_THROTTLED
                    ? 'Tautan reset baru saja dikirim. Tunggu beberapa saat sebelum meminta lagi.'
                    : 'Gagal mengirim tautan reset. Coba lagi nanti.',
            ]);
        }

        RateLimiter::clear($key);

        $this->sent = true;
    }

    public function render(): mixed
    {
        return view('livewire.auth.forgot-password');
    }
}
