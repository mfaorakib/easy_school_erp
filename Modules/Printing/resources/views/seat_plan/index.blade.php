@extends('layouts.admin')
@section('title', __('ui.seat_plan'))

@section('content')
<div class="page-head"><h1>{{ __('ui.seat_plan') }}</h1></div>

<div class="card">
    <div class="overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>{{ __('ui.exam') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($exams as $exam)
            <tr>
                <td>{{ $exam->name }}</td>
                <td>
                    <a class="btn btn-sm" href="{{ route('printing.seat_plan.all', $exam) }}" target="_blank">{{ __('ui.print_all_rooms') }}</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" style="padding:20px;color:#64748b;text-align:center">No exams found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
