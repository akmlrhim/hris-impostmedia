<?php

use App\Http\Middleware\EnsureActiveEmployee;
use App\Http\Middleware\EnsureAdminPanel;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
