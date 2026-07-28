<div class="wrap {{ $sec['width'] }}">
    @if($block->get('heading'))
        <div class="sec-head" style="margin-bottom:34px"><h2>{{ $block->get('heading') }}</h2></div>
    @endif
    <div class="grid grid-4" style="text-align:center">
        @foreach($block->get('items', []) as $it)
            <div class="stat"><b>{{ data_get($it, 'value') }}</b><span>{{ data_get($it, 'label') }}</span></div>
        @endforeach
    </div>
</div>
