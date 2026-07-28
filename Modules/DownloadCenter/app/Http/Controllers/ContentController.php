<?php

namespace Modules\DownloadCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\SchoolClass;
use Modules\AcademicCore\Models\Section;
use Modules\DownloadCenter\Models\ContentType;
use Modules\DownloadCenter\Models\DownloadContent;

class ContentController extends Controller
{
    public function index()
    {
        return view('downloadcenter::contents.index', [
            'contents' => DownloadContent::with(['type', 'schoolClass', 'section'])->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('downloadcenter::contents.form', $this->formData(new DownloadContent));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('downloads', 'public');
        }
        unset($data['file']);

        $data['uploaded_by'] = auth()->id();
        $data['is_published'] = $request->boolean('is_published');

        DownloadContent::create($data);

        return redirect()->route('downloadcenter.contents.index')->with('status', 'Content added.');
    }

    public function edit(DownloadContent $content)
    {
        return view('downloadcenter::contents.form', $this->formData($content));
    }

    public function update(Request $request, DownloadContent $content)
    {
        $data = $this->validated($request);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('downloads', 'public');
        }
        unset($data['file']);

        $data['is_published'] = $request->boolean('is_published');

        $content->update($data);

        return redirect()->route('downloadcenter.contents.index')->with('status', 'Content updated.');
    }

    public function destroy(DownloadContent $content)
    {
        $content->delete();

        return redirect()->route('downloadcenter.contents.index')->with('status', 'Content deleted.');
    }

    private function formData(DownloadContent $content): array
    {
        return [
            'content'  => $content,
            'types'    => ContentType::active()->orderBy('name')->get(),
            'classes'  => SchoolClass::active()->orderBy('name')->get(),
            'sections' => Section::active()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'content_type_id' => ['nullable', 'exists:content_types,id'],
            'title'           => ['required', 'string', 'max:200'],
            'description'     => ['nullable', 'string'],
            'class_id'        => ['nullable', 'exists:classes,id'],
            'section_id'      => ['nullable', 'exists:sections,id'],
            'audiences'       => ['array'],
            'audiences.*'     => ['in:all,admin,teacher,student,parent'],
            'external_url'    => ['nullable', 'url'],
            'file'            => ['nullable', 'file', 'max:5120'],
            'publish_date'    => ['nullable', 'date'],
            'is_published'    => ['nullable', 'boolean'],
        ]);
    }
}
