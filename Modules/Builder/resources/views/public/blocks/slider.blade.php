@inject('builder', 'Modules\Builder\Services\BuilderService')
@php $slides = $builder->activeSliders(); @endphp
@if($slides->count())
    @if($block->get('heading'))
        <div class="wrap sec-head" style="margin-bottom:24px"><h2>{{ $block->get('heading') }}</h2></div>
    @endif
    <div class="cms-slider">
        @foreach($slides as $s)
            @php $img = \Modules\Builder\Services\BuilderService::media($s->image_path); @endphp
            <div class="cms-slide @if($img) has-image @endif" @if($img) style="--hero-img:url('{{ $img }}')" @endif>
                <div class="wrap">
                    @if($s->title)<h2>{{ $s->title }}</h2>@endif
                    @if($s->subtitle)<p>{{ $s->subtitle }}</p>@endif
                    @if($s->link_url && $s->link_label)
                        <div class="actions"><a class="btn btn-primary" href="{{ $s->link_url }}">{{ $s->link_label }} →</a></div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
