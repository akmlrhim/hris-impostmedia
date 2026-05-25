<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Masuk')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    public bool $remember = false;

    public bool $showPanelSelector = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->handleAlreadyAuthenticated();
        }
    }

    public function login(): void
    {
        $this->validate();

        $key = 'login:'.Str::lower($this->email).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif.',
            ]);
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        $this->handleAlreadyAuthenticated();
    }

    public function choosePanel(string $panel): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->redirect(
            $panel === 'employee' ? route('mobile.home') : route('admin.dashboard'),
            navigate: false,
        );
    }

    private function handleAlreadyAuthenticated(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdminPanel = $user->isAdminPanel();
        $hasEmployee = $user->employee !== null;

        if ($isAdminPanel && $hasEmployee) {
            $this->showPanelSelector = true;

            return;
        }

        $this->redirect(
            $isAdminPanel ? route('admin.dashboard') : route('mobile.home'),
            navigate: false,
        );
    }

    public function render(): mixed
    {
        return view('livewire.auth.login');
    }
}
