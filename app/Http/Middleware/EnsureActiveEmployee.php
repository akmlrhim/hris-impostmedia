<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveEmployee
{
	public function handle(Request $request, Closure $next): Response
	{
		$user = $request->user();

		if (! $user || ! $user->is_active) {
			return $this->rejectWithMessage($request, 'Akun Anda tidak aktif. Silakan hubungi admin.');
		}

		$employee = $user->employee;

		if (! $employee || ! $employee->is_active) {
			return $this->rejectWithMessage($request, 'Akun karyawan Anda tidak aktif. Silakan hubungi admin.');
		}

		return $next($request);
	}

	private function rejectWithMessage(Request $request, string $message): Response
	{
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();
		$request->session()->flash('warning', $message);

		return redirect()->route('login');
	}
}
