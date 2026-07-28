<?php

namespace Modules\Communication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Communication\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        return view('communication::notices.index', ['notices' => Notice::latest('notice_date')->get()]);
    }

    public function create()
    {
        return view('communication::notices.form', ['notice' => new Notice]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();
        $data['is_published'] = $request->boolean('is_published');
        Notice::create($data);

        return redirect()->route('communication.notices.index')->with('status', 'Notice added.');
    }

    public function edit(Notice $notice)
    {
        return view('communication::notices.form', ['notice' => $notice]);
    }

    public function update(Request $request, Notice $notice)
    {
        $data = $this->validated($request);
        $data['is_published'] = $request->boolean('is_published');
        $notice->update($data);

        return redirect()->route('communication.notices.index')->with('status', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return redirect()->route('communication.notices.index')->with('status', 'Notice deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'description'  => ['nullable', 'string'],
            'notice_date'  => ['required', 'date'],
            'publish_date' => ['nullable', 'date'],
            'audiences'    => ['required', 'array', 'min:1'],
            'audiences.*'  => ['in:all,admin,teacher,student,parent'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }
}
