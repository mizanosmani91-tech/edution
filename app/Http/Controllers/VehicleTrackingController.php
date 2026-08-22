<?php

namespace App\Http\Controllers;

use App\Models\TransportRoute;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * VehicleTrackingController — ড্রাইভারের ফোন থেকে লাইভ GPS লোকেশন নেওয়া
 * ও দেখানোর পাবলিক (auth-বিহীন) এন্ডপয়েন্ট।
 *
 * ⚠️ tenant scoping নোট: এই দুইটা মেথডই পাবলিক লিংক দিয়ে খোলা হয় (ড্রাইভার
 * লগইন করা থাকে না), তাই request-এ tenant.institution_id বাইন্ড থাকে না।
 * TransportRoute মডেল BelongsToTenant ব্যবহার করে যেটা fail-closed —
 * tenant context ছাড়া query করলে RuntimeException ছোঁড়ে। তাই এখানে
 * ইচ্ছাকৃতভাবে TransportRoute::allTenants() ব্যবহার করা হয়েছে —
 * এটা নিরাপদ কারণ tracking_token একটা unique, গ্লোবালি-র‍্যান্ডম (৩২ ক্যারেক্টার)
 * টোকেন দিয়ে লুকআপ হয়, tenant filter ছাড়াও সঠিক রুটটাই পাওয়া যাবে।
 */
class VehicleTrackingController extends Controller
{
    public function share(string $token): View
    {
        $route = TransportRoute::allTenants()->where('tracking_token', $token)->firstOrFail();

        return view('transport.share-location', ['route' => $route]);
    }

    public function update(Request $request, string $token): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $route = TransportRoute::allTenants()->where('tracking_token', $token)->firstOrFail();

        $route->forceFill([
            'last_lat' => $data['lat'],
            'last_lng' => $data['lng'],
            'last_location_at' => now(),
        ])->save();

        return response()->json(['ok' => true]);
    }

    /**
     * admin-facing লাইভ ম্যাপ পেজের জন্য routes-এর বর্তমান লোকেশন JSON (auth লাগবে,
     * tenant scope স্বাভাবিকভাবেই এনফোর্স হবে কারণ এটা সাধারণ tenant route)।
     */
    public function positions(): JsonResponse
    {
        $routes = TransportRoute::whereNotNull('last_location_at')->get([
            'id', 'route_name', 'vehicle_no', 'driver_name', 'driver_phone', 'last_lat', 'last_lng', 'last_location_at',
        ]);

        return response()->json($routes->map(function (TransportRoute $r) {
            return [
                'id' => $r->id,
                'route_name' => $r->route_name,
                'vehicle_no' => $r->vehicle_no,
                'driver_name' => $r->driver_name,
                'driver_phone' => $r->driver_phone,
                'lat' => (float) $r->last_lat,
                'lng' => (float) $r->last_lng,
                'is_live' => $r->isLocationLive(),
                'updated_at' => $r->last_location_at?->diffForHumans(),
            ];
        }));
    }
}
