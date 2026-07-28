<?php

namespace Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Documents\Models\IdCardTemplate;

class IdCardTemplateController extends Controller
{
    public function index()
    {
        $templates = IdCardTemplate::orderBy('name')->get();

        return view('documents::id-card-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('documents::id-card-templates.form', ['template' => new IdCardTemplate]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request);

        IdCardTemplate::create($data);

        return redirect()->route('documents.idCardTemplates.index')->with('status', 'Template saved.');
    }

    public function edit(IdCardTemplate $idCardTemplate)
    {
        return view('documents::id-card-templates.form', ['template' => $idCardTemplate]);
    }

    public function update(Request $request, IdCardTemplate $idCardTemplate)
    {
        $data = $this->data($request, $idCardTemplate);

        $idCardTemplate->update($data);

        return redirect()->route('documents.idCardTemplates.index')->with('status', 'Template saved.');
    }

    public function destroy(IdCardTemplate $idCardTemplate)
    {
        $idCardTemplate->delete();

        return redirect()->route('documents.idCardTemplates.index')->with('status', 'Template deleted.');
    }

    private function data(Request $request, ?IdCardTemplate $template = null): array
    {
        $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'holder_type'      => ['required', 'in:student,staff'],
            'title'            => ['nullable', 'string', 'max:255'],
            'background_color' => ['required', 'string', 'max:20'],
            'text_color'       => ['required', 'string', 'max:20'],
            'orientation'      => ['required', 'in:portrait,landscape'],
            'footer_text'      => ['nullable', 'string', 'max:255'],
            'signature_label'  => ['nullable', 'string', 'max:100'],
            'fields'           => ['array'],
            'fields.*'         => ['string'],
            'logo'             => ['nullable', 'image', 'max:2048'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'signature'        => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only(
            'name',
            'holder_type',
            'title',
            'background_color',
            'text_color',
            'orientation',
            'footer_text',
            'signature_label'
        );

        $data['fields'] = $request->input('fields', []);
        $data['is_active'] = $request->boolean('is_active');

        $uploads = [
            'logo'             => 'logo_path',
            'background_image' => 'background_image_path',
            'signature'        => 'signature_path',
        ];

        foreach ($uploads as $field => $pathCol) {
            if ($request->hasFile($field)) {
                $data[$pathCol] = $request->file($field)->store('documents', 'public');
            }
        }

        return $data;
    }
}
