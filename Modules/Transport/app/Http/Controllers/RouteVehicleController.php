<?php

namespace Modules\Transport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Models\Vehicle;

class RouteVehicleController extends Controller
{
    public function index()
    {
        return view('transport::assign_vehicles.index', [
            'routes'   => TransportRoute::with('vehicles')->orderBy('name')->get(),
            'vehicles' => Vehicle::where('is_active', true)->orderBy('vehicle_no')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transport_route_id' => ['required', 'exists:transport_routes,id'],
            'vehicles'           => ['array'],
            'vehicles.*'         => ['integer', 'exists:vehicles,id'],
        ]);

        TransportRoute::findOrFail($data['transport_route_id'])
            ->vehicles()
            ->sync($request->input('vehicles', []));

        return redirect()->route('transport.assign-vehicles.index')->with('status', 'Vehicles assigned.');
    }
}
