@php($__rtl = data_get(config('locales.available'), app()->getLocale().'.rtl', false))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $__rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <style>
        :root{
            --bg:#f4f6fb; --surface:#fff; --surface-2:#f8fafc; --border:#e2e8f0;
            --text:#1e293b; --muted:#64748b; --primary:#4f46e5; --primary-2:#4338ca;
            --sidebar:#111827; --sidebar-text:#cbd5e1; --sidebar-active:#4f46e5;
            --danger:#dc2626; --success:#16a34a;
            --primary-soft:rgba(79,70,229,.10); --success-soft:rgba(22,163,74,.12);
            --danger-soft:rgba(220,38,38,.12); --warning:#b45309; --warning-soft:rgba(180,83,9,.12);
            --shadow-sm:0 1px 2px rgba(15,23,42,.06); --shadow-md:0 8px 24px -8px rgba(15,23,42,.18);
            --shadow-lg:0 20px 45px -12px rgba(15,23,42,.30);
        }
        @media (prefers-color-scheme: dark){
            :root{ --bg:#0b1120; --surface:#111827; --surface-2:#0f172a; --border:#1f2937;
                   --text:#e2e8f0; --muted:#94a3b8; --sidebar:#0b1120;
                   --primary-soft:rgba(99,102,241,.18); --success-soft:rgba(34,197,94,.16);
                   --danger-soft:rgba(248,113,113,.16); --warning:#f0b429; --warning-soft:rgba(240,180,41,.16);
                   --shadow-sm:0 1px 2px rgba(0,0,0,.3); --shadow-md:0 8px 24px -8px rgba(0,0,0,.5);
                   --shadow-lg:0 20px 45px -12px rgba(0,0,0,.6); }
        }
        :root[data-theme="dark"]{
            --bg:#0b1120; --surface:#111827; --surface-2:#0f172a; --border:#1f2937;
            --text:#e2e8f0; --muted:#94a3b8; --sidebar:#0b1120;
            --primary-soft:rgba(99,102,241,.18); --success-soft:rgba(34,197,94,.16);
            --danger-soft:rgba(248,113,113,.16); --warning:#f0b429; --warning-soft:rgba(240,180,41,.16);
            --shadow-sm:0 1px 2px rgba(0,0,0,.3); --shadow-md:0 8px 24px -8px rgba(0,0,0,.5);
            --shadow-lg:0 20px 45px -12px rgba(0,0,0,.6);
        }
        :root[data-theme="light"]{
            --bg:#f4f6fb; --surface:#fff; --surface-2:#f8fafc; --border:#e2e8f0;
            --text:#1e293b; --muted:#64748b; --sidebar:#111827;
            --primary-soft:rgba(79,70,229,.10); --success-soft:rgba(22,163,74,.12);
            --danger-soft:rgba(220,38,38,.12); --warning:#b45309; --warning-soft:rgba(180,83,9,.12);
            --shadow-sm:0 1px 2px rgba(15,23,42,.06); --shadow-md:0 8px 24px -8px rgba(15,23,42,.18);
            --shadow-lg:0 20px 45px -12px rgba(15,23,42,.30);
        }
        *{ box-sizing:border-box; }
        body{ margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
              background:var(--bg); color:var(--text); display:flex; min-height:100vh; }
        a{ color:inherit; text-decoration:none; }

        /* Sidebar */
        .sidebar{ width:250px; background:var(--sidebar); color:var(--sidebar-text);
                  flex-shrink:0; padding:1.25rem 0; position:sticky; top:0; height:100vh; overflow-y:auto; }
        .brand{ font-size:1.15rem; font-weight:700; color:#fff; padding:0 1.25rem 1.25rem; }
        .brand small{ display:block; font-weight:400; font-size:.7rem; color:var(--muted); }
        .nav a{ display:flex; align-items:center; gap:.6rem; padding:.5rem 1.25rem;
                font-size:.88rem; color:var(--sidebar-text); border-left:3px solid transparent;
                border-radius:0 8px 8px 0; margin-inline-end:.5rem; transition:background .12s,color .12s; }
        .nav a:hover{ background:rgba(255,255,255,.06); color:#fff; }
        .nav a.active{ background:rgba(79,70,229,.18); color:#fff; border-left-color:var(--sidebar-active); font-weight:600; }
        [dir="rtl"] .nav a{ border-left:0; border-right:3px solid transparent; border-radius:8px 0 0 8px; }
        [dir="rtl"] .nav a.active{ border-right-color:var(--sidebar-active); }

        /* Top-level plain link (Dashboard — not grouped, matches the single-item convention) */
        .nav-toplink{ display:flex; align-items:center; gap:.55rem; margin:.15rem .6rem .4rem; padding:.55rem .65rem;
                      border-radius:8px; color:#cbd5e1; font-size:.86rem; font-weight:600; }
        .nav-toplink:hover{ background:rgba(255,255,255,.06); color:#fff; }
        .nav-toplink.active{ background:rgba(79,70,229,.18); color:#fff; }

        /* Accordion nav groups (2 levels: umbrella section > sub-group) */
        .acc-group{ margin:.15rem .6rem; }
        .acc-header{ width:100%; display:flex; align-items:center; justify-content:space-between; gap:.5rem;
                     background:none; border:0; cursor:pointer; text-align:start; color:#94a3b8;
                     padding:.5rem .65rem; border-radius:8px; font-family:inherit;
                     font-size:.68rem; text-transform:uppercase; letter-spacing:.08em; font-weight:600; }
        .acc-header:hover{ background:rgba(255,255,255,.06); color:#fff; }
        .acc-group.is-active > .acc-header{ color:#e2e8f0; background:rgba(79,70,229,.16); }
        .acc-title{ display:flex; align-items:center; gap:.55rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .acc-icon{ flex-shrink:0; font-size:.85rem; opacity:.9; width:1.1em; text-align:center; }
        .acc-caret{ flex-shrink:0; font-size:.7rem; line-height:1; transition:transform .2s ease; }
        [dir="rtl"] .acc-caret{ transform:rotate(180deg); }
        .acc-group.open > .acc-header .acc-caret{ transform:rotate(90deg); }
        .acc-panel{ max-height:0; overflow:hidden; opacity:0;
                    transition:max-height .28s ease, opacity .2s ease; }
        .acc-group.open > .acc-panel{ max-height:2400px; opacity:1; }

        /* Nested sub-group (level 2) — indented, quieter typography */
        .acc-group.sub{ margin:.05rem 0; }
        .acc-header.sub{ padding-inline-start:1.65rem; font-size:.76rem; text-transform:none;
                         letter-spacing:.01em; font-weight:600; }
        .acc-header.sub .acc-caret{ font-size:.62rem; }
        .acc-group.sub.is-active > .acc-header.sub{ color:#e2e8f0; background:rgba(79,70,229,.12); }
        .acc-group.sub .nav a{ padding-inline-start:2.35rem; }

        /* Main */
        .main{ flex:1; display:flex; flex-direction:column; min-width:0; }
        .topbar{ background:var(--surface); border-bottom:1px solid var(--border);
                 padding:.85rem 1.5rem; display:flex; align-items:center; justify-content:space-between; }
        .topbar .title{ font-size:1.1rem; font-weight:600; }
        .content{ padding:1.5rem; flex:1; }
        .userbox{ display:flex; align-items:center; gap:.75rem; font-size:.85rem; color:var(--muted); }
        .btn-logout{ background:none; border:1px solid var(--border); color:var(--muted);
                     padding:.35rem .7rem; border-radius:7px; cursor:pointer; font-size:.8rem; }

        /* Components */
        .card{ background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:1.25rem;
               box-shadow:var(--shadow-sm); }
        .card + .card{ margin-top:1rem; }
        .page-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; flex-wrap:wrap; gap:.6rem; }
        .page-head h1{ font-size:1.35rem; margin:0; letter-spacing:-.01em; }
        table{ width:100%; border-collapse:collapse; font-size:.9rem; }
        th,td{ text-align:left; padding:.7rem .6rem; border-bottom:1px solid var(--border); }
        th{ font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); font-weight:700;
            background:var(--surface-2); }
        tr:last-child td{ border-bottom:0; }
        tbody tr{ transition:background .12s; }
        tbody tr:hover{ background:var(--surface-2); }
        .btn{ display:inline-flex; align-items:center; gap:.4rem; background:var(--primary); color:#fff; border:0; border-radius:9px;
              padding:.55rem 1rem; font-size:.88rem; font-weight:600; cursor:pointer;
              box-shadow:var(--shadow-sm); transition:background .15s,box-shadow .15s,transform .1s; }
        .btn:hover{ background:var(--primary-2); box-shadow:var(--shadow-md); }
        .btn:active{ transform:translateY(1px); }
        .btn-sm{ padding:.35rem .7rem; font-size:.8rem; }
        .btn-ghost{ background:transparent; border:1px solid var(--border); color:var(--text); box-shadow:none; }
        .btn-ghost:hover{ border-color:var(--primary); color:var(--primary); background:var(--primary-soft); box-shadow:none; }
        .btn-danger{ background:var(--danger); }
        .btn-danger:hover{ background:#b91c1c; }
        label{ display:block; font-size:.82rem; color:var(--muted); margin:.9rem 0 .35rem; }
        input,select,textarea{ width:100%; padding:.55rem .7rem; border:1px solid var(--border);
              border-radius:9px; background:var(--surface-2); color:var(--text); font-size:.9rem;
              transition:border-color .12s,box-shadow .12s; }
        input:focus,select:focus,textarea:focus{ outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-soft); }
        .grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.75rem 1rem; }
        .actions{ display:flex; gap:.5rem; }
        .alert{ padding:.7rem 1rem; border-radius:9px; margin-bottom:1rem; font-size:.88rem; }
        .alert-success{ background:var(--success-soft); color:var(--success); border:1px solid var(--success-soft); }
        .alert-danger{ background:var(--danger-soft); color:var(--danger); border:1px solid var(--danger-soft); }
        .badge{ font-size:.7rem; padding:.25rem .6rem; border-radius:20px; background:var(--surface-2); border:1px solid var(--border);
                display:inline-flex; align-items:center; gap:.35rem; font-weight:600; }
        .empty{ text-align:center; color:var(--muted); padding:2rem; }
        .stat-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; }
        .stat{ background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:1.1rem 1.25rem;
               box-shadow:var(--shadow-sm); transition:box-shadow .15s,transform .12s; }
        .stat:hover{ box-shadow:var(--shadow-md); transform:translateY(-1px); }
        .stat .n{ font-size:1.8rem; font-weight:800; letter-spacing:-.01em; font-variant-numeric:tabular-nums; }
        .stat .l{ font-size:.8rem; color:var(--muted); }

        /* Theme toggle */
        .theme-toggle{ background:none; border:1px solid var(--border); color:var(--text); border-radius:8px;
                       width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;
                       cursor:pointer; font-size:1rem; flex-shrink:0; transition:border-color .12s,color .12s; }
        .theme-toggle:hover{ border-color:var(--primary); color:var(--primary); }

        /* Toast notifications */
        .toast-stack{ position:fixed; top:18px; inset-inline-end:18px; z-index:999; display:flex;
                      flex-direction:column; gap:10px; width:min(360px,90vw); }
        .toast{ display:flex; gap:11px; align-items:flex-start; background:var(--surface); border:1px solid var(--border);
                border-radius:12px; padding:13px 14px; box-shadow:var(--shadow-lg);
                transform:translateX(120%); opacity:0; transition:transform .35s cubic-bezier(.32,.9,.35,1.15),opacity .3s ease; }
        [dir="rtl"] .toast{ transform:translateX(-120%); }
        .toast.show{ transform:translateX(0); opacity:1; }
        .toast-icon{ width:26px; height:26px; border-radius:50%; flex-shrink:0; display:flex; align-items:center;
                     justify-content:center; font-size:.8rem; font-weight:800; }
        .toast-success .toast-icon{ background:var(--success-soft); color:var(--success); }
        .toast-error .toast-icon{ background:var(--danger-soft); color:var(--danger); }
        .toast-body{ flex:1; min-width:0; }
        .toast-title{ font-size:.84rem; font-weight:700; margin-bottom:1px; color:var(--text); }
        .toast-msg{ font-size:.79rem; color:var(--muted); line-height:1.4; }
        .toast-close{ background:none; border:0; color:var(--muted); cursor:pointer; font-size:1.1rem; line-height:1;
                      padding:2px; flex-shrink:0; }
        .toast-close:hover{ color:var(--text); }
        .toast-bar{ height:2.5px; border-radius:2px; background:var(--border); margin-top:8px; overflow:hidden; }
        .toast-bar i{ display:block; height:100%; width:100%; transform-origin:left; animation:esToastShrink 5s linear forwards; }
        .toast-success .toast-bar i{ background:var(--success); }
        .toast-error .toast-bar i{ background:var(--danger); }
        @keyframes esToastShrink{ from{ transform:scaleX(1); } to{ transform:scaleX(0); } }
        @media (prefers-reduced-motion: reduce){ .toast{ transition:none; } .toast-bar i{ animation:none; } }
        @media (max-width:480px){ .toast-stack{ inset-inline:12px; top:12px; width:auto; } }

        /* Mobile menu controls (hidden on desktop) */
        .hamburger{ display:none; background:none; border:0; color:var(--text); font-size:1.5rem;
                    line-height:1; cursor:pointer; padding:.25rem .5rem; margin-inline-end:.5rem; }
        .sidebar-overlay{ display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:40; }
        .topbar-left{ display:flex; align-items:center; min-width:0; }

        /* ===== Responsive ===== */
        @media (max-width: 900px){
            .sidebar{ position:fixed; inset-block:0; inset-inline-start:0; height:100vh; z-index:50;
                      transform:translateX(-100%); transition:transform .25s ease; }
            [dir="rtl"] .sidebar{ transform:translateX(100%); }
            body.nav-open .sidebar{ transform:none; }
            body.nav-open .sidebar-overlay{ display:block; }
            .hamburger{ display:inline-block; }
            .content{ padding:1rem; }
            .topbar{ padding:.6rem .8rem; gap:.5rem; }
            .topbar .title{ font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
            .userbox{ gap:.5rem; }
            .userbox span, .userbox-name{ display:none; }    /* hide username to free room */
            .userbox select{ max-width:5.5rem; }             /* compact language picker */
            .btn-logout{ white-space:nowrap; }               /* never wrap to two lines */
            .card{ overflow-x:auto; -webkit-overflow-scrolling:touch; }   /* wide tables scroll (with momentum) inside the card */
            .page-head{ flex-wrap:wrap; gap:.6rem; }
            .page-head h1{ font-size:1.2rem; }
            table{ min-width:520px; }                     /* scroll rather than squish columns */
            th,td{ padding:.55rem .5rem; }                /* a touch tighter to fit more per screen */
            /* iOS Safari auto-zooms when a focused input's font is under 16px and
               then leaves the page awkwardly enlarged — keep form fields at 16px. */
            input,select,textarea{ font-size:16px; }
            .btn{ min-height:40px; }                      /* comfortable tap target */
            .acc-header, .nav a, .nav-toplink{ padding-top:.6rem; padding-bottom:.6rem; }  /* bigger touch rows */
        }
        @media (max-width: 480px){
            .stat .n{ font-size:1.5rem; }
            .btn{ padding:.5rem .8rem; }
            .stat-grid{ grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); }  /* two-up stat tiles on phones */
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    {{-- Tailwind utilities, loaded AFTER the inline design system above so the
         mobile-responsive utility classes win same-specificity conflicts. --}}
    @vite(['resources/css/app.css'])
</head>
<body>
    {{--
        Sidebar structure (which routes/links exist under which sub-group,
        and which sub-groups belong to which of the 10 umbrella sections) now
        lives in App\Support\SidebarNav — a plain PHP service, not inline here
        — so the Sidebar Manager (Settings → Sidebar Manager) can read the
        same structure to offer reordering. This view only asks for the
        DISPLAY order (custom if set, else the built-in default) and renders
        it; it never invents or alters any route.
    --}}
    @php($sidebarNav = app(\App\Support\SidebarNav::class))
    @php($nav = $sidebarNav->groups())
    @php($navSections = $sidebarNav->orderedSections())
    <aside class="sidebar">
        <div class="brand">EasySchool ERP<small>{{ optional(\Modules\Foundation\Models\AcademicYear::current())->title }}</small></div>
        <a href="{{ route('dashboard') }}" class="nav-toplink {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="acc-icon fa-solid fa-house" aria-hidden="true"></i>{{ __('ui.dashboard') }}
        </a>
        @foreach($navSections as $section)
            @continue(empty($section['groups']))
            @continue($sidebarNav->isHiddenForCurrentUser($section['key']))
            @php($__subgroups = $sidebarNav->renderableSubgroups($section))
            @continue($__subgroups->isEmpty())
            @php($__sectionActive = $__subgroups->contains(fn ($g) => collect($g['links'])->contains(fn ($l) => !empty($l[3] ?? false))))
            <div class="acc-group {{ $__sectionActive ? 'is-active open' : '' }}" data-group="{{ $section['label'] }}">
                <button type="button" class="acc-header" aria-expanded="{{ $__sectionActive ? 'true' : 'false' }}">
                    <span class="acc-title">@if(str_starts_with($section['icon'], 'fa-'))<i class="acc-icon {{ $section['icon'] }}" aria-hidden="true"></i>@else<span class="acc-icon" aria-hidden="true">{{ $section['icon'] }}</span>@endif{{ $section['label'] }}</span>
                    <i class="acc-caret fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
                <div class="acc-panel">
                    @if($__subgroups->count() > 1)
                        @foreach($__subgroups as $sub)
                            @php($__subActive = collect($sub['links'])->contains(fn ($l) => !empty($l[3] ?? false)))
                            <div class="acc-group sub {{ $__subActive ? 'is-active open' : '' }}" data-group="{{ $section['label'] }}::{{ $sub['key'] }}">
                                <button type="button" class="acc-header sub" aria-expanded="{{ $__subActive ? 'true' : 'false' }}">
                                    <span class="acc-title">{{ $sub['label'] }}</span>
                                    <i class="acc-caret fa-solid fa-chevron-right" aria-hidden="true"></i>
                                </button>
                                <nav class="nav acc-panel">
                                    @foreach($sub['links'] as [$__slug, $label, $url, $active])
                                        <a href="{{ $url }}" class="{{ $active ? 'active' : '' }}">{{ $label }}</a>
                                    @endforeach
                                </nav>
                            </div>
                        @endforeach
                    @else
                        <nav class="nav">
                            @foreach($__subgroups[0]['links'] as [$__slug, $label, $url, $active])
                                <a href="{{ $url }}" class="{{ $active ? 'active' : '' }}">{{ $label }}</a>
                            @endforeach
                        </nav>
                    @endif
                </div>
            </div>
        @endforeach
    </aside>
    <div class="sidebar-overlay" onclick="document.body.classList.remove('nav-open')"></div>

    <div class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger" aria-label="Menu" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
                <div class="title">@yield('title', 'Dashboard')</div>
            </div>
            <div class="userbox">
                <button type="button" class="theme-toggle" id="themeToggle" aria-label="{{ __('ui.toggle_theme') }}" title="{{ __('ui.toggle_theme') }}"><i class="fa-solid fa-moon" aria-hidden="true"></i></button>
                <select onchange="location.href='{{ url('locale') }}/'+this.value" title="{{ __('ui.language') }}"
                        style="width:auto;padding:.3rem .5rem;font-size:.8rem">
                    @foreach(config('locales.available') as $code => $lang)
                        <option value="{{ $code }}" {{ app()->getLocale() === $code ? 'selected' : '' }}>{{ $lang['native'] }}</option>
                    @endforeach
                </select>
                @if(\Modules\HumanResource\Models\Staff::where('user_id', auth()->id())->exists())
                    <a href="{{ route('staffportal.dashboard') }}" title="{{ __('ui.staff_portal') }}" class="btn-ghost btn-sm" style="padding:.35rem .7rem;border:1px solid var(--border);border-radius:7px">{{ __('ui.staff_portal') }}</a>
                @endif
                <a href="{{ route('profile.edit') }}" title="{{ __('ui.my_profile') }}" class="userbox-name">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn-logout">{{ __('ui.log_out') }}</button>
                </form>
            </div>
        </header>
        <main class="content">
            @include('partials.toast-stack')
            @yield('content')
        </main>
    </div>

    <script>
        // Close the mobile drawer after tapping a nav link.
        document.querySelectorAll('.sidebar .nav a, .sidebar .nav-toplink').forEach(function (a) {
            a.addEventListener('click', function () { document.body.classList.remove('nav-open'); });
        });

        // Accordion nav groups — exactly one open at a time PER LEVEL (top-level
        // umbrellas among themselves, and each umbrella's own nested sub-groups
        // among themselves). Opening one always closes its siblings. The group
        // that contains the current page (server-rendered `is-active`) always
        // wins as the open one on page load, regardless of any stored
        // preference from a previous visit — that's what keeps the menu
        // "stuck" on wherever you actually are.
        (function () {
            var LS_PREFIX = 'esnav:';

            function directHeader(group) {
                for (var i = 0; i < group.children.length; i++) {
                    if (group.children[i].classList.contains('acc-header')) return group.children[i];
                }
                return null;
            }

            function siblingGroups(container) {
                return Array.prototype.filter.call(container.children, function (el) {
                    return el.classList && el.classList.contains('acc-group');
                });
            }

            function setOpen(group, open) {
                group.classList.toggle('open', open);
                var header = directHeader(group);
                if (header) header.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function setupContainer(container, storageKey) {
                var groups = siblingGroups(container);
                if (!groups.length) return;

                var activeGroup = null;
                for (var i = 0; i < groups.length; i++) {
                    if (groups[i].classList.contains('is-active')) { activeGroup = groups[i]; break; }
                }

                var storedLabel = null;
                try { storedLabel = localStorage.getItem(storageKey); } catch (e) {}

                var toOpen = activeGroup;
                if (!toOpen && storedLabel) {
                    for (var j = 0; j < groups.length; j++) {
                        if (groups[j].getAttribute('data-group') === storedLabel) { toOpen = groups[j]; break; }
                    }
                }

                groups.forEach(function (g) { setOpen(g, g === toOpen); });

                groups.forEach(function (g) {
                    var header = directHeader(g);
                    if (!header) return;
                    header.addEventListener('click', function () {
                        var willOpen = !g.classList.contains('open');
                        groups.forEach(function (sib) { setOpen(sib, willOpen && sib === g); });
                        try { localStorage.setItem(storageKey, willOpen ? (g.getAttribute('data-group') || '') : ''); } catch (e) {}
                    });
                });
            }

            var sidebar = document.querySelector('.sidebar');
            if (sidebar) setupContainer(sidebar, LS_PREFIX + 'top');

            document.querySelectorAll('.sidebar > .acc-group').forEach(function (section) {
                var panel = section.querySelector(':scope > .acc-panel');
                if (panel && panel.querySelector(':scope > .acc-group.sub')) {
                    setupContainer(panel, LS_PREFIX + 'sub:' + (section.getAttribute('data-group') || ''));
                }
            });
        })();

        // Light/dark theme toggle, persisted per-browser.
        (function () {
            var root = document.documentElement;
            var btn = document.getElementById('themeToggle');
            if (!btn) return;
            var KEY = 'es-theme';
            var saved = null;
            try { saved = localStorage.getItem(KEY); } catch (e) {}
            if (saved === 'dark' || saved === 'light') { root.setAttribute('data-theme', saved); }

            function sync() {
                var isDark = root.getAttribute('data-theme') === 'dark'
                    || (!root.hasAttribute('data-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
                btn.innerHTML = isDark
                    ? '<i class="fa-solid fa-sun" aria-hidden="true"></i>'
                    : '<i class="fa-solid fa-moon" aria-hidden="true"></i>';
            }
            sync();

            btn.addEventListener('click', function () {
                var current = root.getAttribute('data-theme')
                    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                var next = current === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                try { localStorage.setItem(KEY, next); } catch (e) {}
                sync();
            });
        })();
    </script>
</body>
</html>
