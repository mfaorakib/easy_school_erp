@extends('layouts.admin')
@section('title', __('ui.issue_return'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.issue_return') }}</h1>
    <a href="{{ route('library.issue.history') }}" class="btn">View Full History &rarr;</a>
</div>

{{-- Filter --}}
<div class="card">
    <h3 style="margin-top:0">Filter members</h3>
    <form method="GET" action="{{ route('library.issue.index') }}">
        <div class="grid">
            <div><label>Member type</label>
                <select name="member_type" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="student" {{ $memberType == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="staff" {{ $memberType == 'staff' ? 'selected' : '' }}>Staff/Teacher</option>
                </select>
            </div>
            <div><label>Class</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Section (blank = all sections)</label>
                <select name="section_id" onchange="this.form.submit()">
                    <option value="">All sections</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $sectionId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Staff search</label>
                <input type="text" name="staff_search" value="{{ $staffSearch }}" placeholder="Search staff by name/designation/department…">
            </div>
            <div style="display:flex;align-items:flex-end;gap:.5rem">
                <button class="btn btn-sm">Filter</button>
                <a href="{{ route('library.issue.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">{{ __('ui.issue') }} a book</h3>
    <form method="POST" action="{{ route('library.issue.store') }}">
        @csrf
        <div class="grid">
            <div><label>{{ __('ui.books') }}</label>
                <select name="book_id" required>
                    <option value="">—</option>
                    @foreach($books as $b)<option value="{{ $b->id }}">{{ $b->title }} ({{ $b->available }} {{ __('ui.available') }})</option>@endforeach
                </select></div>
            <div><label>Member</label>
                <select name="library_member_id" required>
                    <option value="">—</option>
                    @foreach($members as $m)<option value="{{ $m->id }}">{{ $m->displayLabel() }}</option>@endforeach
                </select></div>
            <div><label>Due date</label><input type="date" name="due_date" value="{{ $dueHint }}"></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.issue') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Issued (not returned)</h3>
    @if($open->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.books') }}</th><th>Member</th><th>Issued</th><th>Due</th><th>Fine (today)</th><th></th></tr></thead>
        <tbody>
        @foreach($open as $row)
            @php($i = $row['issue'])
            <tr>
                <td><strong>{{ optional($i->book)->title }}</strong></td>
                <td>{{ optional(optional($i->member)->user)->name }}</td>
                <td>{{ $i->issue_date->format('d M Y') }}</td>
                <td>{{ $i->due_date->format('d M Y') }}</td>
                <td>{!! $row['fine'] > 0 ? '<span class="badge" style="color:var(--danger)">'.number_format($row['fine'], 2).'</span>' : '—' !!}</td>
                <td>
                    <form method="POST" action="{{ route('library.issue.return', $i) }}">
                        @csrf<button class="btn btn-sm">{{ __('ui.return') }}</button>
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
