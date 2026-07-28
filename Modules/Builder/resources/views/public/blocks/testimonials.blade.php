@inject('builder', 'Modules\Builder\Services\BuilderService')
@php $items = $builder->activeTestimonials(); @endphp
<div class="wrap {{ $sec['width'] }}">
    <div class="sec-head">
        @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
        <h2>{{ $block->get('heading', 'What people say') }}</h2>
        @if($block->get('subheading'))<p>{{ $block->get('subheading') }}</p>@endif
    </div>
    @if($items->count())
        <div class="grid grid-3">
            @foreach($items as $t)
                @php $photo = \Modules\Builder\Services\BuilderService::media($t->photo_path); @endphp
                <div class="tcard">
                    @if($t->rating)<div class="stars">{{ str_repeat('★', (int) $t->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $t->rating)) }}</div>@endif
                    <blockquote>{{ $t->quote }}</blockquote>
                    <div class="who">
                        @if($photo)<img class="avatar" src="{{ $photo }}" alt="">@else<span class="avatar">{{ \Illuminate\Support\Str::substr($t->name, 0, 1) }}</span>@endif
                        <div><b>{{ $t->name }}</b><small>{{ $t->designation }}@if($t->organization), {{ $t->organization }}@endif</small></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
