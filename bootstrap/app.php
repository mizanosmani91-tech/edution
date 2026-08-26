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
        // ⚠️ সার্ভার Cloudflare + nginx + Apache প্রক্সি চেইনের পেছনে থাকায়
        // Laravel কে বলে দিচ্ছি সব প্রক্সি থেকে আসা X-Forwarded-* হেডার
        // বিশ্বাস করতে — নাহলে request()->isSecure() সবসময় false আসে,
        // ফলে redirect/url হেল্পার ভুল করে http:// জেনারেট করে এবং
        // Cloudflare এর "Always Use HTTPS" এর সাথে মিলে infinite redirect
        // loop তৈরি করে।
        $middleware->trustProxies(at: '*');

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
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });

        // ⚠️ রিয়েল-টাইম এরর অ্যালার্ট: প্রোডাকশনে (production env) যেকোনো
        // অপ্রত্যাশিত সার্ভার-সাইড এরর (৫xx) ঘটলে সাথে সাথে সুপারএডমিনদের
        // এসএমএস+ইমেইলে সঠিক কারণ (ক্লাস, মেসেজ, ফাইল:লাইন) জানিয়ে দেয় —
        // ৪xx (validation, 404, auth) বাদ দিয়ে, যাতে স্প্যাম না হয়।
        $exceptions->reportable(function (\Throwable $e) {
            if (! app()->environment('production')) {
                return;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException
                || $e instanceof \Illuminate\Session\TokenMismatchException) {
                return;
            }

            if (method_exists($e, 'getStatusCode') && $e->getStatusCode() < 500) {
                return;
            }

            try {
                app(\App\Services\NotificationService::class)->systemErrorAlert($e);
            } catch (\Throwable $inner) {
                \Illuminate\Support\Facades\Log::warning('systemErrorAlert নিজেই ব্যর্থ হয়েছে: ' . $inner->getMessage());
            }
        });
    })->create();
