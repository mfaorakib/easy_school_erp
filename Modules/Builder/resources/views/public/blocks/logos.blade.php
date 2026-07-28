<div class="wrap {{ $sec['width'] }}">
    @if($block->get('heading'))
        <div class="sec-head" style="margin-bottom:34px"><h2>{{ $block->get('heading') }}</h2></div>
    @endif
    <div class="logo-row">
        @foreach($block->get('items', []) as $it)
            @php $img = \Modules\Builder\Services\BuilderService::media(data_get($it, 'image')); @endphp
            @if($img)
                <a class="logo-item" href="{{ data_get($it, 'url', '#') }}"><img src="{{ $img }}" alt=""></a>
            @endif
        @endforeach
    </div>
</div>
