<?php

namespace Modules\Behaviour\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Behaviour\Models\BehaviourType;

class BehaviourTypeController extends Controller
{
    public function index()
    {
        return view('behaviour::types.index', ['types' => BehaviourType::orderBy('title')->get()]);
    }

    public function create()
    {
        return view('behaviour::types.form', ['type' => new BehaviourType]);
    }

    public function store(Request $request)
    {
        BehaviourType::create($this->validated($request));

        return redirect()->route('behaviour.types.index')->with('status', 'Behaviour type created.');
    }

    public function edit(BehaviourType $type)
    {
        return view('behaviour::types.form', compact('type'));
    }

    public function update(Request $request, BehaviourType $type)
    {
        $type->update($this->validated($request));

        return redirect()->route('behaviour.types.index')->with('status', 'Behaviour type updated.');
    }

    public function destroy(BehaviourType $type)
    {
        $type->delete();

        return redirect()->route('behaviour.types.index')->with('status', 'Behaviour type deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'point' => ['required', 'numeric'],
        ]);
    }
}
