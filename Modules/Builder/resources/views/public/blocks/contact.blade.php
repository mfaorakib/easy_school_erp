<div class="wrap {{ $sec['width'] }}">
    <div class="contact-grid">
        <div class="contact-info">
            <div class="sec-head" style="text-align:start;margin:0 0 24px">
                @if($block->get('eyebrow'))<span class="eyebrow">{{ $block->get('eyebrow') }}</span>@endif
                <h2>{{ $block->get('heading', 'Get in touch') }}</h2>
                @if($block->get('subheading'))<p>{{ $block->get('subheading') }}</p>@endif
            </div>
            <ul class="contact-list">
                @if($block->get('address'))<li><span>📍</span> {{ $block->get('address') }}</li>@endif
                @if($block->get('phone'))<li><span>📞</span> <a href="tel:{{ $block->get('phone') }}">{{ $block->get('phone') }}</a></li>@endif
                @if($block->get('email'))<li><span>✉️</span> <a href="mailto:{{ $block->get('email') }}">{{ $block->get('email') }}</a></li>@endif
            </ul>
        </div>
        <div class="contact-form card">
            @if(session('cms_sent'))
                <div class="form-ok">{{ session('cms_sent') }}</div>
            @endif
            <form method="POST" action="{{ route('builder.contact.submit') }}">
                @csrf
                <input type="hidden" name="type" value="contact">
                <div class="field"><label>Name</label><input name="name" required></div>
                <div class="field"><label>Email</label><input type="email" name="email" required></div>
                <div class="field"><label>Subject</label><input name="subject"></div>
                <div class="field"><label>Message</label><textarea name="body" rows="4" required></textarea></div>
                <button class="btn btn-primary" type="submit">Send Message</button>
            </form>
        </div>
    </div>
</div>
