<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * StudentList — রেফারেন্স Livewire কম্পোনেন্ট, বাকি সব list পেজ (Teachers,
 * FeeCollections...) এই একই প্যাটার্নে বানাবেন।
 *
 * লক্ষ্য করুন: এখানেও institution_id নিয়ে কিছু লেখা লাগেনি — global scope
 * (BelongsToTenant) Livewire কম্পোনেন্টেও একই ভাবে কাজ করে, কারণ এটা
 * request lifecycle-এর মধ্যেই চলে (SetTenantContext middleware আগে রান হয়)।
 */
class StudentList extends Component
{
    use WithPagination;

    public string $search = '';

    // search টাইপ করার সময় প্রতিটা কী-স্ট্রোকে পেজ ১ এ রিসেট
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $students = Student::with(['schoolClass', 'section'])
            ->when($this->search, fn ($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('student_id_no', 'ilike', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.student-list', ['students' => $students])->layout('components.layouts.app');
    }
}
