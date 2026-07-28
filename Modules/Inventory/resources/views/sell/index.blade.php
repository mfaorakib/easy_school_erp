@extends('layouts.admin')
@section('title', 'Sell items')

@section('content')
<div class="page-head"><h1>Sell items</h1></div>

<div class="card">
    <h3 style="margin-top:0">Sell (stock-out by sale)</h3>
    <form method="POST" action="{{ route('inventory.sell.store') }}">
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
            <div><label>Sold to</label>
                <select name="sold_to">
                    <option value="">—</option>
                    @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                </select></div>
            <div><label>— or — Walk-in buyer name</label><input type="text" name="buyer_name"></div>
            <div><label>Walk-in buyer contact</label><input type="text" name="buyer_contact"></div>
            <div><label>Quantity</label><input type="number" name="quantity" min="1" step="1" required></div>
            <div><label>Unit price</label><input type="number" name="unit_price" min="0" step="0.01"></div>
            <div><label>Sold on</label><input type="date" name="sold_on" value="{{ $today }}" required></div>
            <div><label>Note</label><input type="text" name="note"></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">Sell</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Recent sells</h3>
    @if($recent->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Item</th><th>Sold to</th><th>Qty</th><th>Unit price</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @foreach($recent as $r)
            <tr>
                <td>
                    <strong>{{ optional($r->item)->name }}</strong>
                    @if($r->voucher_no)<div style="font-size:.78rem;color:#64748b">{{ $r->voucher_no }}</div>@endif
                </td>
                <td>{{ $r->buyerLabel() }}</td>
                <td>
                    {{ $r->quantity }}
                    @if($r->returned_quantity > 0)
                        <span style="font-size:.78rem;color:#b45309">({{ $r->returned_quantity }} returned)</span>
                    @endif
                </td>
                <td>{{ number_format($r->unit_price, 2) }}</td>
                <td>{{ optional($r->sold_on)->format('d M Y') }}</td>
                <td style="white-space:nowrap">
                    <a href="{{ route('inventory.sell.voucher', $r->id) }}" target="_blank" class="btn btn-sm btn-ghost">Voucher</a>
                    @if($r->net_quantity > 0)
                        <details style="display:inline-block">
                            <summary class="btn btn-sm" style="display:inline-block;cursor:pointer">{{ __('ui.return') }}</summary>
                            <form method="POST" action="{{ route('inventory.sell.adjust', $r->id) }}" style="margin-top:.4rem;display:flex;gap:.4rem;align-items:center">
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
