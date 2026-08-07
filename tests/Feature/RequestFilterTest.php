<?php

use App\Enums\LeaveType;
use App\Enums\WorkType;
use App\Livewire\Employee\Leave;
use App\Livewire\Employee\Overtime;
use App\Livewire\Employee\RemoteWork;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\RemoteWorkRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-07-08 08:00:00', 'Asia/Makassar'));

    $this->user = User::factory()->create(['roles' => ['employee']]);
    $this->employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'work_type' => WorkType::WFO,
    ]);
});

test('leave requests can be filtered by status', function () {
    LeaveRequest::factory()->for($this->employee)->create();
    LeaveRequest::factory()->for($this->employee)->approved()->create();

    Livewire::actingAs($this->user)
        ->test(Leave::class)
        ->assertViewHas('requests', fn ($requests) => $requests->count() === 2)
        ->set('statusFilter', 'approved')
        ->assertViewHas('requests', fn ($requests) => $requests->count() === 1
            && $requests->first()->status->value === 'approved');
});

test('leave requests can be filtered by type', function () {
    LeaveRequest::factory()->for($this->employee)->ofType(LeaveType::Annual)->create();
    LeaveRequest::factory()->for($this->employee)->ofType(LeaveType::Sick)->create();

    Livewire::actingAs($this->user)
        ->test(Leave::class)
        ->set('typeFilter', LeaveType::Sick->value)
        ->assertViewHas('requests', fn ($requests) => $requests->count() === 1
            && $requests->first()->type === LeaveType::Sick);
});

test('an empty leave filter result explains itself', function () {
    LeaveRequest::factory()->for($this->employee)->create();

    Livewire::actingAs($this->user)
        ->test(Leave::class)
        ->set('statusFilter', 'rejected')
        ->assertSee('Tidak ada pengajuan pada filter ini.');
});

test('remote work requests can be filtered by status and type', function () {
    RemoteWorkRequest::factory()->for($this->employee)->create();
    RemoteWorkRequest::factory()->for($this->employee)->rejected()->create();

    Livewire::actingAs($this->user)
        ->test(RemoteWork::class)
        ->set('statusFilter', 'rejected')
        ->assertViewHas('requests', fn ($requests) => $requests->count() === 1)
        ->set('statusFilter', '')
        ->set('typeFilter', WorkType::WFA->value)
        ->assertViewHas('requests', fn ($requests) => $requests->count() === 2);
});

test('overtime requests can be filtered by status', function () {
    OvertimeRequest::factory()->for($this->employee)->create();
    OvertimeRequest::factory()->for($this->employee)->approved()->create();

    Livewire::actingAs($this->user)
        ->test(Overtime::class)
        ->set('statusFilter', 'pending')
        ->assertViewHas('requests', fn ($requests) => $requests->count() === 1
            && $requests->first()->status->value === 'pending');
});

test('the filter row offers every status', function () {
    Livewire::actingAs($this->user)
        ->test(Overtime::class)
        ->assertSee('Semua')
        ->assertSee('Menunggu')
        ->assertSee('Disetujui')
        ->assertSee('Ditolak');
});
