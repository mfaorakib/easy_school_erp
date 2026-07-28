@extends('layouts.admin')
@section('title', 'Receive stock')

@section('content')
<div class="page-head"><h1>Receive stock</h1></div>

<div class="card">
    <h3 style="margin-top:0">Receive (stock-in)</h3>
    <form method="POST" action="{{ route('inventory.receive.store') }}">
        @csrf
        <div class="grid">
            <div><label>Item</label>
                <select name="item_id" required>
                    <option value="">—</option>
                    @foreach($items as $it)<option value="{{ $it->id }}">{{ $it->name }} ({{ $it->stock }} in stock)</option>@endforeach
                </select></div>
            <div><label>Store</label>
                <select name="item_store_id">
                    <option value="">—</option>
                    @foreach($stores as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select></div>
            <div><label>Supplier</label>
                <select name="supplier_id">
                    <option value="">—</option>
                    @foreach($suppliers as $sp)<option value="{{ $sp->id }}">{{ $sp->name }}</option>@endforeach
                </select></div>
            <div><label>Quantity</label><input type="number" name="quantity" min="1" step="1" required></div>
            <div><label>Unit price</label><input type="number" name="unit_price" min="0" step="0.01"></div>
            <div><label>Received on</label><input type="date" name="received_on" value="{{ $today }}" required></div>
            <div><label>Note</label><input type="text" name="note"></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">Receive</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Recent receives</h3>
    @if($recent->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Item</th><th>Store</th><th>Supplier</th><th>Qty</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @foreach($recent as $r)
            <tr>
                <td>
                    <strong>{{ optional($r->item)->name }}</strong>
                    @if($r->voucher_no)<div style="font-size:.78rem;color:#64748b">{{ $r->voucher_no }}</div>@endif
                </td>
                <td>{{ optional($r->store)->name ?? '—' }}</td>
                <td>{{ optional($r->supplier)->name ?? '—' }}</td>
                <td>
                    {{ $r->quantity }}
                    @if($r->returned_quantity > 0)
                        <span style="font-size:.78rem;color:#b45309">({{ $r->returned_quantity }} returned)</span>
                    @endif
                </td>
                <td>{{ optional($r->received_on)->format('d M Y') }}</td>
                <td style="white-space:nowrap">
                    <a href="{{ route('inventory.receive.voucher', $r->id) }}" target="_blank" class="btn btn-sm btn-ghost">Voucher</a>
                    @if($r->net_quantity > 0)
                        <details style="display:inline-block">
                            <summary class="btn btn-sm" style="display:inline-block;cursor:pointer">{{ __('ui.return') }}</summary>
                            <form method="POST" action="{{ route('inventory.receive.adjust', $r->id) }}" style="margin-top:.4rem;display:flex;gap:.4rem;align-items:center">
                                @csrf
                                <input type="number" name="return_quantity" min="1" max="{{ $r->net_quantity }}" step="1" placeholder="Qty" style="width:70px" required>
                                <input type="text" name="reason" placeholder="{{ __('ui.reason') }}" style="width:140px" required>
                                <button class="btn btn-sm">{{ __('ui.save') }}</button>
                            </form>
                        </details>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
