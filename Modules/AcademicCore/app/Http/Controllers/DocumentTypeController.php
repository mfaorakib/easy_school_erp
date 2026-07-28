<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\DocumentType;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::orderBy('sort_order')->orderBy('name')->get();

        return view('academiccore::document-types.index', compact('documentTypes'));
    }

    public function create()
    {
        return view('academiccore::document-types.create', ['documentType' => new DocumentType]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_required'] = $request->boolean('is_required');
        $data['is_active'] = $request->boolean('is_active');

        DocumentType::create($data);

        return redirect()->route('academic.document-types.index')->with('status', 'Document type saved.');
    }

    public function edit(DocumentType $documentType)
    {
        return view('academiccore::document-types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $data = $this->validated($request);
        $data['is_required'] = $request->boolean('is_required');
        $data['is_active'] = $request->boolean('is_active');

        $documentType->update($data);

        return redirect()->route('academic.document-types.index')->with('status', 'Document type saved.');
    }

    public function destroy(DocumentType $documentType)
    {
        $documentType->delete();

        return redirect()->route('academic.document-types.index')->with('status', 'Document type deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
