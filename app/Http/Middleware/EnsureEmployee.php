<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isEmployee()) {
            abort(403, 'Hanya karyawan yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
