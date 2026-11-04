<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Permissions
 *
 * config/permissions.php-এর ক্যাটালগ ও রোলের ডিফল্ট পারমিশন পড়ার একমাত্র জায়গা।
 * ধাপ ২-এ প্রতিষ্ঠানভিত্তিক কাস্টম রোল যোগ হলে শুধু `roleHas()` বদলাবে।
 */
class Permissions
{
    /** @return array<string,string> চাবি => বাংলা লেবেল */
    public static function catalog(): array
    {
        return config('permissions.permissions', []);
    }

    public static function isKnown(string $permission): bool
    {
        return array_key_exists($permission, self::catalog());
    }

    /** এই রোল ডিফল্টভাবে এই চাবি পায় কিনা। অচেনা রোল বা অচেনা চাবি = না (default-deny)। */
    public static function roleHas(?string $role, string $permission): bool
    {
        if ($role === null || ! self::isKnown($permission)) {
            return false;
        }

        $granted = config("permissions.defaults.{$role}", []);

        return in_array('*', $granted, true) || in_array($permission, $granted, true);
    }

    /**
     * কোনো নামকরা রুট এই ইউজারের জন্য খোলা কিনা — সাইডবার লিংক লুকানোর জন্য।
     * রুটে `permission:` মিডলওয়্যার না থাকলে (সবার জন্য খোলা) true।
     * ⚠️ এটা শুধু UI-র সুবিধা; আসল আটকানো হয় EnsurePermission মিডলওয়্যারে।
     */
    public static function routeAllowed(string $routeName, ?User $user = null): bool
    {
        $user ??= auth()->user();
        $route = Route::getRoutes()->getByName($routeName);

        if (! $route || ! $user) {
            return false;
        }

        $required = self::requiredPermissions($route->gatherMiddleware());

        if ($required === []) {
            return true;
        }

        foreach ($required as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * মিডলওয়্যার তালিকা থেকে `permission:a,b` এর চাবিগুলো (OR) বের করে।
     *
     * @param  array<int,mixed>  $middleware
     * @return array<int,string>
     */
    public static function requiredPermissions(array $middleware): array
    {
        $keys = [];

        foreach ($middleware as $m) {
            if (is_string($m) && str_starts_with($m, 'permission:')) {
                array_push($keys, ...explode(',', substr($m, strlen('permission:'))));
            }
        }

        return $keys;
    }
}
