<?php

use App\Livewire\Admin\Holiday;
use App\Models\Holiday as HolidayModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates a holiday with valid data', function () {
    $admin = User::factory()->create(['roles' => ['admin']]);

    Livewire::actingAs($admin)
        ->test(Holiday::class)
        ->call('open')
        ->set('date', '2026-08-17')
        ->set('holiday_name', 'Hari Kemerdekaan RI')
        ->set('description', 'Libur nasional')
        ->call('save')
        ->assertHasNoErrors();

    expect(HolidayModel::whereDate('date', '2026-08-17')->exists())->toBeTrue();
});

it('rejects a holiday without a date or name', function () {
    $admin = User::factory()->create(['roles' => ['admin']]);

    Livewire::actingAs($admin)
        ->test(Holiday::class)
        ->call('open')
        ->call('save')
        ->assertHasErrors(['date' => 'required', 'holiday_name' => 'required']);

    expect(HolidayModel::count())->toBe(0);
});

it('rejects a duplicate date', function () {
    $admin = User::factory()->create(['roles' => ['admin']]);
    HolidayModel::create(['date' => '2026-08-17', 'holiday_name' => 'Existing']);

    Livewire::actingAs($admin)
        ->test(Holiday::class)
        ->call('open')
        ->set('date', '2026-08-17')
        ->set('holiday_name', 'Duplicate')
        ->call('save')
        ->assertHasErrors(['date' => 'unique']);
});
