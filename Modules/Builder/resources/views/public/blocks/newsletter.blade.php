<div class="wrap narrow" style="text-align:center">
    <h2>{{ $block->get('heading', 'Stay in the loop') }}</h2>
    @if($block->get('subtext'))<p style="max-width:520px;margin-inline:auto">{{ $block->get('subtext') }}</p>@endif
    @if(session('cms_sent'))<div class="form-ok" style="max-width:520px;margin:1rem auto 0">{{ session('cms_sent') }}</div>@endif
    <form method="POST" action="{{ route('builder.contact.submit') }}" class="newsletter-form">
        @csrf
        <input type="hidden" name="type" value="subscribe">
        <input type="email" name="email" placeholder="you@example.com" required>
        <button class="btn btn-primary" type="submit">{{ $block->get('button_label', 'Subscribe') }}</button>
    </form>
</div>
