<?php

namespace Modules\Transport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Transport\Models\TransportRoute;

class RouteController extends Controller
{
    public function index()
    {
        return view('transport::routes.index', ['routes' => TransportRoute::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('transport::routes.form', ['route' => new TransportRoute]);
    }

    public function store(Request $request)
    {
        TransportRoute::create($this->validated($request));

        return redirect()->route('transport.routes.index')->with('status', 'Route added.');
    }

    public function edit(TransportRoute $route)
    {
        return view('transport::routes.form', ['route' => $route]);
    }

    public function update(Request $request, TransportRoute $route)
    {
        $route->update($this->validated($request));

        return redirect()->route('transport.routes.index')->with('status', 'Route updated.');
    }

    public function destroy(TransportRoute $route)
    {
        $route->delete();

        return redirect()->route('transport.routes.index')->with('status', 'Route deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'fare'        => ['required', 'numeric', 'min:0'],
            'start_point' => ['nullable', 'string', 'max:150'],
            'end_point'   => ['nullable', 'string', 'max:150'],
            'note'        => ['nullable', 'string'],
        ]);
    }
}
