@extends('documents::print.layout', ['title' => 'Bank Payment Advice'])

@section('print-styles')
    .adv{background:#fff;max-width:900px;margin:24px auto;padding:32px 40px;box-shadow:0 14px 36px -18px rgba(2,6,23,.35);
        border:1px solid #e5e7eb}
    .adv .top{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #4f46e5;padding-bottom:12px;margin-bottom:16px}
    .adv .top h2{margin:0;font-size:1.35rem}
    .adv .top .r{text-align:end;font-size:.85rem;color:#64748b}
    .adv table{width:100%;border-collapse:collapse;font-size:.9rem}
    .adv th,.adv td{border-bottom:1px solid #e5e7eb;padding:.55rem .6rem;text-align:end}
    .adv th{background:#f8fafc;color:#475569;font-size:.74rem;text-transform:uppercase}
    .adv th:first-child,.adv td:first-child,.adv th:nth-child(2),.adv td:nth-child(2){text-align:start}
    .adv tfoot th{text-align:end;font-size:.95rem}
@endsection

@section('documents')
    <div class="adv">
        <div class="top">
            <h2>Bank Payment Advice</h2>
            <div class="r">Period: {{ $period }}<br>Date: {{ now()->format('d M Y') }}</div>
        </div>

        @if($list->isEmpty())
            <p style="text-align:center;color:#64748b;padding:40px">No bank payments for this period yet.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Staff No</th>
                        <th>Name</th>
                        <th>Bank</th>
                        <th>Account No</th>
                        <th>Branch</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                @php $total = 0; @endphp
                @foreach($list as $payslip)
                    @php $total += $payslip->payableAmount(); @endphp
                    <tr>
                        <td>{{ optional($payslip->staff)->staff_no }}</td>
                        <td>{{ optional($payslip->staff)->displayName() ?? '—' }}</td>
                        <td>{{ optional($payslip->staff)->bank_name }}</td>
                        <td>{{ optional($payslip->staff)->bank_account_no }}</td>
                        <td>{{ optional($payslip->staff)->bank_branch }}</td>
                        <td>{{ number_format($payslip->payableAmount(), 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5">Total</th>
                        <th>{{ number_format($total, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
@endsection
