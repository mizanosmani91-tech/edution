<?php

namespace App\Livewire;

use App\Models\HostelRoom;
use App\Models\Student;
use App\Models\StudentHostel;
use Livewire\Attributes\Validate;
use Livewire\Component;

class HostelManager extends Component
{
    public string $tab = 'rooms'; // rooms / fees

    public bool $showRoomModal = false;
    public ?string $editingRoomId = null;

    #[Validate('required|string|max:30')]
    public string $roomNo = '';
    public string $roomType = '';

    #[Validate('required|integer|min:1')]
    public string $capacity = '1';

    #[Validate('required|numeric|min:0')]
    public string $monthlyFee = '0';

    public bool $showAssignModal = false;

    #[Validate('required|exists:students,id')]
    public string $studentId = '';

    #[Validate('required|exists:hostel_rooms,id')]
    public string $roomId = '';

    public function openRoomModal(?string $id = null): void
    {
        $this->reset(['roomNo', 'roomType']);
        $this->capacity = '1';
        $this->monthlyFee = '0';
        $this->editingRoomId = $id;

        if ($id) {
            $r = HostelRoom::findOrFail($id);
            $this->roomNo = $r->room_no;
            $this->roomType = $r->room_type ?? '';
            $this->capacity = (string) $r->capacity;
            $this->monthlyFee = (string) $r->monthly_fee;
        }

        $this->showRoomModal = true;
    }

    public function saveRoom(): void
    {
        $this->validate([
            'roomNo' => 'required|string|max:30',
            'capacity' => 'required|integer|min:1',
            'monthlyFee' => 'required|numeric|min:0',
        ]);

        HostelRoom::updateOrCreate(
            ['id' => $this->editingRoomId],
            [
                'room_no' => $this->roomNo,
                'room_type' => $this->roomType ?: null,
                'capacity' => (int) $this->capacity,
                'monthly_fee' => $this->monthlyFee,
            ]
        );

        $this->showRoomModal = false;
    }

    public function deleteRoom(string $id): void
    {
        HostelRoom::findOrFail($id)->delete();
    }

    public function openAssignModal(): void
    {
        $this->reset(['studentId', 'roomId']);
        $this->showAssignModal = true;
    }

    public function assign(): void
    {
        $this->validate([
            'studentId' => 'required|exists:students,id',
            'roomId' => 'required|exists:hostel_rooms,id',
        ]);

        StudentHostel::updateOrCreate(
            ['student_id' => $this->studentId],
            ['room_id' => $this->roomId, 'check_in_date' => now()->toDateString()]
        );

        $this->showAssignModal = false;
    }

    public function checkout(string $id): void
    {
        StudentHostel::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.hostel-manager', [
            'rooms' => HostelRoom::withCount('residents')->orderBy('room_no')->get(),
            'residents' => StudentHostel::with(['student', 'room'])->latest('check_in_date')->limit(200)->get(),
            'students' => Student::orderBy('name')->limit(300)->get(),
            'allRooms' => HostelRoom::orderBy('room_no')->get(),
        ])->layout('components.layouts.app', ['title' => 'হোস্টেল']);
    }
}
