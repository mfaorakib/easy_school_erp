@extends('layouts.admin')
@section('title', 'Behaviour Report')

@section('content')
<div class="page-head"><h1>Behaviour Report</h1></div>

<div class="card">
    @if(empty($totals))
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr>
            <th>#</th><th>Student</th><th>Records</th><th>Total Points</th>
        </tr></thead>
        <tbody>
        @foreach($totals as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    {{ optional($row['student'])->full_name }}
                    <small style="color:var(--muted)">{{ optional($row['student'])->admission_no }}</small>
                </td>
                <td><span class="badge">{{ $row['count'] }}</span></td>
                <td><span class="badge" style="color:var(--{{ $row['total'] >= 0 ? 'success' : 'danger' }})">{{ $row['total'] }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
