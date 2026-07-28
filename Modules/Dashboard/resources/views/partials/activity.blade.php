<div class="widget">
  <div class="widget-title"><span>{{ __('ui.recent_activity') }}</span></div>
  <ul class="feed">
    @forelse($activity as $item)
       <li>
         <span><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
         @if($item['url'] ?? null)
             <span><a href="{{ $item['url'] }}">{{ $item['text'] }}</a></span>
         @else
             <span>{{ $item['text'] }}</span>
         @endif
         <span class="t">{{ $item['time'] ? $item['time']->diffForHumans() : '' }}</span>
       </li>
    @empty
       <li class="dash-empty">No recent activity.</li>
    @endforelse
  </ul>
</div>
