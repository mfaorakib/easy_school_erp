@extends('staffportal::layouts.portal')

@section('title', __('ui.my_attendance'))

@section('content')
    <div class="page-head">
        <h1>{{ __('ui.my_attendance') }}</h1>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('staffportal.attendance.index') }}" style="display:flex;align-items:end;gap:1rem;flex-wrap:wrap">
            <div>
                <label style="display:block;font-size:.8rem;color:var(--muted);margin-bottom:.3rem">From</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}">
            </div>
            <div>
                <label style="display:block;font-size:.8rem;color:var(--muted);margin-bottom:.3rem">To</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}">
            </div>
            <div>
                <button type="submit" class="btn">Filter</button>
            </div>
        </form>
    </div>

    <div class="card">
        @if($summary->isEmpty())
            <div style="color:var(--muted);font-size:.9rem">{{ __('ui.no_records') }}</div>
        @else
            <div style="display:flex;gap:1.5rem;flex-wrap:wrap">
                @foreach($summary as $row)
                    <div>
                        <div style="font-size:1.4rem;font-weight:800">{{ $row['count'] }}</div>
                        <div style="color:var(--muted);font-size:.8rem">{{ $row['label'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        @if($records->isEmpty())
            <div class="empty">{{ __('ui.no_records') }}</div>
        @else
            <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('ui.date') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        <tr>
                            <td>{{ $record->date->format('Y-m-d') }}</td>
                            <td>{{ $record->status?->label() }}</td>
                            <td>{{ $record->note ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
