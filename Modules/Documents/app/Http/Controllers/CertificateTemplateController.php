<?php

namespace Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Documents\Models\CertificateTemplate;
use Modules\Documents\Services\DocumentService;

class CertificateTemplateController extends Controller
{
    public function index()
    {
        $templates = CertificateTemplate::orderBy('name')->get();

        return view('documents::certificate-templates.index', compact('templates'));
    }

    public function create(DocumentService $documents)
    {
        return view('documents::certificate-templates.form', [
            'template'     => new CertificateTemplate,
            'placeholders' => $documents->placeholders(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request);

        CertificateTemplate::create($data);

        return redirect()->route('documents.certificateTemplates.index')->with('status', 'Template saved.');
    }

    public function edit(CertificateTemplate $certificateTemplate, DocumentService $documents)
    {
        return view('documents::certificate-templates.form', [
            'template'     => $certificateTemplate,
            'placeholders' => $documents->placeholders(),
        ]);
    }

    public function update(Request $request, CertificateTemplate $certificateTemplate)
    {
        $data = $this->data($request, $certificateTemplate);

        $certificateTemplate->update($data);

        return redirect()->route('documents.certificateTemplates.index')->with('status', 'Template saved.');
    }

    public function destroy(CertificateTemplate $certificateTemplate)
    {
        $certificateTemplate->delete();

        return redirect()->route('documents.certificateTemplates.index')->with('status', 'Template saved.');
    }

    /**
     * Render the certificate with the form's CURRENT (possibly unsaved) values
     * against sample data — never touches DocumentIssuance/document numbering.
     * Reached via the form's "Preview" button (a formaction/formtarget="_blank"
     * on the same <form>, so every field — including a just-picked but
     * not-yet-saved image file — comes along in this one request).
     */
    public function preview(Request $request, DocumentService $documents)
    {
        $request->validate([
            'holder_type'           => ['required', 'in:student,staff,general'],
            'document_prefix'       => ['nullable', 'string', 'max:20'],
            'heading'               => ['nullable', 'string', 'max:255'],
            'body'                  => ['nullable', 'string'],
            'orientation'           => ['required', 'in:portrait,landscape'],
            'accent_color'          => ['nullable', 'string', 'max:7'],
            'font_family'           => ['nullable', 'in:serif,sans,elegant'],
            'border_style'          => ['nullable', 'in:classic,simple,none'],
            'signature_left_label'  => ['nullable', 'string', 'max:100'],
            'signature_right_label' => ['nullable', 'string', 'max:100'],
            'header_image'          => ['nullable', 'image', 'max:4096'],
            'background_image'      => ['nullable', 'image', 'max:4096'],
            'signature_left'        => ['nullable', 'image', 'max:2048'],
            'signature_right'       => ['nullable', 'image', 'max:2048'],
        ]);

        $template = new CertificateTemplate($request->only(
            'holder_type', 'document_prefix', 'heading', 'body', 'orientation',
            'accent_color', 'font_family', 'border_style',
            'signature_left_label', 'signature_right_label',
        ));
        $template->name = 'Preview';

        $existing = $request->integer('template_id')
            ? CertificateTemplate::find($request->integer('template_id'))
            : null;

        $imageColumns = [
            'header_image'     => 'header_image_path',
            'background_image' => 'background_image_path',
            'signature_left'   => 'signature_left_path',
            'signature_right'  => 'signature_right_path',
        ];

        foreach ($imageColumns as $field => $column) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $template->{$column} = 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath()));
            } elseif ($existing) {
                $template->{$column} = $existing->{$column};
            }
        }

        return view('documents::print.certificate', [
            'template'  => $template,
            'documents' => [[
                'name' => 'Preview',
                'body' => $documents->previewBody($template),
            ]],
        ]);
    }

    private function data(Request $request, ?CertificateTemplate $template = null): array
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'holder_type'           => ['required', 'in:student,staff,general'],
            'document_prefix'       => ['nullable', 'string', 'max:20'],
            'heading'               => ['nullable', 'string', 'max:255'],
            'body'                  => ['nullable', 'string'],
            'orientation'           => ['required', 'in:portrait,landscape'],
            'accent_color'          => ['nullable', 'string', 'max:7'],
            'font_family'           => ['nullable', 'in:serif,sans,elegant'],
            'border_style'          => ['nullable', 'in:classic,simple,none'],
            'signature_left_label'  => ['nullable', 'string', 'max:100'],
            'signature_right_label' => ['nullable', 'string', 'max:100'],
            'header_image'          => ['nullable', 'image', 'max:4096'],
            'background_image'      => ['nullable', 'image', 'max:4096'],
            'signature_left'        => ['nullable', 'image', 'max:2048'],
            'signature_right'       => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only(
            'name',
            'holder_type',
            'document_prefix',
            'heading',
            'body',
            'orientation',
            'accent_color',
            'font_family',
            'border_style',
            'signature_left_label',
            'signature_right_label'
        );

        $data['is_active'] = $request->boolean('is_active');

        $uploads = [
            'header_image'     => 'header_image_path',
            'background_image' => 'background_image_path',
            'signature_left'   => 'signature_left_path',
            'signature_right'  => 'signature_right_path',
        ];

        foreach ($uploads as $field => $column) {
            if ($request->hasFile($field)) {
                $data[$column] = $request->file($field)->store('documents', 'public');
            }
        }

        return $data;
    }
}
