<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Where to send people who are not logged in
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('admin', 'admin/*') ? route('admin.login') : route('login');
        });

        // Where to send people who are already logged in and hit a login page
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            return $user && $user->isStaff() ? route('admin.dashboard') : route('portal.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
