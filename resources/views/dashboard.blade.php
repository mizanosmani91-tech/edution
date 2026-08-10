<x-layouts.app>
    <div class="p-4 md:p-6">
        <h1 class="text-xl font-semibold text-gray-900">স্বাগতম, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-gray-500">{{ auth()->user()->institution->name }}</p>
    </div>
    @livewire('dashboard-stats')
</x-layouts.app>
