<?php

namespace Modules\Fees\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fees\Models\FeeGroup;

class FeeGroupController extends Controller
{
    public function index()
    {
        return view('fees::groups.index', ['groups' => FeeGroup::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('fees::groups.form', ['group' => new FeeGroup]);
    }

    public function store(Request $request)
    {
        FeeGroup::create($this->validated($request));

        return redirect()->route('fees.groups.index')->with('status', 'Fee group created.');
    }

    public function edit(FeeGroup $group)
    {
        return view('fees::groups.form', compact('group'));
    }

    public function update(Request $request, FeeGroup $group)
    {
        $group->update($this->validated($request));

        return redirect()->route('fees.groups.index')->with('status', 'Fee group updated.');
    }

    public function destroy(FeeGroup $group)
    {
        $group->delete();

        return redirect()->route('fees.groups.index')->with('status', 'Fee group deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
