<?php

namespace Modules\AcademicCore\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AcademicCore\Models\DocumentType;
use Modules\AcademicCore\Models\Student;
use Modules\AcademicCore\Models\StudentDocument;

class StudentDocumentController extends Controller
{
    public function index(Student $student)
    {
        $documents = $student->documents()->with('type')->latest()->get();
        $types = DocumentType::active()->get();

        return view('academiccore::students.documents', compact('student', 'documents', 'types'));
    }

    public function store(Request $request, Student $student)
    {
        $data = $request->validate([
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'title'             => ['required_without:document_type_id', 'nullable', 'string', 'max:150'],
            'file'              => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('file')->store('students/documents', 'public');

        $title = $data['title'] ?? null;

        if (! empty($data['document_type_id'])) {
            $type = DocumentType::find($data['document_type_id']);
            $title = $title ?: $type->name;
        }

        StudentDocument::create([
            'student_id'        => $student->id,
            'document_type_id'  => $data['document_type_id'] ?? null,
            'title'             => $title,
            'file_path'         => $path,
        ]);

        return redirect()->route('academic.students.documents.index', $student->id)
            ->with('status', 'Document uploaded.');
    }

    public function destroy(Student $student, StudentDocument $document)
    {
        $document->delete();

        return redirect()->route('academic.students.documents.index', $student->id)
            ->with('status', 'Document removed.');
    }
}
