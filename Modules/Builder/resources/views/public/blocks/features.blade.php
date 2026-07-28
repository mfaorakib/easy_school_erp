@php $cols = $block->get('columns', '3'); @endphp
<div class="wrap {{ $sec['width'] }}">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading', 'Features') }}</h2>
    </div>
    <div class="grid grid-{{ $cols }}">
        @foreach($block->get('items', []) as $it)
            <div class="card">
                <div class="chip">{{ data_get($it, 'icon', '✨') }}</div>
                <h3>{{ data_get($it, 'title') }}</h3>
                <p>{{ data_get($it, 'text') }}</p>
            </div>
        @endforeach
    </div>
</div>
