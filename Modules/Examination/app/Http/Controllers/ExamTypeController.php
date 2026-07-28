<?php

namespace Modules\Examination\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Examination\Models\ExamType;

class ExamTypeController extends Controller
{
    public function index()
    {
        return view('examination::types.index', ['types' => ExamType::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('examination::types.form', ['type' => new ExamType]);
    }

    public function store(Request $request)
    {
        ExamType::create($request->validate(['name' => ['required', 'string', 'max:100']]));

        return redirect()->route('exam.types.index')->with('status', 'Exam type created.');
    }

    public function edit(ExamType $type)
    {
        return view('examination::types.form', compact('type'));
    }

    public function update(Request $request, ExamType $type)
    {
        $type->update($request->validate(['name' => ['required', 'string', 'max:100']]));

        return redirect()->route('exam.types.index')->with('status', 'Exam type updated.');
    }

    public function destroy(ExamType $type)
    {
        $type->delete();

        return redirect()->route('exam.types.index')->with('status', 'Exam type deleted.');
    }
}
