@extends('layouts.admin')
@section('title', __('ui.seat_plan'))

@section('content')
<div class="page-head"><h1>{{ __('ui.seat_plan') }}</h1></div>

<div class="card">
    <form method="POST" action="{{ route('exam.seat_plan.generate') }}">
        @csrf
        <div class="grid">
            <label>{{ __('ui.exam') }}
                <select name="exam_id" required>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ __('ui.seats_per_bench') }}
                <select name="seats_per_bench">
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </label>
            <label>
                <input type="checkbox" name="mix_classes" value="1" checked>
                {{ __('ui.mix_classes') }}
            </label>
        </div>

        <p>{{ __('ui.select_rooms') }}</p>
        <div class="grid">
            @foreach($classrooms as $c)
                <label><input type="checkbox" name="classroom_ids[]" value="{{ $c->id }}" checked> {{ $c->room_no }} (cap {{ $c->capacity }})</label>
            @endforeach
        </div>

        <button type="submit" class="btn">{{ __('ui.generate') }}</button>
    </form>
</div>

<div class="card">
    @if($exams->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
        <div class="overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>{{ __('ui.name') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($exams as $exam)
                    <tr>
                        <td><strong>{{ $exam->name }}</strong></td>
                        <td>
                            @if($plans->has($exam->id))
                                <span class="badge">Generated</span>
                            @else
                                <span class="badge badge-muted">Not generated</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a href="{{ route('exam.seat_plan.show', $exam) }}" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endsection
