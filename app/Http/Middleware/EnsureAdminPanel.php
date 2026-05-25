<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPanel
{
	public function handle(Request $request, Closure $next): Response
	{
		$user = $request->user();

		if (! $user || ! $user->isAdminPanel()) {
			abort(403, 'Akses ditolak. Hanya untuk admin/HR.');
		}

		return $next($request);
	}
}
