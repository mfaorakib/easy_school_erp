@extends('documents::print.layout', ['title' => 'Fee Statement'])

@section('print-styles')
    .inv{background:#fff;max-width:760px;margin:24px auto;padding:32px 40px;box-shadow:0 14px 36px -18px rgba(2,6,23,.35);
        border:1px solid #e5e7eb;page-break-after:always}
    .inv:last-child{page-break-after:auto}
    .inv .top{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #4f46e5;padding-bottom:12px;margin-bottom:16px}
    .inv .top h2{margin:0;font-size:1.35rem}
    .inv .top .r{text-align:end;font-size:.85rem;color:#64748b}
    .inv .info{font-size:.9rem;margin-bottom:14px}
    .inv .info b{color:#475569}
    .inv table{width:100%;border-collapse:collapse;font-size:.9rem}
    .inv th,.inv td{border-bottom:1px solid #e5e7eb;padding:.55rem .6rem;text-align:end}
    .inv th{background:#f8fafc;color:#475569;font-size:.74rem;text-transform:uppercase}
    .inv th:first-child,.inv td:first-child{text-align:start}
    .inv .status{font-size:.7rem;padding:.12rem .5rem;border-radius:999px}
    .inv .paid{background:#dcfce7;color:#15803d}.inv .partial{background:#fef9c3;color:#a16207}.inv .unpaid{background:#fee2e2;color:#b91c1c}
    .inv .totals{display:flex;justify-content:flex-end;gap:1.5rem;margin-top:16px;font-size:1rem}
    .inv .totals .due{font-weight:800;color:#b91c1c}
    .inv .stamp{margin-top:34px;text-align:end}
    .inv .stamp .s{display:inline-block;border-top:1.5px solid #475569;padding-top:5px;font-size:.82rem;color:#475569;min-width:160px;text-align:center}
@endsection

@section('documents')
@php $svc = app(\Modules\Printing\Services\PrintService::class); @endphp
@forelse($invoices as $data)
    @php $rec = $data['record']; @endphp
    <div class="inv">
        <div class="top">
            <div><h2>{{ $svc->schoolName() }}</h2><div style="color:#64748b;font-size:.85rem">Fee Statement</div></div>
            <div class="r">Date: {{ now()->format('d M Y') }}</div>
        </div>
        <div class="info">
            <b>Student:</b> {{ optional($data['student'])->full_name }} &nbsp;·&nbsp;
            <b>Class:</b> {{ optional(optional($rec)->schoolClass)->name }}/{{ optional(optional($rec)->section)->name }} &nbsp;·&nbsp;
            <b>Admission:</b> {{ optional($data['student'])->admission_no }}
        </div>
        <table>
            <thead><tr><th>Fee</th><th>Amount</th><th>Discount</th><th>Paid</th><th>Due</th><th style="text-align:center">Status</th></tr></thead>
            <tbody>
            @forelse($data['ledger'] as $line)
                @php $b = $line['balance']; $master = optional($line['assignment'])->master; @endphp
                <tr>
                    <td>{{ optional(optional($master)->type)->name ?? optional(optional($master)->group)->name ?? 'Fee' }}</td>
                    <td>{{ number_format($b['amount'], 2) }}</td>
                    <td>{{ number_format($b['discount'], 2) }}</td>
                    <td>{{ number_format($b['settled'], 2) }}</td>
                    <td>{{ number_format($b['due'], 2) }}</td>
                    <td style="text-align:center"><span class="status {{ $b['status'] }}">{{ ucfirst($b['status']) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:20px;color:#64748b;text-align:center">No fees assigned.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="totals">
            <span>Net: <b>{{ number_format($data['totals']['net'], 2) }}</b></span>
            <span>Paid: <b>{{ number_format($data['totals']['paid'], 2) }}</b></span>
            <span class="due">Due: {{ number_format($data['totals']['due'], 2) }}</span>
        </div>
        <div class="stamp"><span class="s">Accounts Officer</span></div>
    </div>
@empty
    <p style="text-align:center;color:#64748b;padding:40px">No students selected.</p>
@endforelse
@endsection
