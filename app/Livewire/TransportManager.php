<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\StudentTransport;
use App\Models\TransportRoute;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TransportManager extends Component
{
    public string $tab = 'routes'; // routes / assignments

    public bool $showRouteModal = false;
    public ?string $editingRouteId = null;

    #[Validate('required|string|max:100')]
    public string $routeName = '';
    public string $vehicleNo = '';
    public string $driverName = '';
    public string $driverPhone = '';

    #[Validate('required|integer|min:0')]
    public string $capacity = '0';

    #[Validate('required|numeric|min:0')]
    public string $monthlyFee = '0';

    public bool $showAssignModal = false;

    #[Validate('required|exists:students,id')]
    public string $studentId = '';

    #[Validate('required|exists:transport_routes,id')]
    public string $routeId = '';

    public function openRouteModal(?string $id = null): void
    {
        $this->reset(['routeName', 'vehicleNo', 'driverName', 'driverPhone']);
        $this->capacity = '0';
        $this->monthlyFee = '0';
        $this->editingRouteId = $id;

        if ($id) {
            $r = TransportRoute::findOrFail($id);
            $this->routeName = $r->route_name;
            $this->vehicleNo = $r->vehicle_no ?? '';
            $this->driverName = $r->driver_name ?? '';
            $this->driverPhone = $r->driver_phone ?? '';
            $this->capacity = (string) $r->capacity;
            $this->monthlyFee = (string) $r->monthly_fee;
        }

        $this->showRouteModal = true;
    }

    public function saveRoute(): void
    {
        $this->validate([
            'routeName' => 'required|string|max:100',
            'capacity' => 'required|integer|min:0',
            'monthlyFee' => 'required|numeric|min:0',
        ]);

        TransportRoute::updateOrCreate(
            ['id' => $this->editingRouteId],
            [
                'route_name' => $this->routeName,
                'vehicle_no' => $this->vehicleNo ?: null,
                'driver_name' => $this->driverName ?: null,
                'driver_phone' => $this->driverPhone ?: null,
                'capacity' => (int) $this->capacity,
                'monthly_fee' => $this->monthlyFee,
            ]
        );

        $this->showRouteModal = false;
    }

    public function deleteRoute(string $id): void
    {
        TransportRoute::findOrFail($id)->delete();
    }

    public function openAssignModal(): void
    {
        $this->reset(['studentId', 'routeId']);
        $this->showAssignModal = true;
    }

    public function assign(): void
    {
        $this->validate([
            'studentId' => 'required|exists:students,id',
            'routeId' => 'required|exists:transport_routes,id',
        ]);

        StudentTransport::updateOrCreate(
            ['student_id' => $this->studentId],
            ['route_id' => $this->routeId, 'assigned_at' => now()->toDateString()]
        );

        $this->showAssignModal = false;
    }

    public function unassign(string $id): void
    {
        StudentTransport::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.transport-manager', [
            'routes' => TransportRoute::withCount('assignments')->orderBy('route_name')->get(),
            'assignments' => StudentTransport::with(['student', 'route'])->latest('assigned_at')->limit(200)->get(),
            'students' => Student::orderBy('name')->limit(300)->get(),
            'allRoutes' => TransportRoute::orderBy('route_name')->get(),
        ])->layout('components.layouts.app', ['title' => 'পরিবহন']);
    }
}
