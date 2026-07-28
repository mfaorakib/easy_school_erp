<div class="widget">
  <div class="widget-title">
     <span>{{ __('ui.notice_board') }}</span>
     <a href="{{ route('communication.notices.index') }}">{{ __('ui.view_all') }}</a>
  </div>
  <ul class="feed">
    @forelse($notices as $notice)
       <li>{{ $notice->title }} <span class="t">{{ optional($notice->notice_date)->format('d M') }}</span></li>
    @empty
       <li class="dash-empty">No notices.</li>
    @endforelse
  </ul>
</div>
