@php $cols = $block->get('columns', '3'); @endphp
<div class="wrap {{ $sec['width'] }}">
    @if($block->get('heading'))
        <div class="sec-head" style="margin-bottom:34px"><h2>{{ $block->get('heading') }}</h2></div>
    @endif
    <div class="gallery-grid cols-{{ $cols }}">
        @foreach($block->get('images', []) as $it)
            @php $img = \Modules\Builder\Services\BuilderService::media(data_get($it, 'image')); @endphp
            @if($img)
                <figure class="gallery-cell">
                    <img src="{{ $img }}" alt="{{ data_get($it, 'caption') }}">
                    @if(data_get($it, 'caption'))<figcaption>{{ data_get($it, 'caption') }}</figcaption>@endif
                </figure>
            @endif
        @endforeach
    </div>
</div>
