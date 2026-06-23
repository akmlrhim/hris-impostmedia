<?php

use App\Http\Middleware\EnsureActiveEmployee;
use App\Http\Middleware\EnsureAdminPanel;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->appendToGroup('web', SecurityHeaders::class);

        $middleware->alias([
            'admin.panel' => EnsureAdminPanel::class,
            'employee.active' => EnsureActiveEmployee::class,
        ]);

        // Throttle the Livewire AJAX endpoint: 120 requests/minute per IP
        $middleware->web(append: [
            ThrottleRequests::class.':120,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
