<?php

use App\Jobs\SendAnnouncementNotificationsJob;
use App\Livewire\Admin\Announcements;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['roles' => ['admin']]);
    $this->budi = User::factory()->create(['roles' => ['employee']]);
    $this->sari = User::factory()->create(['roles' => ['employee']]);
});

it('stores targeted recipients when audience is selected', function () {
    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->set('title', 'Rapat Divisi')
        ->set('content', 'Isi pengumuman')
        ->set('audience', 'selected')
        ->set('target_users', [$this->budi->id])
        ->call('save')
        ->assertHasNoErrors();

    $announcement = Announcement::where('title', 'Rapat Divisi')->first();

    expect($announcement->audience)->toBe('selected')
        ->and($announcement->recipients->pluck('id'))->toContain($this->budi->id);
});

it('requires at least one target user when audience is selected', function () {
    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->set('title', 'Tanpa Target')
        ->set('content', 'Isi pengumuman')
        ->set('audience', 'selected')
        ->call('save')
        ->assertHasErrors(['target_users']);

    expect(Announcement::where('title', 'Tanpa Target')->exists())->toBeFalse();
});

it('dispatches notifications job for targeted announcement', function () {
    Queue::fake();

    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Khusus Budi',
        'content' => 'Isi pengumuman',
        'audience' => 'selected',
        'published_at' => now(),
    ]);
    $announcement->recipients()->attach($this->budi->id);

    Queue::assertPushed(SendAnnouncementNotificationsJob::class);
});

it('does not dispatch notifications job for a draft', function () {
    Queue::fake();

    Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Masih Draft',
        'content' => 'Isi pengumuman',
        'audience' => 'all',
    ]);

    Queue::assertNotPushed(SendAnnouncementNotificationsJob::class);
});

it('syncs recipients on edit', function () {
    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Edit Saya',
        'content' => 'Isi pengumuman',
        'audience' => 'selected',
        'published_at' => now(),
    ]);
    $announcement->recipients()->attach($this->budi->id);

    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->call('open', $announcement->id)
        ->assertSet('audience', 'selected')
        ->assertSet('target_users', [$this->budi->id])
        ->set('target_users', [$this->sari->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($announcement->fresh()->recipients->pluck('id'))->toContain($this->sari->id)
        ->not->toContain($this->budi->id);
});

it('renders the announcement list', function () {
    Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Libur Kerja',
        'content' => '<p>Besok libur.</p>',
        'audience' => 'all',
        'published_at' => now(),
        'is_pinned' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->assertOk()
        ->assertSee('Libur Kerja')
        ->assertSee('Disematkan');
});

it('renders one checkbox per active user when audience is selected', function () {
    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->set('audience', 'selected')
        ->assertSeeHtml('type="checkbox" wire:model="target_users" value="'.$this->budi->id.'"')
        ->assertSeeHtml('type="checkbox" wire:model="target_users" value="'.$this->sari->id.'"');
});

it('clears stale recipients when switching back to all employees', function () {
    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Semula Terbatas',
        'content' => 'Isi pengumuman',
        'audience' => 'selected',
        'published_at' => now(),
    ]);
    $announcement->recipients()->attach($this->budi->id);

    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->call('open', $announcement->id)
        ->set('audience', 'all')
        ->call('save')
        ->assertHasNoErrors();

    expect($announcement->fresh()->recipients)->toBeEmpty();
});

it('opens the form for both create and edit', function () {
    $announcement = Announcement::create([
        'author_id' => $this->admin->id,
        'title' => 'Ada Judulnya',
        'content' => '<p>Isi</p>',
        'audience' => 'selected',
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->assertSet('showForm', false)
        ->call('open')
        ->assertSet('showForm', true)
        ->assertSet('editingId', null)
        ->call('open', $announcement->id)
        ->assertSet('showForm', true)
        ->assertSet('editingId', $announcement->id)
        ->assertSet('title', 'Ada Judulnya');
});

it('strips unsafe html from the stored content', function () {
    Livewire::actingAs($this->admin)
        ->test(Announcements::class)
        ->set('title', 'XSS')
        ->set('content', '<p onclick="alert(1)">Halo</p><script>alert(2)</script><a href="javascript:alert(3)">x</a>')
        ->call('save')
        ->assertHasNoErrors();

    $content = Announcement::where('title', 'XSS')->value('content');

    expect($content)->not->toContain('onclick')
        ->not->toContain('<script')
        ->not->toContain('javascript:')
        ->toContain('Halo');
});
