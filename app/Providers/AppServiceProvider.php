<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
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
