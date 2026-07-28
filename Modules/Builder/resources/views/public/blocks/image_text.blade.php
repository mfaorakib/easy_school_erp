@php $img = \Modules\Builder\Services\BuilderService::media($block->get('image')); @endphp
<div class="wrap">
    <div class="split {{ $block->get('image_side') === 'right' ? 'reverse' : '' }}">
        <div class="split-media">
            @if($img)
                <img src="{{ $img }}" alt="">
            @else
                <div class="media-fallback">🖼️</div>
            @endif
        </div>
        <div class="split-body">
            @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
            <h2>{{ $block->get('heading') }}</h2>
            <div class="prose">{!! $block->get('body') !!}</div>
            @if($block->get('cta_label'))
                <a class="btn btn-primary" href="{{ $block->get('cta_url', '#') }}">{{ $block->get('cta_label') }} →</a>
            @endif
        </div>
    </div>
</div>
