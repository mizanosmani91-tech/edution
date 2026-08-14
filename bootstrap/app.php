<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // ⚠️ সার্ভারের crontab এ `* * * * * php artisan schedule:run` চালু
        // না থাকলে এই দুটো কমান্ড কখনো চলবে না — deploy এর পর একবার
        // যাচাই করে নিতে হবে।
        $schedule->command('edution:notify-overdue-fees')->dailyAt('08:00');
        $schedule->command('edution:process-billing')->dailyAt('07:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.context' => \App\Http\Middleware\SetTenantContext::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'password.change' => \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);

        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\SetTenantContext::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->validateCsrfTokens(except: ['*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
    })->create();
