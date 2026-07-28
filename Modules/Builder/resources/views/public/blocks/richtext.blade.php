<div class="wrap {{ $sec['width'] }}">
    @if($block->get('heading'))
        <div class="sec-head"><h2>{{ $block->get('heading') }}</h2></div>
    @endif
    <div class="prose">{!! $block->get('body') !!}</div>
</div>
