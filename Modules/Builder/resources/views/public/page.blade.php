@extends('builder::public.layout', ['title' => $page->title, 'meta' => $page->meta_description])

@section('body')
    @forelse($page->visibleBlocks as $block)
        @php $sec = $block->sectionStyle(); @endphp
        <section @if($sec['anchor']) id="{{ $sec['anchor'] }}" @endif
                 class="{{ $sec['classes'] }}"
                 @if($sec['style']) style="{{ $sec['style'] }}" @endif>
            @includeIf('builder::public.blocks.'.$block->type, ['block' => $block, 'sec' => $sec])
        </section>
    @empty
        <section class="cms-section pad-lg">
            <div class="wrap prose" style="text-align:center">
                <h2>{{ $page->title }}</h2>
                <p>This page has no sections yet.</p>
            </div>
        </section>
    @endforelse
@endsection
