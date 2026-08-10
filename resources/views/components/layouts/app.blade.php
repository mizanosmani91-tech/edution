<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Edution') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{--
        White-labeling — প্রতিষ্ঠান-ভিত্তিক রং। memory তে উল্লেখিত কৌশল:
        [data-institution-theme] স্কোপ করা CSS variable, হার্ডকোড করা রং
        বদলাতে হয় না ডজন ডজন জায়গায়, শুধু এই দুইটা variable override হয়।
        ব্যবহার: Tailwind utility এর বদলে bg-[var(--theme-primary)] ব্যবহার করুন
        নতুন কম্পোনেন্টে ব্র্যান্ড-কালার দরকার হলে।
    --}}
    <style>
        [data-institution-theme] {
            --theme-primary: {{ auth()->user()->institution?->settings?->theme_primary_color ?? '#2563eb' }};
            --theme-accent: {{ auth()->user()->institution?->settings?->theme_accent_color ?? '#16a34a' }};
        }
    </style>
</head>
<body class="bg-gray-50" data-institution-theme>
    <div class="flex min-h-screen">
        {{-- ডেস্কটপ সাইডবার — মোবাইলে সম্পূর্ণ হাইড --}}
        <aside class="hidden w-56 shrink-0 border-r border-gray-200 bg-white md:block">
            <div class="p-4">
                @if ($logo = auth()->user()->institution?->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}"
                         class="mb-2 h-8" alt="লোগো">
                @endif
                <p class="text-lg font-bold" style="color: var(--theme-primary)">
                    {{ auth()->user()->institution?->name ?? 'Edution' }}
                </p>
            </div>
            <nav class="space-y-1 px-2">
                @foreach ([
                    ['label' => 'ড্যাশবোর্ড', 'route' => 'dashboard', 'icon' => '🏠'],
                    ['label' => 'ছাত্র', 'route' => 'students.index', 'icon' => '🎓'],
                    ['label' => 'শিক্ষক', 'route' => 'teachers.index', 'icon' => '👨‍🏫'],
                    ['label' => 'উপস্থিতি', 'route' => 'attendance.index', 'icon' => '✅'],
                    ['label' => 'ফি', 'route' => 'fees.index', 'icon' => '💰'],
                    ['label' => 'রুটিন', 'route' => 'routine.index', 'icon' => '📅'],
                    ['label' => 'মেসেজ', 'route' => 'chat.index', 'icon' => '💬'],
                    ['label' => 'সেটিংস', 'route' => 'settings.index', 'icon' => '⚙️'],
                ] as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- মেইন কন্টেন্ট — মোবাইলে নিচে bottom-nav এর জন্য padding রাখা হলো --}}
        <main class="flex-1 pb-20 md:pb-0">
            <div class="flex justify-end border-b border-gray-200 bg-white p-3 md:hidden">
                @livewire('notification-bell')
            </div>
            {{ $slot }}
        </main>
    </div>

    {{-- ডেস্কটপে top-right এ bell --}}
    <div class="fixed right-4 top-4 hidden md:block">
        @livewire('notification-bell')
    </div>

    {{-- মোবাইল bottom navigation — ডেস্কটপে হাইড --}}
    <nav class="fixed bottom-0 left-0 right-0 z-40 flex border-t border-gray-200 bg-white md:hidden"
         style="padding-bottom: env(safe-area-inset-bottom)">
        @foreach ([
            ['label' => 'হোম', 'route' => 'dashboard', 'icon' => '🏠'],
            ['label' => 'ছাত্র', 'route' => 'students.index', 'icon' => '🎓'],
            ['label' => 'উপস্থিতি', 'route' => 'attendance.index', 'icon' => '✅'],
            ['label' => 'মেসেজ', 'route' => 'chat.index', 'icon' => '💬'],
            ['label' => 'আরও', 'route' => 'settings.index', 'icon' => '⋯'],
        ] as $item)
            <a href="{{ route($item['route']) }}"
               class="flex flex-1 flex-col items-center gap-0.5 py-2.5 text-xs
                   {{ request()->routeIs($item['route']) ? 'text-blue-600' : 'text-gray-500' }}">
                <span class="text-lg">{{ $item['icon'] }}</span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    @livewireScripts
</body>
</html>
