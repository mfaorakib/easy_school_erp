<div class="widget">
  <div class="widget-title">
     <span>{{ __('ui.income_expense') }}</span>
     <a href="{{ route('accounting.summary') }}">{{ __('ui.view_all') }}</a>
  </div>
  @include('dashboard::partials.barchart', ['series' => $finance['series']])
  <div class="mini">
     <div class="box"><b>{{ number_format($finance['income_month'],0) }}</b><small>{{ __('ui.income') }} · {{ __('ui.this_month') }}</small></div>
     <div class="box"><b>{{ number_format($finance['fees_month'],0) }}</b><small>{{ __('ui.fees_collected') }}</small></div>
     <div class="box"><b>{{ number_format($finance['bank_balance'],0) }}</b><small>{{ __('ui.bank_balance') }}</small></div>
  </div>
</div>
