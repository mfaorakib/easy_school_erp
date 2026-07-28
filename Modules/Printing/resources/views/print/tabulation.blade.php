@extends('documents::print.layout', ['title' => 'Tabulation Sheet'])

@section('print-styles')
    @page{size:A4 landscape;margin:10mm}
    .tab{background:#fff;margin:24px auto;padding:24px 28px;box-shadow:0 14px 36px -18px rgba(2,6,23,.35);border:1px solid #e5e7eb;max-width:1100px}
    .tab .top{text-align:center;margin-bottom:14px}
    .tab .top h2{margin:0;font-size:1.35rem}
    .tab .top .ex{color:#4f46e5;font-weight:700}
    .tab-scroll{overflow-x:auto}
    .tab table{width:100%;border-collapse:collapse;font-size:.82rem}
    .tab th,.tab td{border:1px solid #cbd5e1;padding:.4rem .5rem;text-align:center;white-space:nowrap}
    .tab th{background:#eef2ff;color:#3730a3;font-size:.72rem}
    .tab td.name{text-align:start;font-weight:600}
    .tab td.tot{font-weight:700;background:#f8fafc}
    @media print{ .tab{box-shadow:none;max-width:none} }
@endsection

@section('documents')
@php $svc = app(\Modules\Printing\Services\PrintService::class); @endphp
<div class="tab">
    <div class="top">
        <h2>{{ $svc->schoolName() }}</h2>
        <div class="ex">{{ optional($data['exam'])->name }} — Tabulation Sheet · {{ $data['class'] }}/{{ $data['section'] }}</div>
    </div>
    <div class="tab-scroll">
        <table>
            <thead>
                <tr>
                    <th>Roll</th><th style="text-align:start">Student</th>
                    @foreach($data['subjects'] as $sc)<th>{{ \Illuminate\Support\Str::limit(optional($sc->subject)->name, 10) }}</th>@endforeach
                    <th>Total</th><th>GPA</th><th>Grade</th><th>Rank</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td>{{ $row['roll'] }}</td>
                    <td class="name">{{ optional($row['student'])->full_name }}</td>
                    @foreach($data['subjects'] as $sc)<td>{{ $row['cells'][$sc->subject_id] ?? '—' }}</td>@endforeach
                    <td class="tot">{{ $row['total'] !== null ? (int) $row['total'] : '—' }}</td>
                    <td>{{ $row['gpa'] !== null ? number_format($row['gpa'], 2) : '—' }}</td>
                    <td>{{ $row['grade'] }}</td>
                    <td>{{ $row['position'] ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="99" style="padding:24px;color:#64748b">No students / results found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
