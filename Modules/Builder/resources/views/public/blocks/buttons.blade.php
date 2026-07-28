<div class="wrap {{ $sec['width'] }}">
    <div class="btn-row">
        @foreach($block->get('items', []) as $it)
            @if(data_get($it, 'label'))
                <a class="btn {{ data_get($it, 'style') === 'ghost' ? 'btn-ghost' : 'btn-primary' }}" href="{{ data_get($it, 'url', '#') }}">{{ data_get($it, 'label') }}</a>
            @endif
        @endforeach
    </div>
</div>
