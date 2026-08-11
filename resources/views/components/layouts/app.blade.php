<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'বিদ্যাপঞ্জি') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[var(--color-paper)]">
    <div class="flex min-h-screen">
        {{-- ডেস্কটপ সাইডবার — মেরুন গ্রেডিয়েন্ট + সোনালি accent --}}
        <aside class="hidden w-[278px] shrink-0 flex-col bg-[radial-gradient(140%_120%_at_0%_0%,#6E2136_0%,var(--color-maroon)_45%,var(--color-maroon-deep)_100%)] text-[#F3E9D2] md:flex">
            <div class="flex items-center gap-3 border-b border-[rgba(231,199,103,.16)] px-5 py-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[rgba(231,199,103,.55)]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#E7C767" stroke-width="1.6" class="h-5 w-5">
                        <path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/>
                        <path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/>
                    </svg>
                </div>
                <div class="overflow-hidden">
                    <p class="font-serif-bn truncate text-lg text-[var(--color-gold-light)]">বিদ্যাপঞ্জি</p>
                    <p class="truncate text-xs text-[rgba(243,233,210,.6)]">{{ auth()->user()->institution?->name ?? '' }}</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @foreach ([
                    ['label' => 'ড্যাশবোর্ড', 'route' => 'dashboard', 'icon' => '🏠'],
                    ['label' => 'শিক্ষার্থী', 'route' => 'students.index', 'icon' => '🎓'],
                    ['label' => 'শিক্ষক', 'route' => 'teachers.index', 'icon' => '👨‍🏫'],
                    ['label' => 'উপস্থিতি', 'route' => 'attendance.index', 'icon' => '✅'],
                    ['label' => 'ফি', 'route' => 'fees.index', 'icon' => '💰'],
                    ['label' => 'রুটিন', 'route' => 'routine.index', 'icon' => '📅'],
                    ['label' => 'মেসেজ', 'route' => 'chat.index', 'icon' => '💬'],
                    ['label' => 'সেটিংস', 'route' => 'settings.index', 'icon' => '⚙️'],
                ] as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                           {{ $isActive
                               ? 'bg-[rgba(231,199,103,.14)] text-[var(--color-gold-light)] before:absolute before:right-[-12px] before:top-2 before:bottom-2 before:w-[3px] before:rounded-l-md before:bg-[var(--color-gold)]'
                               : 'text-[rgba(243,233,210,.82)] hover:bg-white/[.06] hover:text-white' }}">
                        <span class="w-5 shrink-0 text-center">{{ $item['icon'] }}</span>
                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-[rgba(231,199,103,.16)] p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-[rgba(243,233,210,.65)] hover:bg-white/[.06] hover:text-white">
                        <span class="w-5 text-center">🚪</span> লগআউট
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- টপবার --}}
            <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-[var(--color-line)] bg-[rgba(247,242,229,.9)] px-5 py-3 backdrop-blur">
                <p class="font-serif-bn flex-1 truncate text-lg text-[var(--color-ink)] md:hidden">বিদ্যাপঞ্জি</p>
                <div class="ml-auto flex items-center gap-3">
                    @livewire('notification-bell')
                    <div class="hidden items-center gap-2 rounded-lg border border-[var(--color-line)] bg-white px-3 py-1.5 md:flex">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-[var(--color-gold-light)] to-[var(--color-gold)] text-xs font-bold text-[var(--color-maroon-deep)]">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span class="text-sm text-[var(--color-ink)]">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            {{-- মেইন কন্টেন্ট --}}
            <main class="flex-1 pb-20 md:pb-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- মোবাইল bottom navigation --}}
    <nav class="fixed bottom-0 left-0 right-0 z-40 flex border-t border-[var(--color-line)] bg-white md:hidden"
         style="padding-bottom: env(safe-area-inset-bottom)">
        @foreach ([
            ['label' => 'হোম', 'route' => 'dashboard', 'icon' => '🏠'],
            ['label' => 'ছাত্র', 'route' => 'students.index', 'icon' => '🎓'],
            ['label' => 'উপস্থিতি', 'route' => 'attendance.index', 'icon' => '✅'],
            ['label' => 'মেসেজ', 'route' => 'chat.index', 'icon' => '💬'],
            ['label' => 'সেটিংস', 'route' => 'settings.index', 'icon' => '⚙️'],
        ] as $item)
            <a href="{{ route($item['route']) }}"
               class="flex flex-1 flex-col items-center gap-0.5 py-2.5 text-xs
                   {{ request()->routeIs($item['route']) ? 'text-[var(--color-maroon)]' : 'text-[var(--color-ink-soft)]' }}">
                <span class="text-lg">{{ $item['icon'] }}</span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    @livewireScripts
</body>
</html>
