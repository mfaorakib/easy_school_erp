<div class="widget">
  <div class="widget-title">
     <span>{{ __('ui.upcoming_events') }}</span>
     <a href="{{ route('communication.events.index') }}">{{ __('ui.view_all') }}</a>
  </div>
  <ul class="feed">
    @forelse($events as $event)
       <li>
         <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> {{ $event->title }}
         @if($event->location) <small style="color:var(--muted)">· {{ $event->location }}</small> @endif
         <span class="t">{{ optional($event->start_date)->format('d M') }}</span>
       </li>
    @empty
       <li class="dash-empty">No upcoming events.</li>
    @endforelse
  </ul>
</div>
