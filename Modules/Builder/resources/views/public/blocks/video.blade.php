@php
    $url = trim((string) $block->get('video_url'));
    $embed = null;
    if ($url) {
        if (preg_match('~youtube\.com/watch\?v=([\w-]+)~', $url, $m) || preg_match('~youtu\.be/([\w-]+)~', $url, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            $embed = 'https://player.vimeo.com/video/'.$m[1];
        }
    }
@endphp
<div class="wrap narrow">
    @if($block->get('heading'))
        <div class="sec-head">
            @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
            <h2>{{ $block->get('heading') }}</h2>
            @if($block->get('subheading'))<p>{{ $block->get('subheading') }}</p>@endif
        </div>
    @endif
    <div class="video-frame">
        @if($embed)
            <iframe src="{{ $embed }}" title="{{ $block->get('heading', 'Video') }}" allowfullscreen loading="lazy"></iframe>
        @elseif($url)
            <video src="{{ $url }}" controls></video>
        @else
            <div class="video-empty">🎬</div>
        @endif
    </div>
</div>
