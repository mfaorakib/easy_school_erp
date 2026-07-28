@php $cols = $block->get('columns', '3'); @endphp
<div class="wrap {{ $sec['width'] }}">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading', 'Services') }}</h2>
    </div>
    <div class="grid grid-{{ $cols }}">
        @foreach($block->get('items', []) as $it)
            @php $link = data_get($it, 'link_url'); @endphp
            @if($link)
                <a href="{{ $link }}" class="card">
                    <div class="chip">{{ data_get($it, 'icon', '✨') }}</div>
                    <h3>{{ data_get($it, 'title') }}</h3>
                    <p>{{ data_get($it, 'text') }}</p>
                    <span class="eyebrow" style="margin-top:.6rem">Learn more →</span>
                </a>
            @else
                <div class="card">
                    <div class="chip">{{ data_get($it, 'icon', '✨') }}</div>
                    <h3>{{ data_get($it, 'title') }}</h3>
                    <p>{{ data_get($it, 'text') }}</p>
                </div>
            @endif
        @endforeach
    </div>
</div>
