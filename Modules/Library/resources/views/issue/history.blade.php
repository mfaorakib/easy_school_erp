@extends('layouts.admin')
@section('title', __('ui.book_issue_history'))

@section('content')
<div class="page-head"><h1>{{ __('ui.book_issue_history') }}</h1></div>

<div class="card">
    <form method="GET" action="{{ route('library.issue.history') }}">
        <div class="grid">
            <div><label>Member type</label>
                <select name="member_type" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="student" {{ $memberType == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="staff" {{ $memberType == 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Staff search</label>
                <input type="text" name="staff_search" value="{{ $staffSearch }}" placeholder="Name...">
            </div>
            <div><label>{{ __('ui.books') }}</label>
                <select name="book_id" onchange="this.form.submit()">
                    <option value="">—</option>
                    @foreach($books as $b)
                        <option value="{{ $b->id }}" {{ $bookId == $b->id ? 'selected' : '' }}>{{ $b->title }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="" {{ $status == '' || is_null($status) ? 'selected' : '' }}>All</option>
                    <option value="open" {{ $status == 'open' ? 'selected' : '' }}>Not returned</option>
                    <option value="returned" {{ $status == 'returned' ? 'selected' : '' }}>Returned</option>
                </select>
            </div>
            <div><label>{{ __('ui.from_date') }}</label>
                <input type="date" name="from" value="{{ $from }}">
            </div>
            <div><label>{{ __('ui.to_date') }}</label>
                <input type="date" name="to" value="{{ $to }}">
            </div>
            <div style="align-self:end;display:flex;gap:.5rem">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate') }}</button>
                <a class="btn btn-sm" href="{{ route('library.issue.history') }}">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="page-head"><h1 style="margin-top:0">{{ __('ui.book_issue_history') }}</h1></div>
    @if($issues->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.books') }}</th>
                <th>Borrower</th>
                <th>Issued</th>
                <th>Due</th>
                <th>Returned</th>
                <th>Fine</th>
                <th>Issued By</th>
            </tr>
        </thead>
        <tbody>
        @foreach($issues as $issue)
            <tr>
                <td><strong>{{ optional($issue->book)->title }}</strong></td>
                <td>{{ optional($issue->member)->displayLabel() }}</td>
                <td>{{ $issue->issue_date->format('d M Y') }}</td>
                <td>{{ $issue->due_date->format('d M Y') }}</td>
                <td>
                    @if($issue->is_returned)
                        {{ $issue->return_date?->format('d M Y') ?? '—' }}
                    @else
                        <span class="badge">Not returned</span>
                    @endif
                </td>
                <td>
                    @if($issue->fine > 0)
                        <span class="badge" style="color:var(--danger)">{{ number_format($issue->fine, 2) }}</span>
                        @if($issue->is_returned)
                            @if($issue->fine_collected)
                                <span class="badge">Collected</span>
                                <a href="{{ route('library.issue.fine-receipt', $issue) }}">Receipt</a>
                            @else
                                <form method="POST" action="{{ route('library.issue.collect-fine', $issue) }}" style="display:inline">
                                    @csrf
                                    <button class="btn btn-sm" type="submit">Collect Fine</button>
                                </form>
                            @endif
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td>{{ optional($issue->issuedBy)->name ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>

<p><a href="{{ route('library.issue.index') }}">← Back to Issue/Return</a></p>
@endsection
