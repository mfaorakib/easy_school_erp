<div class="wrap {{ $sec['width'] }}">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading') }}</h2>
        @if($block->get('subheading'))<p>{{ $block->get('subheading') }}</p>@endif
    </div>
</div>
