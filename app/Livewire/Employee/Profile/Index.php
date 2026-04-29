<?php

namespace App\Livewire\Employee\Profile;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
class Index extends Component
{
    public function logout(): void
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirect(route('login'), navigate: false);
    }

    public function render(): mixed
    {
        $employee = auth()->user()?->employee;

        return view('livewire.employee.profile.index', compact('employee'));
    }
}
