<?php

use App\Enums\OvertimeStatus;
use App\Livewire\Admin\Overtime as AdminOvertime;
use App\Livewire\Employee\Overtime as EmployeeOvertime;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-07-08 08:00:00', 'Asia/Jakarta'));
    Storage::fake('local');

    $this->user = User::factory()->create(['roles' => ['employee']]);
    $this->employee = Employee::factory()->create(['user_id' => $this->user->id]);
});

function hrUser(): User
{
    RolePermission::firstOrCreate(['role' => 'hr', 'permission' => 'manage_overtime']);

    return User::factory()->create(['roles' => ['hr']]);
}

test('employee can submit an overtime request with head approval proof', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->set('overtime_date', '2026-07-08')
        ->set('start_time', '18:00')
        ->set('end_time', '21:00')
        ->set('reason', 'Menyelesaikan rilis fitur payroll')
        ->set('head_approval', UploadedFile::fake()->image('approval.jpg'))
        ->call('requestConfirm')
        ->assertHasNoErrors()
        ->call('submit')
        ->assertDispatched('notify', type: 'success');

    $request = OvertimeRequest::first();

    expect($request)->not->toBeNull()
        ->and($request->employee_id)->toBe($this->employee->id)
        ->and($request->status)->toBe(OvertimeStatus::Pending)
        ->and($request->durationMinutes())->toBe(180)
        ->and($request->started_at->format('Y-m-d H:i'))->toBe('2026-07-08 18:00')
        ->and($request->ended_at->format('Y-m-d H:i'))->toBe('2026-07-08 21:00')
        ->and($request->head_approval_path)->not->toBeNull();

    Storage::disk('local')->assertExists($request->head_approval_path);
});

test('overtime past midnight rolls the end time over to the next day', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->set('overtime_date', '2026-07-08')
        ->set('start_time', '22:00')
        ->set('end_time', '02:00')
        ->set('reason', 'Deploy rilis malam hari')
        ->set('head_approval', UploadedFile::fake()->image('approval.jpg'))
        ->call('submit')
        ->assertHasNoErrors();

    $request = OvertimeRequest::first();

    expect($request->started_at->format('Y-m-d H:i'))->toBe('2026-07-08 22:00')
        ->and($request->ended_at->format('Y-m-d H:i'))->toBe('2026-07-09 02:00')
        ->and($request->durationMinutes())->toBe(240);
});

test('overtime request requires head approval proof', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->set('overtime_date', '2026-07-08')
        ->set('start_time', '18:00')
        ->set('end_time', '21:00')
        ->set('reason', 'Menyelesaikan rilis fitur payroll')
        ->call('requestConfirm')
        ->assertHasErrors(['head_approval' => 'required']);

    expect(OvertimeRequest::count())->toBe(0);
});

test('overtime end time cannot equal the start time', function () {
    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->set('overtime_date', '2026-07-08')
        ->set('start_time', '18:00')
        ->set('end_time', '18:00')
        ->set('reason', 'Menyelesaikan rilis fitur payroll')
        ->set('head_approval', UploadedFile::fake()->image('approval.jpg'))
        ->call('requestConfirm')
        ->assertHasErrors(['end_time' => 'different']);

    expect(OvertimeRequest::count())->toBe(0);
});

test('hr can approve a pending overtime request', function () {
    $request = OvertimeRequest::factory()->create(['employee_id' => $this->employee->id]);
    $hr = hrUser();

    Livewire::actingAs($hr)
        ->test(AdminOvertime::class)
        ->call('approve', $request->id)
        ->assertDispatched('notify', type: 'success');

    $request->refresh();

    expect($request->status)->toBe(OvertimeStatus::Approved)
        ->and($request->reviewed_by)->toBe($hr->id)
        ->and($request->reviewed_at)->not->toBeNull();
});

test('hr can reject a pending overtime request with a reason', function () {
    $request = OvertimeRequest::factory()->create(['employee_id' => $this->employee->id]);
    $hr = hrUser();

    Livewire::actingAs($hr)
        ->test(AdminOvertime::class)
        ->call('openRejectForm', $request->id)
        ->set('rejectionReason', 'Beban kerja tidak mendesak')
        ->call('reject')
        ->assertHasNoErrors()
        ->assertDispatched('notify', type: 'success');

    $request->refresh();

    expect($request->status)->toBe(OvertimeStatus::Rejected)
        ->and($request->rejection_reason)->toBe('Beban kerja tidak mendesak');
});

test('employee can still edit work documentation after the request is approved', function () {
    $request = OvertimeRequest::factory()->approved()->create([
        'employee_id' => $this->employee->id,
        'work_documentation' => 'Draft awal',
    ]);

    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->call('openDocForm', $request->id)
        ->assertSet('work_documentation', 'Draft awal')
        ->set('work_documentation', 'Deploy rilis v2 dan verifikasi payroll bulan Juli')
        ->call('saveDocumentation')
        ->assertHasNoErrors()
        ->assertDispatched('notify', type: 'success');

    $request->refresh();

    expect($request->work_documentation)->toBe('Deploy rilis v2 dan verifikasi payroll bulan Juli')
        ->and($request->status)->toBe(OvertimeStatus::Approved);
});

test('employee cannot edit work documentation of a rejected request', function () {
    $request = OvertimeRequest::factory()->rejected()->create([
        'employee_id' => $this->employee->id,
        'work_documentation' => 'Draft awal',
    ]);

    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->call('openDocForm', $request->id)
        ->assertDispatched('notify', type: 'warning')
        ->assertSet('showDocForm', false);

    expect($request->refresh()->work_documentation)->toBe('Draft awal');
});

test('employee cannot touch another employees overtime request', function () {
    $otherRequest = OvertimeRequest::factory()->create(['work_documentation' => 'Milik orang lain']);

    expect(fn () => Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->call('openDocForm', $otherRequest->id)
    )->toThrow(ModelNotFoundException::class);

    expect($otherRequest->refresh()->work_documentation)->toBe('Milik orang lain');
});

test('employee can cancel their own pending request', function () {
    $request = OvertimeRequest::factory()->create(['employee_id' => $this->employee->id]);

    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->call('cancel', $request->id)
        ->assertDispatched('notify', type: 'success');

    expect(OvertimeRequest::find($request->id))->toBeNull();
});

test('employee cannot cancel an already approved request', function () {
    $request = OvertimeRequest::factory()->approved()->create(['employee_id' => $this->employee->id]);

    Livewire::actingAs($this->user)
        ->test(EmployeeOvertime::class)
        ->call('cancel', $request->id)
        ->assertDispatched('notify', type: 'warning');

    expect(OvertimeRequest::find($request->id))->not->toBeNull();
});

test('user without manage_overtime permission cannot open the admin page', function () {
    Livewire::actingAs($this->user)
        ->test(AdminOvertime::class)
        ->assertForbidden();
});

test('head approval file is only reachable by the owner or hr', function () {
    $request = OvertimeRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'head_approval_path' => 'overtime-approvals/proof.jpg',
    ]);

    Storage::disk('local')->put('overtime-approvals/proof.jpg', 'dummy');

    $this->actingAs($this->user)
        ->get(route('files.overtime-approval', $request))
        ->assertOk();

    $this->actingAs(hrUser())
        ->get(route('files.overtime-approval', $request))
        ->assertOk();

    $stranger = User::factory()->create(['roles' => ['employee']]);
    Employee::factory()->create(['user_id' => $stranger->id]);

    $this->actingAs($stranger)
        ->get(route('files.overtime-approval', $request))
        ->assertForbidden();
});
