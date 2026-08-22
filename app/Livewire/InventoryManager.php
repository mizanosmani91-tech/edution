<?php

namespace App\Livewire;

use App\Models\InventoryItem;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InventoryManager extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;
    public string $search = '';
    public string $categoryFilter = '';

    #[Validate('required|string|max:150')]
    public string $name = '';

    public string $category = '';
    public string $assetTag = '';

    #[Validate('required|integer|min:1')]
    public string $quantityTotal = '1';

    public string $unit = 'পিস';
    public string $purchaseDate = '';
    public string $purchasePrice = '';
    public string $condition = 'good';
    public string $location = '';
    public string $remarks = '';

    public function openModal(?string $id = null): void
    {
        $this->reset(['name', 'category', 'assetTag', 'purchaseDate', 'purchasePrice', 'location', 'remarks']);
        $this->quantityTotal = '1';
        $this->unit = 'পিস';
        $this->condition = 'good';
        $this->editingId = $id;

        if ($id) {
            $item = InventoryItem::findOrFail($id);
            $this->name = $item->name;
            $this->category = $item->category ?? '';
            $this->assetTag = $item->asset_tag ?? '';
            $this->quantityTotal = (string) $item->quantity_total;
            $this->unit = $item->unit;
            $this->purchaseDate = $item->purchase_date?->toDateString() ?? '';
            $this->purchasePrice = $item->purchase_price !== null ? (string) $item->purchase_price : '';
            $this->condition = $item->condition;
            $this->location = $item->location ?? '';
            $this->remarks = $item->remarks ?? '';
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $item = InventoryItem::findOrFail($this->editingId);
            $diff = (int) $this->quantityTotal - $item->quantity_total;

            $item->update([
                'name' => $this->name,
                'category' => $this->category ?: null,
                'asset_tag' => $this->assetTag ?: null,
                'quantity_total' => (int) $this->quantityTotal,
                'quantity_available' => max(0, $item->quantity_available + $diff),
                'unit' => $this->unit ?: 'পিস',
                'purchase_date' => $this->purchaseDate ?: null,
                'purchase_price' => $this->purchasePrice !== '' ? $this->purchasePrice : null,
                'condition' => $this->condition,
                'location' => $this->location ?: null,
                'remarks' => $this->remarks ?: null,
            ]);
        } else {
            InventoryItem::create([
                'name' => $this->name,
                'category' => $this->category ?: null,
                'asset_tag' => $this->assetTag ?: null,
                'quantity_total' => (int) $this->quantityTotal,
                'quantity_available' => (int) $this->quantityTotal,
                'unit' => $this->unit ?: 'পিস',
                'purchase_date' => $this->purchaseDate ?: null,
                'purchase_price' => $this->purchasePrice !== '' ? $this->purchasePrice : null,
                'condition' => $this->condition,
                'location' => $this->location ?: null,
                'remarks' => $this->remarks ?: null,
            ]);
        }

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        InventoryItem::findOrFail($id)->delete();
    }

    public function render()
    {
        $items = InventoryItem::when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy('name')
            ->get();

        $categories = InventoryItem::whereNotNull('category')->distinct()->pluck('category');

        return view('livewire.inventory-manager', [
            'items' => $items,
            'categories' => $categories,
        ])->layout('components.layouts.app', ['title' => 'ইনভেন্টরি/অ্যাসেট']);
    }
}
