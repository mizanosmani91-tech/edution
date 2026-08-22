<?php

namespace App\Livewire;

use App\Models\TransportRoute;
use Livewire\Component;

/**
 * VehicleTrackingMap — এডমিন প্যানেলে সব গাড়ির লাইভ অবস্থান দেখার পেজ।
 * ম্যাপ নিজে JS (Leaflet.js + OpenStreetMap, ফ্রি, কোনো API key লাগে না) দিয়ে
 * আঁকা হয় এবং periodic polling দিয়ে /transport-tracking/positions থেকে ডেটা টানে
 * (দেখুন VehicleTrackingController::positions())। শেয়ার-লিংক এখানেই তৈরি হয়।
 */
class VehicleTrackingMap extends Component
{
    public function render()
    {
        $routes = TransportRoute::orderBy('route_name')->get();

        return view('livewire.vehicle-tracking-map', [
            'routes' => $routes,
        ])->layout('components.layouts.app', ['title' => 'লাইভ গাড়ি ট্র্যাকিং']);
    }
}
