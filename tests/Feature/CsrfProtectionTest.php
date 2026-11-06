<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * CSRF সুরক্ষা ফিরিয়ে আনার গার্ড। টেস্ট পরিবেশে Laravel নিজে CSRF চেক এড়িয়ে যায়,
 * তাই এখানে মিডলওয়্যারের "ছাড়ের তালিকা" সরাসরি যাচাই করা হয়।
 */
class CsrfProtectionTest extends TestCase
{
    private function isExempt(string $uri): bool
    {
        $middleware = app(ValidateCsrfToken::class);
        $method = new ReflectionMethod($middleware, 'inExceptArray');
        $method->setAccessible(true);

        return (bool) $method->invoke($middleware, Request::create($uri, 'POST'));
    }

    /** @return array<string,array{0:string}> */
    public static function protectedUris(): array
    {
        return [
            'logout' => ['/logout'],
            'login' => ['/login'],
            'fee collection api' => ['/fee-collections'],
            'students api' => ['/students'],
            'settings' => ['/settings'],
            'livewire update' => ['/livewire/update'],
            'chat' => ['/chat/conversations/start'],
            'tracking page (not the update)' => ['/transport-tracking/abc123'],
            'tracking positions' => ['/transport-tracking/positions'],
        ];
    }

    #[Test]
    #[DataProvider('protectedUris')]
    public function normal_routes_are_csrf_protected(string $uri): void
    {
        $this->assertFalse($this->isExempt($uri), "{$uri} CSRF-ছাড়ের তালিকায় আছে — থাকার কথা নয়");
    }

    #[Test]
    public function only_the_driver_location_update_is_exempt(): void
    {
        $this->assertTrue($this->isExempt('/transport-tracking/some-secret-token/update'));
    }

    #[Test]
    public function the_old_wildcard_exemption_is_gone(): void
    {
        $this->assertFalse($this->isExempt('/anything/at/all'));
    }
}
