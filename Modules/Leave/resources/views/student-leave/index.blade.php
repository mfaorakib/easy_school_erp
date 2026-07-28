@extends('layouts.admin')
@section('title', __('ui.student_leave'))

@section('content')
<div class="page-head"><h1>{{ __('ui.student_leave') }}</h1></div>

<div class="card">
    @if($pending->isEmpty())
        <div class="empty">There are no pending student leave requests.</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>{{ __('ui.leave_types') }}</th>
                <th>{{ __('ui.from_to') }}</th>
                <th>{{ __('ui.days') }}</th>
                <th>{{ __('ui.reason') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($pending as $app)
            <tr>
                <td><strong>{{ optional($app->student)->full_name }}</strong></td>
                <td>{{ optional($app->type)->name }}</td>
                <td>{{ $app->from_date?->format('d M Y') }} — {{ $app->to_date?->format('d M Y') }}</td>
                <td>{{ $app->days }}</td>
                <td>{{ \Illuminate\Support\Str::limit($app->reason, 40) }}</td>
                <td class="actions">
                    <form method="POST" action="{{ route('leave.student.review', $app->id) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <button class="btn btn-sm" type="submit">{{ __('ui.approve') }}</button>
                    </form>
                    <form method="POST" action="{{ route('leave.student.review', $app->id) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <input type="text" name="review_note" maxlength="255" placeholder="note">
                        <button class="btn btn-sm btn-danger" type="submit">{{ __('ui.reject') }}</button>
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
