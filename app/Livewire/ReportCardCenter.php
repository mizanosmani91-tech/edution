<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\SchoolClass;
use Livewire\Component;

class ReportCardCenter extends Component
{
    public function render()
    {
        return view('livewire.report-card-center', [
            'exams' => Exam::orderByDesc('start_date')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
        ])->layout('components.layouts.app', ['title' => 'রিপোর্ট কার্ড ও প্রবেশপত্র']);
    }
}
