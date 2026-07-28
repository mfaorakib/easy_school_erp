<?php

namespace Modules\Dormitory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dormitory\Models\Dormitory;

class DormitoryController extends Controller
{
    public function index()
    {
        return view('dormitory::dormitories.index', ['dormitories' => Dormitory::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('dormitory::dormitories.form', ['dormitory' => new Dormitory]);
    }

    public function store(Request $request)
    {
        Dormitory::create($this->validated($request));

        return redirect()->route('dormitory.dormitories.index')->with('status', 'Dormitory added.');
    }

    public function edit(Dormitory $dormitory)
    {
        return view('dormitory::dormitories.form', ['dormitory' => $dormitory]);
    }

    public function update(Request $request, Dormitory $dormitory)
    {
        $dormitory->update($this->validated($request));

        return redirect()->route('dormitory.dormitories.index')->with('status', 'Dormitory updated.');
    }

    public function destroy(Dormitory $dormitory)
    {
        $dormitory->delete();

        return redirect()->route('dormitory.dormitories.index')->with('status', 'Dormitory deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'type'    => ['required', 'in:boys,girls,mixed'],
            'address' => ['nullable', 'string', 'max:200'],
            'note'    => ['nullable', 'string', 'max:255'],
        ]);
    }
}
