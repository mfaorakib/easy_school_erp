<div class="wrap {{ $sec['width'] }}">
    <div class="banner-inner">
        <span>{{ $block->get('text') }}</span>
        @if($block->get('link_label'))<a class="btn btn-ghost" href="{{ $block->get('link_url', '#') }}">{{ $block->get('link_label') }} →</a>@endif
    </div>
</div>
