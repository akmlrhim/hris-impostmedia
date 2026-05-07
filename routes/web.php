<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Announcements as AdminAnnouncements;
use App\Livewire\Admin\Attendance as AdminAttendance;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Employee\Index as AdminEmployee;
use App\Livewire\Admin\Employee\Show as AdminEmployeeShow;
use App\Livewire\Admin\Holiday as AdminHoliday;
use App\Livewire\Admin\Payroll\Index as AdminPayroll;
use App\Livewire\Admin\Payroll\Payslip as AdminPayslip;
use App\Livewire\Admin\Payroll\Show as AdminPayrollShow;
use App\Livewire\Admin\Profile as AdminProfileEdit;
use App\Livewire\Admin\Shift as AdminShift;
use App\Livewire\Auth\Login;
use App\Livewire\Employee\Attendance as MobileAttendance;
use App\Livewire\Employee\Directory as MobileDirectory;
use App\Livewire\Employee\Home as MobileHome;
use App\Livewire\Employee\Payslip\Index as MobilePayslip;
use App\Livewire\Employee\Payslip\Show as MobilePayslipShow;
use App\Livewire\Employee\Profile\Edit as MobileProfileEdit;
use App\Livewire\Employee\Profile\Index as MobileProfile;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
        Route::get('/employees/{employee}', AdminEmployeeShow::class)->name('employees.show');

        Route::get('/attendance', AdminAttendance::class)->name('attendance');

        Route::get('/shift', AdminShift::class)->name('shift');
        Route::get('/payroll', AdminPayroll::class)->name('payroll');
        Route::get('/payroll/payslip/{payroll}', AdminPayslip::class)->name('payroll.payslip');
        Route::get('/payroll/{period}', AdminPayrollShow::class)->name('payroll.show');
        Route::get('/announcements', AdminAnnouncements::class)->name('announcements');
        Route::get('/holidays', AdminHoliday::class)->name('holidays');
        Route::get('/profile', AdminProfileEdit::class)->name('profile');
    });

// --- Mobile (PWA) — open to any authenticated user, including HR/Admin ---
Route::middleware('auth')
    ->prefix('m')
    ->name('mobile.')
    ->group(function () {
        Route::get('/', MobileHome::class)->name('home');
        Route::get('/attendance', MobileAttendance::class)->name('attendance');
        Route::get('/directory', MobileDirectory::class)->name('directory');
        Route::get('/payslip', MobilePayslip::class)->name('payslip');
        Route::get('/payslip/{payroll}', MobilePayslipShow::class)->name('payslip.show');
        Route::get('/profile', MobileProfile::class)->name('profile');
        Route::get('/profile/edit', MobileProfileEdit::class)->name('profile.edit');
    });

// --- Private file streaming (auth-gated) ---
Route::middleware('auth')->group(function () {
    Route::get('/files/avatar/{employee}', function (Employee $employee) {
        abort_unless($employee->avatar_path && Storage::disk('local')->exists($employee->avatar_path), 404);

        return Storage::disk('local')->response($employee->avatar_path, null, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    })->name('files.avatar');

});
