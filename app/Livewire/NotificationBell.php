<?php

namespace App\Livewire;

use App\Models\AppNotification;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function markAsRead(string $id): void
    {
        // ⚠️ AppNotification এর 'owner' global scope এমনিতেই নিশ্চিত করে
        // এটা auth()->id() এর নিজের notification, তাই আলাদা owner-check
        // লাগছে না — findOrFail নিজেই scope অনুযায়ী ফিল্টার করবে
        AppNotification::findOrFail($id)->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        AppNotification::whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        $notifications = AppNotification::latest()->limit(10)->get();
        $unreadCount = AppNotification::whereNull('read_at')->count();

        return view('livewire.notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
