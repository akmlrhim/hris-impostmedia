<?php

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    RateLimiter::clear('forgot-password:127.0.0.1');
});

it('requires an email', function () {
    Livewire::test(ForgotPassword::class)
        ->set('email', '')
        ->call('sendLink')
        ->assertHasErrors(['email' => 'required'])
        ->assertSet('sent', false);
});

it('rejects an invalid email format', function () {
    Livewire::test(ForgotPassword::class)
        ->set('email', 'bukan-email')
        ->call('sendLink')
        ->assertHasErrors(['email' => 'email'])
        ->assertSet('sent', false);
});

it('rejects an email that is not registered', function () {
    Livewire::test(ForgotPassword::class)
        ->set('email', 'tidakada@example.com')
        ->call('sendLink')
        ->assertHasErrors('email')
        ->assertSet('sent', false);

    Notification::assertNothingSent();
});

it('rejects an inactive account', function () {
    $user = User::factory()->create(['is_active' => false]);

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendLink')
        ->assertHasErrors('email')
        ->assertSet('sent', false);

    Notification::assertNothingSent();
});

it('sends the reset link for a registered active account', function () {
    $user = User::factory()->create(['is_active' => true]);

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('throttles repeated requests from the same ip', function () {
    User::factory()->create(['email' => 'ada@example.com', 'is_active' => true]);

    foreach (range(1, 3) as $attempt) {
        Livewire::test(ForgotPassword::class)
            ->set('email', 'tidakada@example.com')
            ->call('sendLink')
            ->assertHasErrors('email');
    }

    Livewire::test(ForgotPassword::class)
        ->set('email', 'ada@example.com')
        ->call('sendLink')
        ->assertHasErrors('email')
        ->assertSet('sent', false);

    Notification::assertNothingSent();
});
