<?php

use App\Livewire\Employee\AnnouncementShow;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['roles' => ['employee']]);
    $this->other = User::factory()->create(['roles' => ['employee']]);
    $this->admin = User::factory()->create(['roles' => ['admin']]);
});

it('shows a published announcement to its targeted recipient', function () {
    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Khusus Kamu',
        'content' => '<p>Halo</p>',
        'audience' => 'selected',
        'published_at' => now()->subMinute(),
    ]);
    $announcement->recipients()->attach($this->user->id);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['announcement' => $announcement])
        ->assertOk()
        ->assertSee('Khusus Kamu');
});

it('hides a targeted announcement from everyone else', function () {
    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Milik Publikasi',
        'content' => '<p> Rahasia </p>',
        'audience' => 'selected',
        'published_at' => now()->subMinute(),
    ]);
    $announcement->recipients()->attach($this->other->id);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['announcement' => $announcement])
        ->assertNotFound();
});

it('hides an expired announcement', function () {
    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Sudah Berakhir',
        'content' => '<p>Dulu</p>',
        'audience' => 'all',
        'published_at' => now()->subDays(5),
        'expires_at' => now()->subDay(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['announcement' => $announcement])
        ->assertNotFound();
});

it('hides a scheduled announcement that is not live yet', function () {
    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Nanti Diumumkan',
        'content' => '<p>Nanti</p>',
        'audience' => 'all',
        'published_at' => now()->addDay(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AnnouncementShow::class, ['announcement' => $announcement])
        ->assertNotFound();
});
