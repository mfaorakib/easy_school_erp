<div class="wrap narrow">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading', 'FAQ') }}</h2>
    </div>
    <div class="faq-list">
        @foreach($block->get('items', []) as $it)
            <details class="faq-item">
                <summary>{{ data_get($it, 'question') }}<span class="faq-mark">+</span></summary>
                <div class="faq-a">{{ data_get($it, 'answer') }}</div>
            </details>
        @endforeach
    </div>
</div>
