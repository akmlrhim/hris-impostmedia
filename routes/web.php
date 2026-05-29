<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\Payroll\PayrollPdfController;
use App\Http\Controllers\Employee\PayslipPdfController;
use App\Livewire\Admin\AccessControl as AdminAccessControl;
use App\Livewire\Admin\ActivityLog as AdminActivityLog;
use App\Livewire\Admin\Announcements as AdminAnnouncements;
use App\Livewire\Admin\Attendance as AdminAttendance;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Employee\Index as AdminEmployee;
use App\Livewire\Admin\Employee\Show as AdminEmployeeShow;
use App\Livewire\Admin\Holiday as AdminHoliday;
use App\Livewire\Admin\Leave as AdminLeave;
use App\Livewire\Admin\OfficeLocation as AdminOfficeLocation;
use App\Livewire\Admin\Payroll\Index as AdminPayroll;
use App\Livewire\Admin\Payroll\Payslip as AdminPayslip;
use App\Livewire\Admin\Payroll\Show as AdminPayrollShow;
use App\Livewire\Admin\Profile as AdminProfileEdit;
use App\Livewire\Admin\RemoteWork as AdminRemoteWork;
use App\Livewire\Admin\Shift as AdminShift;
use App\Livewire\Admin\UserManagement as AdminUserManagement;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Employee\AnnouncementShow as MobileAnnouncementShow;
use App\Livewire\Employee\Attendance as MobileAttendance;
use App\Livewire\Employee\Directory as MobileDirectory;
use App\Livewire\Employee\Home as MobileHome;
use App\Livewire\Employee\Leave as MobileLeave;
use App\Livewire\Employee\Payslip\Index as MobilePayslip;
use App\Livewire\Employee\Payslip\Show as MobilePayslipShow;
use App\Livewire\Employee\Profile\Biometric as MobileProfileBiometric;
use App\Livewire\Employee\Profile\Edit as MobileProfileEdit;
use App\Livewire\Employee\Profile\Index as MobileProfile;
use App\Livewire\Employee\RemoteWork as MobileRemoteWork;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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
	Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
	Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// Email verification
Route::middleware('auth')->group(function () {
	Route::get('/email/verify', VerifyEmail::class)->name('verification.notice');

	Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
		$request->fulfill();

		/** @var User $user */
		$user = $request->user();

		$redirectTo = $user->isAdminPanel() && $user->employee === null
			? route('admin.dashboard')
			: route('mobile.home');

		return redirect($redirectTo);
	})->middleware('signed')->name('verification.verify');
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

		Route::get('/employees', AdminEmployee::class)->name('employees')->middleware('can:manage_employees');
		Route::get('/employees/{employee}', AdminEmployeeShow::class)->name('employees.show')->middleware('can:manage_employees');

		Route::get('/attendance', AdminAttendance::class)->name('attendance')->middleware('can:manage_attendance');
		Route::get('/remote-work', AdminRemoteWork::class)->name('remote-work')->middleware('can:manage_remote_work');
		Route::get('/leave', AdminLeave::class)->name('leave')->middleware('can:manage_leave');

		Route::get('/shift', AdminShift::class)->name('shift')->middleware('can:manage_shifts');
		Route::get('/payroll', AdminPayroll::class)->name('payroll')->middleware('can:manage_payroll');
		Route::get('/payroll/payslip/{payroll}', AdminPayslip::class)->name('payroll.payslip')->middleware('can:manage_payroll');
		Route::get('/payroll/{period}/pdf', PayrollPdfController::class)->name('payroll.period.pdf')->middleware('can:manage_payroll');
		Route::get('/payroll/{period}', AdminPayrollShow::class)->name('payroll.show')->middleware('can:manage_payroll');
		Route::get('/announcements', AdminAnnouncements::class)->name('announcements')->middleware('can:manage_announcements');
		Route::get('/holidays', AdminHoliday::class)->name('holidays')->middleware('can:manage_holidays');
		Route::get('/office-locations', AdminOfficeLocation::class)->name('office-locations')->middleware('can:manage_office_locations');

		// Pengaturan (SuperAdmin only)
		Route::get('/users', AdminUserManagement::class)->name('users')->middleware('can:manage_users');
		Route::get('/access-control', AdminAccessControl::class)->name('access-control')->middleware('can:manage_users');
		Route::get('/activity-log', AdminActivityLog::class)->name('activity-log')->middleware('can:manage_users');

		Route::get('/profile', AdminProfileEdit::class)->name('profile');
	});

// --- Mobile (PWA) — hanya untuk karyawan aktif ---
Route::middleware(['auth', 'employee.active'])
	->prefix('m')
	->name('mobile.')
	->group(function () {
		Route::get('/', MobileHome::class)->name('home');
		Route::get('/attendance', MobileAttendance::class)->name('attendance');
		Route::get('/directory', MobileDirectory::class)->name('directory');
		Route::get('/payslip', MobilePayslip::class)->name('payslip');
		Route::get('/payslip/{payroll}', MobilePayslipShow::class)->name('payslip.show');
		Route::get('/payslip/{payroll}/pdf', PayslipPdfController::class)->name('payslip.pdf');
		Route::get('/profile', MobileProfile::class)->name('profile');
		Route::get('/profile/edit', MobileProfileEdit::class)->name('profile.edit');
		Route::get('/profile/biometric', MobileProfileBiometric::class)->name('profile.biometric');
		Route::get('/announcements/{announcement}', MobileAnnouncementShow::class)->name('announcements.show');
		Route::get('/remote-work', MobileRemoteWork::class)->name('remote-work');
		Route::get('/leave', MobileLeave::class)->name('leave');
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
