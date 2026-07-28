<div class="wrap {{ $sec['width'] }}">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading', 'Pricing') }}</h2>
    </div>
    <div class="pricing-grid">
        @foreach($block->get('items', []) as $it)
            @php
                $features = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) data_get($it, 'features', ''))));
                $featured = filter_var(data_get($it, 'featured'), FILTER_VALIDATE_BOOL);
            @endphp
            <div class="price-card {{ $featured ? 'featured' : '' }}">
                @if($featured)<span class="price-badge">★</span>@endif
                <div class="price-name">{{ data_get($it, 'name') }}</div>
                <div class="price-amt"><b>{{ data_get($it, 'price') }}</b><span>{{ data_get($it, 'period') }}</span></div>
                <ul class="price-feats">
                    @foreach($features as $f)<li>{{ $f }}</li>@endforeach
                </ul>
                @if(data_get($it, 'cta_label'))
                    <a class="btn {{ $featured ? 'btn-primary' : 'btn-ghost' }}" href="{{ data_get($it, 'cta_url', '#') }}">{{ data_get($it, 'cta_label') }}</a>
                @endif
            </div>
        @endforeach
    </div>
</div>
