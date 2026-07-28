@php $img = \Modules\Builder\Services\BuilderService::media($block->get('image')); @endphp
@if($block->get('layout') === 'left' && $img)
    <div class="wrap">
        <div class="split hero-split">
            <div class="split-body">
                @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
                <h1>{{ $block->get('headline', 'Welcome') }}</h1>
                @if($block->get('subheadline'))<p>{{ $block->get('subheadline') }}</p>@endif
                <div class="actions">
                    @if($block->get('cta_label'))<a class="btn btn-primary" href="{{ $block->get('cta_url', '#') }}">{{ $block->get('cta_label') }}</a>@endif
                    @if($block->get('cta2_label'))<a class="btn btn-ghost" href="{{ $block->get('cta2_url', '#') }}">{{ $block->get('cta2_label') }}</a>@endif
                </div>
            </div>
            <div class="split-media"><img src="{{ $img }}" alt=""></div>
        </div>
    </div>
@else
    <div class="wrap" style="text-align:center">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h1>{{ $block->get('headline', 'Welcome') }}</h1>
        @if($block->get('subheadline'))<p style="max-width:680px;margin-inline:auto">{{ $block->get('subheadline') }}</p>@endif
        <div class="actions" style="justify-content:center;display:flex;gap:.8rem;flex-wrap:wrap;margin-top:1.6rem">
            @if($block->get('cta_label'))<a class="btn btn-primary" href="{{ $block->get('cta_url', '#') }}">{{ $block->get('cta_label') }}</a>@endif
            @if($block->get('cta2_label'))<a class="btn btn-ghost" href="{{ $block->get('cta2_url', '#') }}">{{ $block->get('cta2_label') }}</a>@endif
        </div>
    </div>
@endif
