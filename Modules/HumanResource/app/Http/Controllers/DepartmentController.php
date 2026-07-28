<?php

namespace Modules\HumanResource\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Foundation\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('humanresource::departments.index', ['departments' => Department::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('humanresource::departments.form', ['department' => new Department]);
    }

    public function store(Request $request)
    {
        Department::create($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('hr.departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department)
    {
        return view('humanresource::departments.form', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $department->update($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('hr.departments.index')->with('status', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('hr.departments.index')->with('status', 'Department deleted.');
    }
}
