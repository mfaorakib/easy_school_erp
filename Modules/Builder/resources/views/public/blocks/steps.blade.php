<div class="wrap {{ $sec['width'] }}">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading', 'How it works') }}</h2>
    </div>
    <div class="steps">
        @foreach($block->get('items', []) as $it)
            <div class="step">
                <div class="step-no"></div>
                <h3>{{ data_get($it, 'title') }}</h3>
                <p>{{ data_get($it, 'text') }}</p>
            </div>
        @endforeach
    </div>
</div>
