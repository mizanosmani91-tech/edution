<?php

/**
 * এই স্নিপেটটা আপনার bootstrap/app.php ফাইলের ->withMiddleware() অংশে বসান
 * (Laravel 11 এর নতুন structure)। Laravel 10 বা তার আগে হলে
 * app/Http/Kernel.php এর $middlewareAliases এ যোগ করুন — নিচে দুইটাই আছে।
 */

// ───── Laravel 11 (bootstrap/app.php) ─────
// ->withMiddleware(function (Middleware $middleware) {
//     $middleware->alias([
//         'tenant.context' => \App\Http\Middleware\SetTenantContext::class,
//         'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
//     ]);
// })

// ───── Laravel 10 বা আগে (app/Http/Kernel.php) ─────
// protected $middlewareAliases = [
//     ...
//     'tenant.context' => \App\Http\Middleware\SetTenantContext::class,
//     'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
// ];

/**
 * config/database.php এ pgsql connection — অবশ্যই non-superuser role:
 *
 * 'pgsql' => [
 *     'driver' => 'pgsql',
 *     'host' => env('DB_HOST', '142.44.162.156'),
 *     'port' => env('DB_PORT', '5432'),
 *     'database' => env('DB_DATABASE', 'edution'),
 *     'username' => env('DB_USERNAME'), // ⚠️ 'postgres' superuser না — RLS বাইপাস হয়ে যাবে
 *     'password' => env('DB_PASSWORD'),
 *     'sslmode' => 'prefer',
 * ],
 */
