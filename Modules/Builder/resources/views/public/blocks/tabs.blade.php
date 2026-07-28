@php $items = $block->get('items', []); @endphp
<div class="wrap {{ $sec['width'] }}">
    @if($block->get('heading'))
        <div class="sec-head"><h2>{{ $block->get('heading') }}</h2></div>
    @endif
    @if(count($items))
        <div class="tabset" data-tabs>
            <div class="tab-btns" role="tablist">
                @foreach($items as $i => $it)
                    <button type="button" class="tab-btn {{ $i === 0 ? 'active' : '' }}" data-tab="{{ $i }}">{{ data_get($it, 'title', 'Tab '.($i + 1)) }}</button>
                @endforeach
            </div>
            <div class="tab-panels">
                @foreach($items as $i => $it)
                    <div class="tab-panel {{ $i === 0 ? 'active' : '' }}" data-panel="{{ $i }}">
                        <div class="prose">{!! data_get($it, 'content') !!}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
