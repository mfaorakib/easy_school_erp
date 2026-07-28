<div class="wrap {{ $sec['width'] }}" style="text-align:center">
    <h2>{{ $block->get('headline', 'Ready to get started?') }}</h2>
    @if($block->get('subtext'))<p style="max-width:620px;margin-inline:auto">{{ $block->get('subtext') }}</p>@endif
    <div class="actions" style="justify-content:center;display:flex;gap:.8rem;flex-wrap:wrap;margin-top:1.6rem">
        @if($block->get('button_label'))<a class="btn btn-primary" href="{{ $block->get('button_url', '#') }}">{{ $block->get('button_label') }}</a>@endif
        @if($block->get('button2_label'))<a class="btn btn-ghost" href="{{ $block->get('button2_url', '#') }}">{{ $block->get('button2_label') }}</a>@endif
    </div>
</div>
