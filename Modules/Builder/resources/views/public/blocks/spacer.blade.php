@php $h = ['sm' => 24, 'md' => 56, 'lg' => 96][$block->get('height', 'md')] ?? 56; @endphp
<div class="wrap {{ $sec['width'] }}">
    @if($block->get('divider'))
        <hr class="cms-divider" style="margin:{{ $h / 2 }}px 0">
    @else
        <div style="height:{{ $h }}px"></div>
    @endif
</div>
