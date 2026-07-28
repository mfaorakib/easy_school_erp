@inject('builder', 'Modules\Builder\Services\BuilderService')
@php
    $settings = $builder->settings();
    $headerMenu = $builder->menu('header');
    $footerMenu = $builder->menu('footer');
    $primary = $settings->primary_color ?: '#4f46e5';
    $secondary = $settings->secondary_color ?: '#0ea5e9';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ data_get(config('locales.available'), app()->getLocale().'.rtl', false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $settings->site_name }}</title>
    @if($settings->tagline)<meta name="description" content="{{ $meta ?? $settings->tagline }}">@endif
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        :root{
            --primary:{{ $primary }};
            --secondary:{{ $secondary }};
            --ink:#0f172a; --muted:#64748b; --line:#e5e7eb;
            --bg:#ffffff; --soft:#f8fafc; --card:#ffffff;
            --radius:18px; --shadow:0 10px 30px -12px rgba(2,6,23,.18);
            --shadow-lg:0 30px 60px -20px rgba(2,6,23,.28);
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{margin:0;font-family:'Segoe UI',system-ui,-apple-system,Roboto,'Noto Sans Bengali','Noto Sans Arabic',sans-serif;color:var(--ink);background:var(--bg);line-height:1.6;-webkit-font-smoothing:antialiased}
        a{color:inherit;text-decoration:none}
        img{max-width:100%;display:block}
        .wrap{max-width:1140px;margin:0 auto;padding:0 22px}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.8rem 1.5rem;border-radius:999px;font-weight:600;font-size:.95rem;border:0;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease}
        .btn-primary{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;box-shadow:0 10px 24px -8px color-mix(in srgb,var(--primary) 70%,transparent)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 30px -8px color-mix(in srgb,var(--primary) 70%,transparent)}
        .btn-ghost{background:#fff;color:var(--ink);border:1px solid var(--line)}
        .btn-ghost:hover{border-color:var(--primary);color:var(--primary)}
        .eyebrow{display:inline-block;font-size:.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--primary);background:color-mix(in srgb,var(--primary) 12%,#fff);padding:.35rem .8rem;border-radius:999px;margin-bottom:1rem}
        section{padding:84px 0}
        .sec-head{text-align:center;max-width:640px;margin:0 auto 54px}
        .sec-head h2{font-size:clamp(1.7rem,3.4vw,2.6rem);margin:.4rem 0;letter-spacing:-.02em;line-height:1.15}
        .sec-head p{color:var(--section-text,var(--muted));font-size:1.08rem;margin:0}

        /* Header */
        header.site{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.78);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
        .nav{display:flex;align-items:center;justify-content:space-between;height:72px}
        .brand{display:flex;align-items:center;gap:.6rem;font-weight:800;font-size:1.2rem;letter-spacing:-.02em}
        .brand .logo{width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,var(--primary),var(--secondary));display:grid;place-items:center;color:#fff;font-weight:800}
        .brand img{width:38px;height:38px;border-radius:11px;object-fit:cover}
        .menu{display:flex;align-items:center;gap:.35rem;list-style:none;margin:0;padding:0}
        .menu a{padding:.55rem .9rem;border-radius:10px;font-weight:600;font-size:.95rem;color:var(--ink);transition:background .15s,color .15s}
        .menu a:hover{background:var(--soft);color:var(--primary)}
        .nav .cta-login{margin-inline-start:.4rem}
        .burger{display:none;background:none;border:0;font-size:1.6rem;cursor:pointer;color:var(--ink)}
        @media(max-width:820px){
            .menu{display:none;position:absolute;top:72px;inset-inline:0;flex-direction:column;background:#fff;border-bottom:1px solid var(--line);padding:12px 22px;gap:.2rem;align-items:stretch}
            .menu.open{display:flex}
            .burger{display:block}
        }

        /* Hero */
        .hero{position:relative;overflow:hidden;color:#fff;background:linear-gradient(135deg,var(--primary),var(--secondary))}
        .hero::before{content:"";position:absolute;inset:0;background:radial-gradient(900px 500px at 15% -10%,rgba(255,255,255,.28),transparent 60%),radial-gradient(700px 500px at 100% 120%,rgba(0,0,0,.18),transparent 55%)}
        .hero.has-image::after{content:"";position:absolute;inset:0;background:var(--hero-img);background-size:cover;background-position:center;opacity:.20;mix-blend-mode:overlay}
        .hero .wrap{position:relative;padding:110px 22px;text-align:center}
        .hero h1{font-size:clamp(2.2rem,5.2vw,4rem);line-height:1.05;letter-spacing:-.03em;margin:0 0 1.1rem;font-weight:800;text-shadow:0 2px 30px rgba(0,0,0,.15)}
        .hero p{font-size:clamp(1.05rem,2vw,1.3rem);max-width:680px;margin:0 auto 2rem;opacity:.94}
        .hero .actions{display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap}
        .hero .btn-ghost{background:rgba(255,255,255,.14);color:#fff;border-color:rgba(255,255,255,.4)}
        .hero .btn-ghost:hover{background:rgba(255,255,255,.24);color:#fff}

        /* Feature cards */
        .grid{display:grid;gap:24px}
        .grid-3{grid-template-columns:repeat(3,1fr)}
        .grid-4{grid-template-columns:repeat(4,1fr)}
        @media(max-width:900px){.grid-3,.grid-4{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:560px){.grid-3,.grid-4{grid-template-columns:1fr}}
        .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:30px;box-shadow:var(--shadow);transition:transform .18s ease,box-shadow .18s ease}
        .card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg)}
        .card .chip{width:54px;height:54px;border-radius:15px;display:grid;place-items:center;font-size:1.6rem;background:color-mix(in srgb,var(--primary) 12%,#fff);margin-bottom:1rem}
        .card h3{margin:.2rem 0 .5rem;font-size:1.2rem}
        .card p{color:var(--muted);margin:0}

        /* Stats */
        .stats{background:var(--soft);border-radius:var(--radius)}
        .stat b{display:block;font-size:clamp(2rem,4vw,3rem);font-weight:800;background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;background-clip:text;color:transparent;letter-spacing:-.02em}
        .stat span{color:var(--muted);font-weight:600}

        /* Rich text */
        .prose{max-width:760px;margin:0 auto;font-size:1.1rem;color:var(--section-text,#334155)}
        .prose h2{color:var(--section-text,var(--ink))}
        .prose img{max-width:100%;border-radius:14px;margin:1rem 0;box-shadow:var(--shadow)}

        /* Testimonials */
        .tcard{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow)}
        .tcard .stars{color:#f59e0b;margin-bottom:.6rem}
        .tcard blockquote{margin:0 0 1.1rem;font-size:1.05rem;color:#334155}
        .tcard .who{display:flex;align-items:center;gap:.8rem}
        .avatar{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;display:grid;place-items:center;font-weight:700;object-fit:cover}
        .tcard .who b{display:block}
        .tcard .who small{color:var(--muted)}

        /* CTA band */
        .cta-band{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;border-radius:26px;padding:60px;text-align:center;position:relative;overflow:hidden}
        .cta-band::before{content:"";position:absolute;inset:0;background:radial-gradient(600px 300px at 100% 0,rgba(255,255,255,.2),transparent 60%)}
        .cta-band h2{position:relative;font-size:clamp(1.7rem,3.4vw,2.5rem);margin:0 0 .6rem}
        .cta-band p{position:relative;opacity:.95;margin:0 0 1.6rem}
        .cta-band .btn-ghost{background:#fff;color:var(--primary);border:0;position:relative}

        /* Gallery */
        .gallery-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        @media(max-width:700px){.gallery-grid{grid-template-columns:repeat(2,1fr)}}
        .gallery-grid img{width:100%;height:220px;object-fit:cover;border-radius:14px;box-shadow:var(--shadow)}

        /* Footer */
        footer.site{background:#0b1220;color:#cbd5e1;padding:64px 0 28px;margin-top:20px}
        footer.site .cols{display:grid;grid-template-columns:2fr 1fr 1fr;gap:40px}
        @media(max-width:760px){footer.site .cols{grid-template-columns:1fr}}
        footer.site h4{color:#fff;font-size:1rem;margin:0 0 1rem;letter-spacing:.02em}
        footer.site a{color:#cbd5e1;display:block;padding:.25rem 0}
        footer.site a:hover{color:#fff}
        footer.site .brand{color:#fff;margin-bottom:.8rem}
        footer.site .social{display:flex;gap:.6rem;margin-top:1rem}
        footer.site .social a{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.08);display:grid;place-items:center;padding:0}
        footer.site .social a:hover{background:linear-gradient(135deg,var(--primary),var(--secondary))}
        .copy{border-top:1px solid rgba(255,255,255,.1);margin-top:40px;padding-top:22px;text-align:center;color:#94a3b8;font-size:.9rem}

        /* ============ Page-builder section system ============ */
        .cms-section{position:relative;color:var(--section-text,inherit)}
        .cms-section.pad-none{padding:0}
        .cms-section.pad-sm{padding:22px 0}
        .cms-section.pad-md{padding:48px 0}
        .cms-section.pad-lg{padding:84px 0}
        .cms-section.pad-xl{padding:120px 0}
        .cms-section.is-soft{background:var(--soft)}
        .cms-section.align-center{text-align:center}
        .cms-section.align-center .sec-head{margin-inline:auto}
        .cms-section.align-right{text-align:right}
        .cms-section.has-bg-image{background-size:cover;background-position:center}
        .cms-section.has-bg-image::before{content:"";position:absolute;inset:0;background:rgba(2,6,23,var(--overlay,.45))}
        .cms-section.has-bg-image > *{position:relative;z-index:1}
        .cms-section.on-dark,.cms-section.on-dark h1,.cms-section.on-dark h2,.cms-section.on-dark h3,.cms-section.on-dark h4,.cms-section.on-dark p,.cms-section.on-dark li{color:#fff}
        .cms-section.on-dark .sec-head p,.cms-section.on-dark p{opacity:.92}
        .cms-section.on-dark .eyebrow{background:rgba(255,255,255,.16);color:#fff}
        .cms-section.on-dark .card{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.18)}
        .cms-section.on-dark .btn-ghost{background:rgba(255,255,255,.14);color:#fff;border-color:rgba(255,255,255,.4)}
        .cms-section.on-dark .stat b{-webkit-text-fill-color:#fff;color:#fff}
        .cms-section.on-light,.cms-section.on-light h1,.cms-section.on-light h2,.cms-section.on-light p{color:var(--ink)}

        /* container widths */
        .wrap.narrow{max-width:760px}
        .wrap.full{max-width:none;padding-inline:40px}
        @media(max-width:600px){.wrap.full{padding-inline:22px}}

        /* split — image + text / hero-left */
        .split{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
        .split.reverse .split-media{order:2}
        .split-media img{width:100%;border-radius:var(--radius);box-shadow:var(--shadow-lg)}
        .split-body h2{margin-top:.3rem}
        .hero-split .split-body h1{font-size:clamp(2rem,4vw,3.2rem);line-height:1.08;letter-spacing:-.02em;margin:.4rem 0 1rem}
        .media-fallback{aspect-ratio:4/3;border-radius:var(--radius);display:grid;place-items:center;font-size:3rem;background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff}
        @media(max-width:820px){.split{grid-template-columns:1fr;gap:28px}.split.reverse .split-media{order:0}}

        /* steps / timeline */
        .steps{counter-reset:step;display:grid;grid-template-columns:repeat(3,1fr);gap:28px}
        .steps .step{position:relative}
        .steps .step-no::before{counter-increment:step;content:counter(step);display:grid;place-items:center;width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;font-weight:800;margin-bottom:1rem}
        .steps .step h3{margin:.2rem 0 .4rem}.steps .step p{color:var(--muted);margin:0}
        @media(max-width:820px){.steps{grid-template-columns:1fr}}

        /* logos */
        .logo-row{display:flex;flex-wrap:wrap;gap:40px;align-items:center;justify-content:center}
        .logo-item img{height:44px;object-fit:contain;filter:grayscale(1);opacity:.65;transition:filter .2s,opacity .2s}
        .logo-item:hover img{filter:none;opacity:1}

        /* pricing */
        .pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;align-items:start}
        .price-card{position:relative;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:32px;box-shadow:var(--shadow);text-align:center}
        .price-card.featured{border-color:var(--primary);box-shadow:var(--shadow-lg);transform:translateY(-6px)}
        .price-badge{position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;width:30px;height:30px;border-radius:50%;display:grid;place-items:center}
        .price-name{font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;font-size:.85rem}
        .price-amt{margin:.6rem 0 1.2rem}.price-amt b{font-size:2.6rem;letter-spacing:-.02em}.price-amt span{color:var(--muted)}
        .price-feats{list-style:none;margin:0 0 1.4rem;padding:0;text-align:start}
        .price-feats li{padding:.5rem 0;border-bottom:1px solid var(--line);color:#334155}
        .price-feats li::before{content:"✓";color:var(--primary);font-weight:800;margin-inline-end:.5rem}
        .price-card .btn{width:100%;justify-content:center}
        @media(max-width:820px){.pricing-grid{grid-template-columns:1fr}}

        /* faq / accordion */
        .faq-list{display:flex;flex-direction:column;gap:12px}
        .faq-item{border:1px solid var(--line);border-radius:14px;background:var(--card);overflow:hidden}
        .faq-item summary{list-style:none;cursor:pointer;padding:18px 22px;font-weight:600;display:flex;justify-content:space-between;align-items:center;gap:1rem}
        .faq-item summary::-webkit-details-marker{display:none}
        .faq-item .faq-mark{color:var(--primary);font-size:1.4rem;transition:transform .2s}
        .faq-item[open] .faq-mark{transform:rotate(45deg)}
        .faq-item .faq-a{padding:0 22px 18px;color:var(--muted)}

        /* tabs */
        .tab-btns{display:flex;flex-wrap:wrap;gap:.4rem;border-bottom:1px solid var(--line);margin-bottom:24px}
        .tab-btn{background:none;border:0;padding:.8rem 1.2rem;font-weight:600;color:var(--muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px}
        .tab-btn.active{color:var(--primary);border-bottom-color:var(--primary)}
        .tab-panel{display:none}.tab-panel.active{display:block}

        /* team */
        .team-card{text-align:center;text-decoration:none;display:block}
        .team-photo{width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto .9rem;display:block}
        .team-initial{display:grid;place-items:center;background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;font-size:2.4rem;font-weight:700}
        .team-card h3{margin:.2rem 0 .1rem}.team-role{color:var(--muted);font-size:.92rem}

        /* video */
        .video-frame{position:relative;aspect-ratio:16/9;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-lg);background:#000}
        .video-frame iframe,.video-frame video{position:absolute;inset:0;width:100%;height:100%;border:0}
        .video-empty{position:absolute;inset:0;display:grid;place-items:center;font-size:3rem;background:linear-gradient(135deg,var(--primary),var(--secondary))}

        /* banner + buttons */
        .banner-inner{display:flex;align-items:center;justify-content:center;gap:1.2rem;flex-wrap:wrap;font-weight:600}
        .btn-row{display:flex;gap:.8rem;flex-wrap:wrap}
        .align-center .btn-row{justify-content:center}.align-right .btn-row{justify-content:flex-end}

        /* contact */
        .contact-grid{display:grid;grid-template-columns:1fr 1.1fr;gap:44px;align-items:start}
        .contact-list{list-style:none;padding:0;margin:1rem 0 0;display:flex;flex-direction:column;gap:.8rem}
        .contact-list li{display:flex;gap:.7rem;align-items:center;color:#334155}
        .contact-form .field{margin-bottom:1rem}
        .contact-form label{display:block;font-weight:600;font-size:.9rem;margin-bottom:.35rem}
        .contact-form input,.contact-form textarea{width:100%;padding:.7rem .9rem;border:1px solid var(--line);border-radius:12px;font:inherit;background:#fff}
        .contact-form input:focus,.contact-form textarea:focus{outline:none;border-color:var(--primary)}
        .form-ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:.8rem 1rem;border-radius:12px;margin-bottom:1rem}
        .newsletter-form{display:flex;gap:.6rem;max-width:480px;margin:1.4rem auto 0}
        .newsletter-form input{flex:1;padding:.8rem 1rem;border:1px solid var(--line);border-radius:999px;font:inherit}
        @media(max-width:820px){.contact-grid{grid-template-columns:1fr;gap:28px}}
        @media(max-width:520px){.newsletter-form{flex-direction:column}}

        /* map */
        .map-frame{position:relative;aspect-ratio:21/9;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow)}
        .map-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
        .map-empty{position:absolute;inset:0;display:grid;place-items:center;font-size:3rem;background:var(--soft)}

        /* gallery columns */
        .gallery-grid.cols-2{grid-template-columns:repeat(2,1fr)}
        .gallery-grid.cols-4{grid-template-columns:repeat(4,1fr)}
        .gallery-cell{margin:0}.gallery-cell figcaption{color:var(--muted);font-size:.9rem;margin-top:.4rem;text-align:center}
        @media(max-width:700px){.gallery-grid.cols-3,.gallery-grid.cols-4{grid-template-columns:repeat(2,1fr)}}

        /* slider */
        .cms-slider{display:flex;overflow-x:auto;scroll-snap-type:x mandatory}
        .cms-slide{min-width:100%;scroll-snap-align:start;color:#fff;position:relative;background:linear-gradient(135deg,var(--primary),var(--secondary))}
        .cms-slide.has-image{background-image:var(--hero-img);background-size:cover;background-position:center}
        .cms-slide.has-image::before{content:"";position:absolute;inset:0;background:rgba(2,6,23,.4)}
        .cms-slide .wrap{position:relative;padding:96px 22px;text-align:center}
        .cms-slide h2{font-size:clamp(1.8rem,4vw,3rem);color:#fff}.cms-slide p{color:#fff;opacity:.94}
        .cms-slide .actions{margin-top:1.2rem}

        .cms-divider{border:0;border-top:1px solid var(--line)}

        /* columns */
        .col-card{padding:0;overflow:hidden;display:flex;flex-direction:column}
        .col-card-img{width:100%;height:170px;object-fit:cover}
        .col-card-body{padding:26px}
        .col-card-body h3{margin:.2rem 0 .5rem}
        .col-card-body p{color:var(--muted);margin:0 0 .8rem}
    </style>
</head>
<body>
<header class="site">
    <div class="wrap nav">
        <a href="{{ url('/') }}" class="brand">
            @if($settings->logo_path)
                <img src="{{ \Modules\Builder\Services\BuilderService::media($settings->logo_path) }}" alt="{{ $settings->site_name }}">
            @else
                <span class="logo">{{ Str::substr($settings->site_name, 0, 1) }}</span>
            @endif
            <span>{{ $settings->site_name }}</span>
        </a>
        <button class="burger" onclick="document.getElementById('sitemenu').classList.toggle('open')" aria-label="Menu">☰</button>
        <ul class="menu" id="sitemenu">
            @forelse($headerMenu as $item)
                <li><a href="{{ $item->href() }}" @if($item->open_new_tab) target="_blank" @endif>{{ $item->label }}</a></li>
            @empty
                <li><a href="{{ url('/') }}">Home</a></li>
            @endforelse
            <li><a class="btn btn-primary cta-login" href="{{ route('login') }}">Login</a></li>
        </ul>
    </div>
</header>

<main>
    @yield('body')
</main>

<footer class="site">
    <div class="wrap">
        <div class="cols">
            <div>
                <div class="brand" style="font-size:1.3rem;font-weight:800">{{ $settings->site_name }}</div>
                <p style="max-width:340px">{{ $settings->footer_text ?: $settings->tagline }}</p>
                <div class="social">
                    @if($settings->facebook)<a href="{{ $settings->facebook }}" target="_blank" title="Facebook">f</a>@endif
                    @if($settings->twitter)<a href="{{ $settings->twitter }}" target="_blank" title="Twitter">𝕏</a>@endif
                    @if($settings->youtube)<a href="{{ $settings->youtube }}" target="_blank" title="YouTube">▶</a>@endif
                    @if($settings->linkedin)<a href="{{ $settings->linkedin }}" target="_blank" title="LinkedIn">in</a>@endif
                </div>
            </div>
            <div>
                <h4>Links</h4>
                @forelse($footerMenu as $item)
                    <a href="{{ $item->href() }}" @if($item->open_new_tab) target="_blank" @endif>{{ $item->label }}</a>
                @empty
                    <a href="{{ url('/') }}">Home</a>
                @endforelse
            </div>
            <div>
                <h4>Contact</h4>
                @if($settings->address)<a href="#">{{ $settings->address }}</a>@endif
                @if($settings->phone)<a href="tel:{{ $settings->phone }}">{{ $settings->phone }}</a>@endif
                @if($settings->email)<a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>@endif
            </div>
        </div>
        <div class="copy">© {{ date('Y') }} {{ $settings->site_name }}. All rights reserved.</div>
    </div>
</footer>
<script>
    // Tabs sections
    document.querySelectorAll('[data-tabs]').forEach(function (ts) {
        var btns = ts.querySelectorAll('.tab-btn'), panels = ts.querySelectorAll('.tab-panel');
        btns.forEach(function (b) {
            b.addEventListener('click', function () {
                var i = b.getAttribute('data-tab');
                btns.forEach(function (x) { x.classList.remove('active'); });
                panels.forEach(function (p) { p.classList.toggle('active', p.getAttribute('data-panel') === i); });
                b.classList.add('active');
            });
        });
    });
</script>
</body>
</html>
