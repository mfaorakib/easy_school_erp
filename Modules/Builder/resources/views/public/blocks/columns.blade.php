@php $cols = $block->get('columns', '3'); @endphp
<div class="wrap {{ $sec['width'] }}">
    @if($block->get('eyebrow') || $block->get('heading'))
        <div class="sec-head">
            @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
            @if($block->get('heading'))<h2>{{ $block->get('heading') }}</h2>@endif
        </div>
    @endif
    <div class="grid grid-{{ $cols }}">
        @foreach($block->get('items', []) as $it)
            @php $img = \Modules\Builder\Services\BuilderService::media(data_get($it, 'image')); @endphp
            <div class="card col-card">
                @if($img)<img class="col-card-img" src="{{ $img }}" alt=""> @endif
                <div class="col-card-body">
                    @if(data_get($it, 'heading'))<h3>{{ data_get($it, 'heading') }}</h3>@endif
                    @if(data_get($it, 'text'))<p>{{ data_get($it, 'text') }}</p>@endif
                    @if(data_get($it, 'link_label'))
                        <a class="btn btn-ghost" href="{{ data_get($it, 'link_url', '#') }}">{{ data_get($it, 'link_label') }} →</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
