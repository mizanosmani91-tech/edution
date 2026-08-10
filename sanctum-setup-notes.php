<?php
/**
 * Sanctum সেটআপ (মোবাইল অ্যাপের জন্য token auth) — এই কমান্ডগুলো একবার চালান:
 *
 *   composer require laravel/sanctum
 *   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
 *   php artisan migrate
 *
 * User মডেলে trait যোগ করুন:
 *   use Laravel\Sanctum\HasApiTokens;
 *   class User extends Authenticatable {
 *       use HasApiTokens, HasFactory, Notifiable, UuidPrimaryKey;
 *   }
 *
 * routes/api.php ইতিমধ্যে RouteServiceProvider দিয়ে অটো-লোড হয় (Laravel default) —
 * আলাদা করে কিছু রেজিস্টার করা লাগবে না, শুধু middleware alias (bootstrap-app-
 * middleware-snippet.php) ঠিকমতো থাকলেই 'auth:sanctum' কাজ করবে।
 *
 * মোবাইল অ্যাপ থেকে ব্যবহার:
 *   POST /api/login  { institution_slug, email, password } → { token }
 *   পরের প্রতিটা request এ header: Authorization: Bearer {token}
 */
