<?php

namespace Modules\OnlineExam\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OnlineExam\Models\QuestionGroup;

class QuestionGroupController extends Controller
{
    public function index()
    {
        $groups = QuestionGroup::withCount('questions')->latest()->get();

        return view('onlineexam::groups.index', compact('groups'));
    }

    public function create()
    {
        return view('onlineexam::groups.form', ['group' => new QuestionGroup]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        QuestionGroup::create($data);

        return redirect()->route('onlineexam.groups.index')->with('status', 'Question group created.');
    }

    public function edit(QuestionGroup $group)
    {
        return view('onlineexam::groups.form', ['group' => $group]);
    }

    public function update(Request $request, QuestionGroup $group)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        $group->update($data);

        return redirect()->route('onlineexam.groups.index')->with('status', 'Question group updated.');
    }

    public function destroy(QuestionGroup $group)
    {
        $group->delete();

        return redirect()->route('onlineexam.groups.index')->with('status', 'Question group deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'     => ['required', 'string', 'max:200'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
