<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Announcements\Index as AdminAnnouncements;
use App\Livewire\Admin\Attendance\Index as AdminAttendance;
use App\Livewire\Admin\Dashboard\Index as AdminDashboard;
use App\Livewire\Admin\Employee\Form as AdminEmployeeForm;
use App\Livewire\Admin\Employee\Index as AdminEmployee;
use App\Livewire\Admin\Employee\Show as AdminEmployeeShow;
use App\Livewire\Admin\Leave\Index as AdminLeave;
use App\Livewire\Admin\Overtime\Index as AdminOvertime;
use App\Livewire\Admin\Payroll\Components as AdminPayrollComponents;
use App\Livewire\Admin\Payroll\Index as AdminPayroll;
use App\Livewire\Admin\Payroll\Payslip as AdminPayslip;
use App\Livewire\Admin\Payroll\Show as AdminPayrollShow;
use App\Livewire\Admin\Profile\Edit as AdminProfileEdit;
use App\Livewire\Admin\Reimbursement\Index as AdminReimbursement;
use App\Livewire\Admin\Shift\Index as AdminShift;
use App\Livewire\Auth\Login;
use App\Livewire\Employee\Attendance\Index as MobileAttendance;
use App\Livewire\Employee\Home\Index as MobileHome;
use App\Livewire\Employee\Leave\Create as MobileLeaveCreate;
use App\Livewire\Employee\Leave\Index as MobileLeave;
use App\Livewire\Employee\Payslip\Index as MobilePayslip;
use App\Livewire\Employee\Payslip\Show as MobilePayslipShow;
use App\Livewire\Employee\Profile\Edit as MobileProfileEdit;
use App\Livewire\Employee\Profile\Index as MobileProfile;
use App\Livewire\Employee\Reimbursement\Create as MobileReimbursementCreate;
use App\Livewire\Employee\Reimbursement\Index as MobileReimbursement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }
    $role = Auth::user()->role;

    return $role instanceof UserRole && $role->isAdminPanel()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('mobile.home');
})->name('home');

Route::view('/offline', 'offline')->name('offline');

// --- Auth ---
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// --- Admin / HR Web Dashboard ---
Route::middleware(['auth', 'admin.panel'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');

        Route::get('/employees', AdminEmployee::class)->name('employees');
        Route::get('/employees/create', AdminEmployeeForm::class)->name('employees.create');
        Route::get('/employees/{employee}', AdminEmployeeShow::class)->name('employees.show');
        Route::get('/employees/{employee}/edit', AdminEmployeeForm::class)->name('employees.edit');

        Route::get('/attendance', AdminAttendance::class)->name('attendance');
        Route::get('/leave', AdminLeave::class)->name('leave');
        Route::get('/overtime', AdminOvertime::class)->name('overtime');
        Route::get('/reimbursement', AdminReimbursement::class)->name('reimbursement');
        Route::get('/shift', AdminShift::class)->name('shift');
        Route::get('/payroll', AdminPayroll::class)->name('payroll');
        Route::get('/payroll/components', AdminPayrollComponents::class)->name('payroll.components');
        Route::get('/payroll/payslip/{payroll}', AdminPayslip::class)->name('payroll.payslip');
        Route::get('/payroll/{period}', AdminPayrollShow::class)->name('payroll.show');
        Route::get('/announcements', AdminAnnouncements::class)->name('announcements');
        Route::get('/profile', AdminProfileEdit::class)->name('profile');
    });

// --- Employee PWA Mobile ---
Route::middleware(['auth', 'employee.only'])
    ->prefix('m')
    ->name('mobile.')
    ->group(function () {
        Route::get('/', MobileHome::class)->name('home');
        Route::get('/attendance', MobileAttendance::class)->name('attendance');
        Route::get('/leave', MobileLeave::class)->name('leave');
        Route::get('/leave/create', MobileLeaveCreate::class)->name('leave.create');
        Route::get('/payslip', MobilePayslip::class)->name('payslip');
        Route::get('/payslip/{payroll}', MobilePayslipShow::class)->name('payslip.show');
        Route::get('/reimbursement', MobileReimbursement::class)->name('reimbursement');
        Route::get('/reimbursement/create', MobileReimbursementCreate::class)->name('reimbursement.create');
        Route::get('/profile', MobileProfile::class)->name('profile');
        Route::get('/profile/edit', MobileProfileEdit::class)->name('profile.edit');
    });
