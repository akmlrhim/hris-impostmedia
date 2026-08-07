<?php

use App\Enums\Permission;
use App\Livewire\Admin\Employee\Index as EmployeeIndex;
use App\Livewire\Admin\Employee\Show as EmployeeShow;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    RolePermission::firstOrCreate(['role' => 'hr', 'permission' => 'manage_employees']);

    $this->admin = User::factory()->create(['roles' => ['admin']]);
    $this->hr = User::factory()->create(['roles' => ['hr']]);

    $this->employee = Employee::factory()->create([
        'full_name' => 'Sari Karyawan',
        'basic_salary' => 7500000,
    ]);
});

test('hr may view salary but admin may not', function () {
    expect(Gate::forUser($this->hr)->allows(Permission::ViewSalary->value))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows(Permission::ViewSalary->value))->toBeFalse();
});

test('the admin bypass still grants every other permission', function () {
    expect(Gate::forUser($this->admin)->allows(Permission::ManagePayroll->value))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows(Permission::ManageUsers->value))->toBeTrue();
});

test('a user holding both admin and hr roles may view salary', function () {
    $both = User::factory()->create(['roles' => ['admin', 'hr']]);

    expect(Gate::forUser($both)->allows(Permission::ViewSalary->value))->toBeTrue();
});

test('rupiah_masked hides the amount from admin and shows it to hr', function () {
    $this->actingAs($this->admin);
    expect(rupiah_masked(7500000))->toBe('*****');

    $this->actingAs($this->hr);
    expect(rupiah_masked(7500000))->toBe('Rp 7.500.000');
});

test('view_salary is not exposed as a configurable role permission', function () {
    expect(Permission::configurable())
        ->not->toContain(Permission::ViewSalary)
        ->not->toContain(Permission::ManageUsers);
});

test('the employee detail page censors the salary for admin', function () {
    Livewire::actingAs($this->admin)
        ->test(EmployeeShow::class, ['employee' => $this->employee])
        ->assertSee('*****')
        ->assertDontSee('Rp 7.500.000');
});

test('the employee detail page shows the salary to hr', function () {
    Livewire::actingAs($this->hr)
        ->test(EmployeeShow::class, ['employee' => $this->employee])
        ->assertSee('Rp 7.500.000');
});

test('the edit form never hydrates the salary for admin', function () {
    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->call('open', $this->employee->id)
        ->assertSet('basic_salary', 0.0);
});

test('the edit form hydrates the salary for hr', function () {
    Livewire::actingAs($this->hr)
        ->test(EmployeeIndex::class)
        ->call('open', $this->employee->id)
        ->assertSet('basic_salary', 7500000.0);
});

test('an admin saving the form leaves the stored salary untouched', function () {
    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->call('open', $this->employee->id)
        ->set('full_name', 'Sari Diperbarui')
        ->set('basic_salary', 999999999)
        ->call('save')
        ->assertHasNoErrors();

    $this->employee->refresh();

    expect($this->employee->full_name)->toBe('Sari Diperbarui')
        ->and((float) $this->employee->basic_salary)->toBe(7500000.0);
});

test('hr can still update the salary', function () {
    Livewire::actingAs($this->hr)
        ->test(EmployeeIndex::class)
        ->call('open', $this->employee->id)
        ->set('basic_salary', 9000000)
        ->call('save')
        ->assertHasNoErrors();

    expect((float) $this->employee->refresh()->basic_salary)->toBe(9000000.0);
});

test('admin cannot download the payroll period pdf', function () {
    $period = PayrollPeriod::create([
        'code' => '2026-07',
        'year' => 2026,
        'month' => 7,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-31',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.payroll.period.pdf', $period))
        ->assertForbidden();
});
