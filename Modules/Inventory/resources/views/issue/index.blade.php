@extends('layouts.admin')
@section('title', 'Issue items')

@section('content')
<div class="page-head"><h1>Issue items</h1></div>

<div class="card">
    <h3 style="margin-top:0">Issue (stock-out, returnable)</h3>
    <form method="POST" action="{{ route('inventory.issue.store') }}">
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
            <div><label>Issued to</label>
                <select name="issued_to">
                    <option value="">—</option>
                    @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                </select></div>
            <div><label>Quantity</label><input type="number" name="quantity" min="1" step="1" required></div>
            <div><label>Issue date</label><input type="date" name="issue_date" value="{{ $today }}" required></div>
            <div><label>Note</label><input type="text" name="note"></div>
            <div style="display:flex;align-items:flex-end"><button class="btn">{{ __('ui.issue') }}</button></div>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0">Open issues (not returned)</h3>
    @if($open->isEmpty())<div class="empty">{{ __('ui.no_records') }}</div>@else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>Item</th><th>Issued to</th><th>Qty</th><th>Issued</th><th></th></tr></thead>
        <tbody>
        @foreach($open as $i)
            <tr>
                <td><strong>{{ optional($i->item)->name }}</strong></td>
                <td>{{ optional($i->issuedTo)->name ?? '—' }}</td>
                <td>{{ $i->quantity }}</td>
                <td>{{ optional($i->issue_date)->format('d M Y') }}</td>
                <td>
                    <form method="POST" action="{{ route('inventory.issue.return', $i) }}">
                        @csrf<button class="btn btn-sm">{{ __('ui.return') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
