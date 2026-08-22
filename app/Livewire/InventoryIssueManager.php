<?php

namespace App\Livewire;

use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InventoryIssueManager extends Component
{
    public string $tab = 'issued'; // issued / overdue / returned

    public function mount(string $tab = 'issued'): void
    {
        $this->tab = $tab;
    }

    public bool $showModal = false;

    #[Validate('required|exists:inventory_items,id')]
    public string $itemId = '';

    #[Validate('required|integer|min:1')]
    public string $quantity = '1';

    #[Validate('required|string|min:2')]
    public string $issuedTo = '';

    public string $expectedReturnAt = '';

    public function openModal(): void
    {
        $this->reset(['itemId', 'issuedTo']);
        $this->quantity = '1';
        $this->expectedReturnAt = '';
        $this->showModal = true;
    }

    public function issue(): void
    {
        $this->validate();

        $item = InventoryItem::findOrFail($this->itemId);
        $qty = (int) $this->quantity;

        if ($item->quantity_available < $qty) {
            $this->addError('itemId', "এই আইটেমের মাত্র {$item->quantity_available}টা উপলব্ধ আছে।");

            return;
        }

        InventoryIssue::create([
            'item_id' => $this->itemId,
            'quantity' => $qty,
            'issued_to' => trim($this->issuedTo),
            'issued_by' => auth()->id(),
            'issued_at' => now()->toDateString(),
            'expected_return_at' => $this->expectedReturnAt ?: null,
            'status' => 'issued',
        ]);

        $item->decrement('quantity_available', $qty);

        $this->showModal = false;
    }

    public function markReturned(string $id): void
    {
        $issue = InventoryIssue::findOrFail($id);

        if ($issue->status !== 'issued') {
            return;
        }

        $issue->update([
            'returned_at' => now()->toDateString(),
            'status' => 'returned',
        ]);

        $issue->item->increment('quantity_available', $issue->quantity);
    }

    public function markLost(string $id): void
    {
        $issue = InventoryIssue::findOrFail($id);

        if ($issue->status !== 'issued') {
            return;
        }

        // ⚠️ হারিয়ে গেলে quantity_available বাড়ানো হয় না — সেই ইউনিট চিরতরে
        // কমে গেল ধরা হয় (quantity_total ম্যানুয়ালি কমিয়ে আইটেম সম্পাদনা করতে হবে)
        $issue->update(['status' => 'lost']);
    }

    public function render()
    {
        $query = InventoryIssue::with('item');

        if ($this->tab === 'issued') {
            $query->where('status', 'issued')
                ->where(fn ($q) => $q->whereNull('expected_return_at')->orWhere('expected_return_at', '>=', now()->toDateString()));
        } elseif ($this->tab === 'overdue') {
            $query->where('status', 'issued')->where('expected_return_at', '<', now()->toDateString());
        } elseif ($this->tab === 'lost') {
            $query->where('status', 'lost');
        } else {
            $query->where('status', 'returned');
        }

        return view('livewire.inventory-issue-manager', [
            'issues' => $query->latest('issued_at')->limit(100)->get(),
            'items' => InventoryItem::where('quantity_available', '>', 0)->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'ইস্যু ও রিটার্ন — ইনভেন্টরি']);
    }
}
