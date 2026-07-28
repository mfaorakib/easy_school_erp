@extends('documents::print.layout', ['title' => 'Sales Voucher'])

@section('print-styles')
    .voucher{background:#fff;max-width:720px;margin:24px auto;padding:32px 40px;box-shadow:0 14px 36px -18px rgba(2,6,23,.35);
        border:1px solid #e5e7eb}
    .voucher .top{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #4f46e5;padding-bottom:12px;margin-bottom:16px}
    .voucher .top h2{margin:0;font-size:1.35rem}
    .voucher .top .r{text-align:end;font-size:.85rem;color:#64748b}
    .voucher .info{font-size:.9rem;margin-bottom:14px;display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem}
    .voucher .info b{color:#475569}
    .voucher table{width:100%;border-collapse:collapse;font-size:.9rem;margin-top:10px}
    .voucher th,.voucher td{border-bottom:1px solid #e5e7eb;padding:.55rem .6rem;text-align:end}
    .voucher th{background:#f8fafc;color:#475569;font-size:.74rem;text-transform:uppercase}
    .voucher th:first-child,.voucher td:first-child{text-align:start}
    .voucher .totals{display:flex;justify-content:flex-end;gap:1.5rem;margin-top:16px;font-size:1rem}
    .voucher .totals .grand{font-weight:800;color:#0f172a}
    .voucher .returned-note{margin-top:14px;font-size:.85rem;color:#a16207;background:#fef9c3;padding:.55rem .75rem;border-radius:6px}
    .voucher .note-box{margin-top:14px;font-size:.9rem}
    .voucher .note-box b{color:#475569}
    .voucher .stamp{margin-top:40px;text-align:end}
    .voucher .stamp .s{display:inline-block;border-top:1.5px solid #475569;padding-top:5px;font-size:.82rem;color:#475569;min-width:160px;text-align:center}
@endsection

@section('documents')
@php $total = (float) $sell->quantity * (float) $sell->unit_price; @endphp
<div class="voucher">
    <div class="top">
        <div>
            <h2>Sales Voucher</h2>
            <div style="color:#64748b;font-size:.85rem">Stock Sale</div>
        </div>
        <div class="r">
            Voucher No: <strong>{{ $sell->voucher_no ?? '—' }}</strong><br>
            Date: {{ optional($sell->sold_on)->format('d M Y') }}
        </div>
    </div>

    <div class="info">
        <div><b>Item:</b> {{ optional($sell->item)->name ?? '—' }}</div>
        <div>
            <b>Buyer:</b> {{ $sell->buyerLabel() }}
            @if(!$sell->soldTo && $sell->buyer_contact)
                &nbsp;·&nbsp; {{ $sell->buyer_contact }}
            @endif
        </div>
    </div>

    <table>
        <thead><tr><th>Item</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ optional($sell->item)->name ?? '—' }}</td>
                <td>{{ $sell->quantity }}</td>
                <td>{{ number_format($sell->unit_price, 2) }}</td>
                <td>{{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($sell->returned_quantity > 0)
        <div class="returned-note">
            {{ $sell->returned_quantity }} unit(s) returned by buyer — net quantity retained: <strong>{{ $sell->net_quantity }}</strong>
        </div>
    @endif

    <div class="totals"><span class="grand">Total: {{ number_format($total, 2) }}</span></div>

    @if($sell->note)
        <div class="note-box"><b>Note:</b> {{ $sell->note }}</div>
    @endif

    <div class="stamp"><span class="s">Sold By</span></div>
</div>
@endsection
