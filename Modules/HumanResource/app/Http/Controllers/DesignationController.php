<?php

namespace Modules\HumanResource\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Foundation\Models\Designation;

class DesignationController extends Controller
{
    public function index()
    {
        return view('humanresource::designations.index', ['designations' => Designation::orderBy('title')->get()]);
    }

    public function create()
    {
        return view('humanresource::designations.form', ['designation' => new Designation]);
    }

    public function store(Request $request)
    {
        Designation::create($request->validate(['title' => ['required', 'string', 'max:150']]));

        return redirect()->route('hr.designations.index')->with('status', 'Designation created.');
    }

    public function edit(Designation $designation)
    {
        return view('humanresource::designations.form', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $designation->update($request->validate(['title' => ['required', 'string', 'max:150']]));

        return redirect()->route('hr.designations.index')->with('status', 'Designation updated.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();

        return redirect()->route('hr.designations.index')->with('status', 'Designation deleted.');
    }
}
