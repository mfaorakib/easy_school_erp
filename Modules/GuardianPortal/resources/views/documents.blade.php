@extends('guardianportal::layouts.portal')

@section('title', __('ui.student_documents'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.student_documents') }}</h1>
        <a href="{{ route('portal.children.show', $student->id) }}" class="btn btn-ghost">&larr; {{ $student->full_name }}</a>
    </div>

    <div class="card">
        @if($documents->isEmpty())
            <div class="empty">{{ __('ui.no_documents_yet') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.name') }}</th>
                        <th>{{ __('ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                        <tr>
                            <td>{{ optional($doc->type)->name ?? $doc->title }}</td>
                            <td>
                                <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-ghost">{{ __('ui.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
