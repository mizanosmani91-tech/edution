<?php

namespace App\Livewire;

use App\Services\FileUploadService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * PhotoUpload — reusable কম্পোনেন্ট, Institution logo / Student photo /
 * Teacher photo সব জায়গায় একই ব্যবহার করা যাবে। Blade থেকে ব্যবহার:
 *
 *   <livewire:photo-upload
 *       model="App\Models\Student"
 *       :model-id="$student->id"
 *       category="student-photos"
 *       :current-url="$student->photo_path ? Storage::url($student->photo_path) : null"
 *   />
 */
class PhotoUpload extends Component
{
    use WithFileUploads;

    public string $model;      // fully-qualified model class
    public string $modelId;
    public string $category;   // 'institution-logos' / 'student-photos' / 'teacher-photos'
    public ?string $currentUrl = null;

    #[Validate('nullable|image|max:2048')] // 2MB, Livewire নিজেই mime/size validate করে (UI level)
    public $photo = null;

    public function updatedPhoto(FileUploadService $uploads): void
    {
        $this->validateOnly('photo');

        // ⚠️ route model binding না — এখানে ম্যানুয়ালি ফাইন্ড, global scope
        // এর কারণে অন্য institution এর modelId দিলে এখানেই fail করবে (fail-closed)
        $modelClass = $this->model;
        $record = $modelClass::findOrFail($this->modelId);

        $oldPath = $record->photo_path ?? $record->logo_path ?? null;
        $uploads->delete($oldPath);

        $path = $uploads->store($this->photo, $this->category);

        $column = str_contains($this->category, 'logo') ? 'logo_path' : 'photo_path';
        $record->update([$column => $path]);

        $this->currentUrl = $uploads->url($path);
        $this->photo = null;
    }

    public function render()
    {
        return view('livewire.photo-upload');
    }
}
