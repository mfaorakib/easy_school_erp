@extends('layouts.admin')
@section('title', 'Download Center')

@section('content')
<div class="page-head"><h1>Download Center</h1></div>

@if($contents->isEmpty())
    <div class="empty">{{ __('ui.no_records') }}</div>
@else
    <div class="card">
        <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Class/Section</th>
                    <th>Published</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
            @foreach($contents as $content)
                <tr>
                    <td>
                        <strong>{{ $content->title }}</strong>
                        @if($content->description)
                            <div class="l" style="color:var(--muted);font-size:.85rem;margin:.15rem 0 0">
                                {{ $content->description }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($content->type)
                            <span class="badge">{{ $content->type->name }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($content->schoolClass)
                            {{ $content->schoolClass->name }}@if($content->section) / {{ $content->section->name }}@endif
                        @else
                            All
                        @endif
                    </td>
                    <td>{{ optional($content->publish_date)->format('d M Y') ?? '—' }}</td>
                    <td>
                        @if($content->link())
                            <a class="btn btn-sm" href="{{ $content->link() }}" target="_blank">Download</a>
                        @else
                            <span style="color:var(--muted)">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>
@endif
@endsection
