<?php

namespace App\Livewire;

use App\Models\PerformanceReview;
use App\Models\Teacher;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class PerformanceReviewManager extends Component
{
    use WithPagination;

    public bool $showModal = false;

    #[Validate('required|uuid')]
    public string $teacherId = '';

    #[Validate('required|string|max:80')]
    public string $reviewPeriod = '';

    #[Validate('required|date')]
    public string $reviewDate = '';

    public int $teachingQuality = 3;
    public int $punctuality = 3;
    public int $discipline = 3;
    public int $cooperation = 3;

    public string $strengths = '';
    public string $improvementAreas = '';

    public function openModal(): void
    {
        $this->reset(['teacherId', 'reviewPeriod', 'strengths', 'improvementAreas']);
        $this->teachingQuality = $this->punctuality = $this->discipline = $this->cooperation = 3;
        $this->reviewDate = now()->toDateString();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        PerformanceReview::create([
            'teacher_id' => $this->teacherId,
            'review_period' => $this->reviewPeriod,
            'review_date' => $this->reviewDate,
            'teaching_quality' => $this->teachingQuality,
            'punctuality' => $this->punctuality,
            'discipline' => $this->discipline,
            'cooperation' => $this->cooperation,
            'strengths' => $this->strengths ?: null,
            'improvement_areas' => $this->improvementAreas ?: null,
            'reviewed_by' => auth()->id(),
        ]);

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        PerformanceReview::findOrFail($id)->delete();
    }

    public function render()
    {
        $reviews = PerformanceReview::with('teacher')->latest('review_date')->paginate(15);

        return view('livewire.performance-review-manager', [
            'reviews' => $reviews,
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Performance / মূল্যায়ন']);
    }
}
