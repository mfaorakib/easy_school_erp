@extends('layouts.admin')
@section('title', __('ui.seat_plan'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.seat_plan') }} — {{ $exam->name }}</h1>
    <a href="{{ route('exam.seat_plan.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

@if(!$plan)
    <div class="card">
        <div class="empty">{{ __('ui.no_seat_plan') }}</div>
        <a href="{{ route('exam.seat_plan.index') }}" class="btn">{{ __('ui.generate') }}</a>
    </div>
@else
    <div class="card">
        <span class="badge">{{ __('ui.seats_per_bench') }}: {{ $plan->seats_per_bench }}</span>
        <span class="badge">{{ __('ui.mix_classes') }}: {{ $plan->mix_classes ? 'Yes' : 'No' }}</span>
        <span class="badge">{{ $plan->assignments()->count() }}</span>

        <div class="actions">
            <a href="{{ route('printing.seat_plan.all', $exam) }}" class="btn" target="_blank"><i class="fa-solid fa-print" aria-hidden="true"></i> {{ __('ui.print_all_rooms') }}</a>
            <form method="POST" action="{{ route('exam.seat_plan.destroy', $exam) }}" onsubmit="return confirm('{{ __('ui.delete') }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
            </form>
        </div>
    </div>

    @foreach($rooms as $room)
        <div class="card">
            <div class="page-head">
                <h2>{{ __('ui.classroom') }}: {{ $room['classroom']->room_no }}</h2>
                <a href="{{ route('printing.seat_plan.room', [$exam, $room['classroom']]) }}" class="btn btn-sm btn-ghost" target="_blank"><i class="fa-solid fa-print" aria-hidden="true"></i> Print</a>
            </div>

            @if($room['assignments']->isEmpty())
                <div class="empty">{{ __('ui.no_records') }}</div>
            @else
                <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('ui.bench') }}</th>
                            <th>{{ __('ui.seat') }}</th>
                            <th>Roll</th>
                            <th>Name</th>
                            <th>Class/Section</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($room['assignments'] as $a)
                            <tr>
                                <td>{{ $a->bench_no }}</td>
                                <td>{{ $a->seat_no }}</td>
                                <td>{{ $a->roll_no }}</td>
                                <td>{{ $a->student->full_name ?? '—' }}</td>
                                <td>{{ optional($a->schoolClass)->name }} - {{ optional($a->section)->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </div>
    @endforeach
@endif
@endsection
