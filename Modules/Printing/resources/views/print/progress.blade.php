@extends('documents::print.layout', ['title' => 'Progress Card'])

@section('print-styles')
    .pcard{background:#fff;max-width:640px;margin:24px auto;padding:32px 38px;box-shadow:0 14px 36px -18px rgba(2,6,23,.35);
        border:1px solid #e5e7eb;border-top:6px solid #4f46e5;page-break-after:always}
    .pcard:last-child{page-break-after:auto}
    .pcard .top{text-align:center;margin-bottom:14px}
    .pcard .top h2{margin:0;font-size:1.4rem}
    .pcard .top .sub{color:#64748b;font-size:.85rem}
    .pcard .info{display:flex;flex-wrap:wrap;gap:.3rem 2rem;font-size:.9rem;margin:12px 0}
    .pcard .info b{color:#475569}
    .pcard table{width:100%;border-collapse:collapse;font-size:.9rem}
    .pcard th,.pcard td{border:1px solid #cbd5e1;padding:.5rem;text-align:center}
    .pcard th{background:#eef2ff;color:#3730a3;font-size:.76rem}
    .pcard td.ex{text-align:start;font-weight:600}
    .pcard .overall{margin-top:16px;text-align:center;background:linear-gradient(135deg,#4f46e5,#0ea5e9);color:#fff;border-radius:12px;padding:14px}
    .pcard .overall b{font-size:1.8rem;display:block}
    .pcard .signs{display:flex;justify-content:space-between;margin-top:40px}
    .pcard .signs .s{border-top:1.5px solid #475569;padding-top:5px;font-size:.8rem;color:#475569;min-width:140px;text-align:center}
@endsection

@section('documents')
@php $svc = app(\Modules\Printing\Services\PrintService::class); @endphp
@forelse($cards as $data)
    @php $rec = $data['record']; @endphp
    <div class="pcard">
        <div class="top">
            <h2>{{ $svc->schoolName() }}</h2>
            <div class="sub">Academic Progress Card</div>
        </div>
        <div class="info">
            <div><b>Name:</b> {{ optional($data['student'])->full_name }}</div>
            <div><b>Class:</b> {{ optional(optional($rec)->schoolClass)->name }} / {{ optional(optional($rec)->section)->name }}</div>
            <div><b>Roll:</b> {{ optional($rec)->roll_no ?: '—' }}</div>
        </div>
        <table>
            <thead><tr><th style="text-align:start">Exam / Term</th><th>Total</th><th>Full</th><th>GPA</th><th>Grade</th><th>Result</th></tr></thead>
            <tbody>
            @forelse($data['terms'] as $t)
                <tr>
                    <td class="ex">{{ $t['exam'] }}</td>
                    <td>{{ (int) $t['total'] }}</td>
                    <td>{{ (int) $t['full'] }}</td>
                    <td>{{ number_format($t['gpa'], 2) }}</td>
                    <td>{{ $t['grade'] ?: '—' }}</td>
                    <td>{{ $t['pass'] ? 'Pass' : 'Fail' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:20px;color:#64748b">No results recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="overall"><b>{{ number_format($data['overall_gpa'], 2) }}</b><span>Overall GPA</span></div>
        <div class="signs"><div class="s">Class Teacher</div><div class="s">Principal</div></div>
    </div>
@empty
    <p style="text-align:center;color:#64748b;padding:40px">No students selected.</p>
@endforelse
@endsection
