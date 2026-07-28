<div class="wrap {{ $sec['width'] }}">
    @if($block->get('heading'))
        <div class="sec-head" style="margin-bottom:24px"><h2>{{ $block->get('heading') }}</h2></div>
    @endif
    @php $url = trim((string) $block->get('embed_url')); @endphp
    <div class="map-frame">
        @if($url)
            <iframe src="{{ $url }}" title="{{ $block->get('heading', 'Map') }}" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
        @else
            <div class="map-empty">🗺️</div>
        @endif
    </div>
</div>
