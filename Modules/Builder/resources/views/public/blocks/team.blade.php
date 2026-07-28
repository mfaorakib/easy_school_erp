<div class="wrap {{ $sec['width'] }}">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading', 'Meet the team') }}</h2>
    </div>
    <div class="grid grid-4">
        @foreach($block->get('items', []) as $it)
            @php $photo = \Modules\Builder\Services\BuilderService::media(data_get($it, 'photo')); $link = data_get($it, 'link_url'); @endphp
            <{{ $link ? 'a' : 'div' }} class="team-card" @if($link) href="{{ $link }}" @endif>
                @if($photo)
                    <img class="team-photo" src="{{ $photo }}" alt="">
                @else
                    <span class="team-photo team-initial">{{ \Illuminate\Support\Str::substr(data_get($it, 'name', '?'), 0, 1) }}</span>
                @endif
                <h3>{{ data_get($it, 'name') }}</h3>
                <span class="team-role">{{ data_get($it, 'role') }}</span>
            </{{ $link ? 'a' : 'div' }}>
        @endforeach
    </div>
</div>
