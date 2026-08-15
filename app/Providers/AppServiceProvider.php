<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ⚠️ গুরুত্বপূর্ণ ফিক্স: সার্ভার Cloudflare + nginx + Apache এর পেছনে
        // থাকায় Laravel বুঝতে পারে না আসল রিকোয়েস্টটা https ছিল, ফলে
        // redirect()/url() হেল্পার http:// লিংক জেনারেট করছিল। Cloudflare এর
        // "Always Use HTTPS" আবার সেটাকে https এ পাঠাচ্ছিল — এতে অসীম
        // redirect loop (ERR_TOO_MANY_REDIRECTS) তৈরি হচ্ছিল, বিশেষত
        // tenant subdomain গুলোতে (যেমন flourish.edution.xyz)।
        // APP_URL সবসময় https হওয়ায় সব জেনারেটেড URL কে জোর করে https
        // স্কিমে বেঁধে দিচ্ছি।
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // ⚠️ গুরুত্বপূর্ণ ফিক্স: Livewire এর প্রতিটা wire:click/wire:model
        // action আলাদা AJAX request পাঠায় নিজস্ব /livewire/update route এ,
        // যেখানে আমাদের কাস্টম 'tenant.context' middleware ডিফল্টে attach
        // থাকে না। ফলে Livewire component এর কোনো method call করলেই
        // (যেমন modal খোলা, save করা) tenant context হারিয়ে যাচ্ছিল।
        //
        // setUpdateRoute দিয়ে Livewire কে বলে দিচ্ছি এই route টাও যেন
        // 'web' + 'auth' + 'tenant.context' middleware দিয়েই চলে —
        // পেজ লোড রুটের মতোই একই security guarantee সব Livewire action এ।
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web', 'auth', 'tenant.context']);
        });
    }
}
