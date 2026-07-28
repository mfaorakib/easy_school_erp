<?php

namespace Modules\DownloadCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DownloadCenter\Models\ContentType;

class ContentTypeController extends Controller
{
    public function index()
    {
        return view('downloadcenter::types.index', ['types' => ContentType::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('downloadcenter::types.form', ['type' => new ContentType]);
    }

    public function store(Request $request)
    {
        ContentType::create($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('downloadcenter.types.index')->with('status', 'Content type created.');
    }

    public function edit(ContentType $type)
    {
        return view('downloadcenter::types.form', compact('type'));
    }

    public function update(Request $request, ContentType $type)
    {
        $type->update($request->validate(['name' => ['required', 'string', 'max:150']]));

        return redirect()->route('downloadcenter.types.index')->with('status', 'Content type updated.');
    }

    public function destroy(ContentType $type)
    {
        $type->delete();

        return redirect()->route('downloadcenter.types.index')->with('status', 'Content type deleted.');
    }
}
