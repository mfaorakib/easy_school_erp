@extends('documents::print.layout', ['title' => 'Marksheet'])

@section('print-styles')
    .msheet{background:#fff;max-width:800px;margin:24px auto;padding:34px 40px;box-shadow:0 14px 36px -18px rgba(2,6,23,.35);
        border:1px solid #e5e7eb;page-break-after:always;position:relative}
    .msheet:last-child{page-break-after:auto}
    .msheet .top{text-align:center;border-bottom:3px double #4f46e5;padding-bottom:12px;margin-bottom:16px}
    .msheet .top h2{margin:0;font-size:1.5rem;color:#1e293b;letter-spacing:.02em}
    .msheet .top .ex{color:#4f46e5;font-weight:700;margin-top:2px}
    .msheet .info{display:flex;flex-wrap:wrap;gap:.4rem 2rem;font-size:.9rem;margin-bottom:14px}
    .msheet .info div b{color:#475569}
    .msheet table{width:100%;border-collapse:collapse;font-size:.9rem}
    .msheet th,.msheet td{border:1px solid #cbd5e1;padding:.5rem .6rem;text-align:center}
    .msheet th{background:#eef2ff;color:#3730a3;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em}
    .msheet td.subj{text-align:start;font-weight:600}
    .msheet .fail{color:#dc2626;font-weight:700}
    .msheet .summary{display:flex;flex-wrap:wrap;gap:1rem;margin-top:16px}
    .msheet .summary .b{flex:1;min-width:110px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:.7rem;text-align:center}
    .msheet .summary .b b{display:block;font-size:1.25rem;color:#1e293b}
    .msheet .summary .b small{color:#64748b;font-size:.72rem}
    .msheet .signs{display:flex;justify-content:space-between;margin-top:44px;padding:0 10px}
    .msheet .signs .s{border-top:1.5px solid #475569;padding-top:5px;font-size:.82rem;color:#475569;min-width:150px;text-align:center}
    .result-badge{display:inline-block;padding:.2rem .8rem;border-radius:999px;font-weight:700;font-size:.85rem}
    .pass{background:#dcfce7;color:#15803d}.failb{background:#fee2e2;color:#b91c1c}
@endsection

@section('documents')
@php $svc = app(\Modules\Printing\Services\PrintService::class); @endphp
@forelse($sheets as $data)
    @php $r = $data['result']; $rec = $data['record']; @endphp
    <div class="msheet">
        <div class="top">
            <h2>{{ $svc->schoolName() }}</h2>
            <div class="ex">{{ optional($data['exam'])->name }} — Marksheet</div>
        </div>
        <div class="info">
            <div><b>Name:</b> {{ optional($data['student'])->full_name }}</div>
            <div><b>Class:</b> {{ optional(optional($rec)->schoolClass)->name }} / {{ optional(optional($rec)->section)->name }}</div>
            <div><b>Roll:</b> {{ optional($rec)->roll_no ?: '—' }}</div>
            <div><b>Admission No:</b> {{ optional($data['student'])->admission_no }}</div>
        </div>
        <table>
            <thead><tr><th style="text-align:start">Subject</th><th>Full Mark</th><th>Pass Mark</th><th>Obtained</th><th>Grade</th><th>Result</th></tr></thead>
            <tbody>
            @foreach($data['rows'] as $row)
                <tr>
                    <td class="subj">{{ $row['subject'] }}</td>
                    <td>{{ (int) $row['full'] }}</td>
                    <td>{{ (int) $row['pass'] }}</td>
                    <td>{{ $row['obtained'] === null ? 'Abs' : (int) $row['obtained'] }}</td>
                    <td>{{ $row['grade'] }}</td>
                    <td class="{{ $row['is_pass'] ? '' : 'fail' }}">{{ $row['is_pass'] ? 'Pass' : 'Fail' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="summary">
            <div class="b"><b>{{ $r ? (int) $r->total_marks : '—' }}</b><small>Total</small></div>
            <div class="b"><b>{{ $r ? number_format((float) $r->gpa, 2) : '—' }}</b><small>GPA</small></div>
            <div class="b"><b>{{ $r->grade ?? '—' }}</b><small>Grade</small></div>
            <div class="b"><b>{{ $r && $r->position ? $r->position : '—' }}</b><small>Position</small></div>
            <div class="b">
                @if($r)<span class="result-badge {{ $r->is_pass ? 'pass' : 'failb' }}">{{ $r->is_pass ? 'PASSED' : 'FAILED' }}</span>@else — @endif
                <small style="display:block;margin-top:.3rem">Result</small>
            </div>
        </div>
        <div class="signs">
            <div class="s">Class Teacher</div>
            <div class="s">Examiner</div>
            <div class="s">Principal</div>
        </div>
    </div>
@empty
    <p style="text-align:center;color:#64748b;padding:40px">No students selected.</p>
@endforelse
@endsection
