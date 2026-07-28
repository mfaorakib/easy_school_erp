@extends('layouts.admin')
@section('title', __('ui.student_documents'))

@section('content')
<div class="page-head"><h1>{{ $student->full_name }} — {{ __('ui.student_documents') }}</h1>
    <a href="{{ route('academic.students.index') }}" class="btn btn-ghost">Back</a></div>

<div class="card">
    <h3 style="margin-top:0">{{ __('ui.upload_document') }}</h3>
    <form method="POST" action="{{ route('academic.students.documents.store', $student->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="grid">
            <div>
                <label>{{ __('ui.document_types') }}</label>
                <select name="document_type_id">
                    <option value="">— other / custom —</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ old('document_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Title</label>
                <input name="title" value="{{ old('title') }}" placeholder="Only needed if no type is picked above">
            </div>
            <div>
                <label>File</label>
                <input type="file" name="file" accept="image/*,.pdf" required>
            </div>
        </div>
        <button class="btn" style="margin-top:.9rem">{{ __('ui.upload_document') }}</button>
    </form>
</div>

<div class="card">
    @if($documents->isEmpty())
        <div class="empty">{{ __('ui.no_documents_yet') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.student_documents') }}</th><th>Uploaded</th><th></th></tr></thead>
        <tbody>
        @foreach($documents as $doc)
            <tr>
                <td><strong>{{ optional($doc->type)->name ?? $doc->title }}</strong></td>
                <td>{{ $doc->created_at->format('Y-m-d') }}</td>
                <td class="actions">
                    <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                    <form method="POST" action="{{ route('academic.students.documents.destroy', [$student->id, $doc->id]) }}"
                          onsubmit="return confirm('Delete this document?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
