<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * PwaController — প্রতিটা প্রতিষ্ঠানের নিজস্ব সাবডোমেইন (<slug>.edution.xyz)
 * ব্রাউজারের জন্য আলাদা origin, তাই manifest.webmanifest ডাইনামিকভাবে তৈরি
 * করে সেই প্রতিষ্ঠানের নাম/লোগো দিয়ে — ফলে হোম স্ক্রিনে "ইনস্টল" করলে
 * প্রতিষ্ঠানের নিজস্ব নাম ও লোগো দেখাবে, সবার জন্য একই "EDUTION" আইকন না।
 *
 * panel.edution.xyz (সুপারএডমিন) ও edution.xyz (ল্যান্ডিং/রেজিস্ট্রেশন)
 * এর জন্য ডিফল্ট EDUTION ব্র্যান্ডিং ব্যবহার হয়।
 */
class PwaController extends Controller
{
    public function manifest(Request $request): JsonResponse
    {
        $host = $request->getHost();
        $institution = Institution::resolveFromSubdomain($host);

        $name = $institution?->name ?? 'EDUTION';
        $shortName = $institution ? mb_substr($institution->name, 0, 12) : 'EDUTION';

        $icons = [];

        if ($institution?->logo_path) {
            $logoUrl = Storage::disk('public')->url($institution->logo_path);
            $icons[] = ['src' => $logoUrl, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => $logoUrl, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'];
        } else {
            $icons[] = ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => '/icons/icon-192-maskable.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'];
            $icons[] = ['src' => '/icons/icon-512-maskable.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'];
        }

        $themeColor = $institution?->settings?->theme_primary_color ?: '#5C1A2B';

        return response()->json([
            'name' => $name,
            'short_name' => $shortName,
            'description' => $name.' — EDUTION স্কুল ম্যানেজমেন্ট প্ল্যাটফর্ম',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#F7F2E5',
            'theme_color' => $themeColor,
            'orientation' => 'portrait-primary',
            'lang' => 'bn',
            'icons' => $icons,
        ])->header('Content-Type', 'application/manifest+json');
    }
}
