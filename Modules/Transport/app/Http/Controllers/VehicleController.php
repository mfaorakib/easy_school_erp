<?php

namespace Modules\Transport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Transport\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        return view('transport::vehicles.index', ['vehicles' => Vehicle::orderBy('vehicle_no')->get()]);
    }

    public function create()
    {
        return view('transport::vehicles.form', ['vehicle' => new Vehicle]);
    }

    public function store(Request $request)
    {
        Vehicle::create($this->validated($request));

        return redirect()->route('transport.vehicles.index')->with('status', 'Vehicle added.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('transport::vehicles.form', ['vehicle' => $vehicle]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $vehicle->update($this->validated($request));

        return redirect()->route('transport.vehicles.index')->with('status', 'Vehicle updated.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('transport.vehicles.index')->with('status', 'Vehicle deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'vehicle_no'     => ['required', 'string', 'max:50'],
            'model'          => ['nullable', 'string', 'max:150'],
            'driver_name'    => ['nullable', 'string', 'max:150'],
            'driver_phone'   => ['nullable', 'string', 'max:50'],
            'driver_license' => ['nullable', 'string', 'max:100'],
            'capacity'       => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
