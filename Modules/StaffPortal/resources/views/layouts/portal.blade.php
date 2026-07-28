@php($__rtl = data_get(config('locales.available'), app()->getLocale().'.rtl', false))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $__rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('ui.staff_portal')) — {{ config('app.name') }}</title>
    <style>
        :root{
            --bg:#f4f6fb; --surface:#fff; --surface-2:#f8fafc; --border:#e2e8f0;
            --text:#1e293b; --muted:#64748b; --primary:#4f46e5; --primary-2:#4338ca;
            --success:#16a34a; --danger:#dc2626; --radius:16px;
            --primary-soft:rgba(79,70,229,.10); --success-soft:rgba(22,163,74,.12); --danger-soft:rgba(220,38,38,.12);
            --shadow-sm:0 1px 2px rgba(15,23,42,.06); --shadow-md:0 8px 24px -8px rgba(15,23,42,.18);
            --shadow-lg:0 20px 45px -12px rgba(15,23,42,.30);
        }
        @media (prefers-color-scheme: dark){
            :root{ --bg:#0b1120; --surface:#111827; --surface-2:#0f172a; --border:#1f2937; --text:#e2e8f0; --muted:#94a3b8;
                   --primary-soft:rgba(99,102,241,.18); --success-soft:rgba(34,197,94,.16); --danger-soft:rgba(248,113,113,.16);
                   --shadow-sm:0 1px 2px rgba(0,0,0,.3); --shadow-md:0 8px 24px -8px rgba(0,0,0,.5); --shadow-lg:0 20px 45px -12px rgba(0,0,0,.6); }
        }
        :root[data-theme="dark"]{
            --bg:#0b1120; --surface:#111827; --surface-2:#0f172a; --border:#1f2937; --text:#e2e8f0; --muted:#94a3b8;
            --primary-soft:rgba(99,102,241,.18); --success-soft:rgba(34,197,94,.16); --danger-soft:rgba(248,113,113,.16);
            --shadow-sm:0 1px 2px rgba(0,0,0,.3); --shadow-md:0 8px 24px -8px rgba(0,0,0,.5); --shadow-lg:0 20px 45px -12px rgba(0,0,0,.6);
        }
        :root[data-theme="light"]{
            --bg:#f4f6fb; --surface:#fff; --surface-2:#f8fafc; --border:#e2e8f0; --text:#1e293b; --muted:#64748b;
            --primary-soft:rgba(79,70,229,.10); --success-soft:rgba(22,163,74,.12); --danger-soft:rgba(220,38,38,.12);
            --shadow-sm:0 1px 2px rgba(15,23,42,.06); --shadow-md:0 8px 24px -8px rgba(15,23,42,.18); --shadow-lg:0 20px 45px -12px rgba(15,23,42,.30);
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:inherit;text-decoration:none}
        .wrap{max-width:960px;margin:0 auto;padding:0 20px}

        header.portal{background:var(--surface);border-bottom:1px solid var(--border);padding:1rem 0}
        header.portal .row{display:flex;align-items:center;justify-content:space-between;gap:1rem}
        .brand{font-weight:800;font-size:1.15rem;letter-spacing:-.02em}
        .brand small{display:block;font-weight:500;font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em}
        .who{display:flex;align-items:center;gap:.8rem;font-size:.88rem;color:var(--muted)}
        .btn-logout{background:none;border:1px solid var(--border);color:var(--muted);padding:.4rem .85rem;border-radius:8px;cursor:pointer;font-size:.82rem}
        .btn-logout:hover{border-color:var(--danger);color:var(--danger)}
        .theme-toggle{background:none;border:1px solid var(--border);color:var(--text);border-radius:8px;width:32px;height:32px;
                      display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:.95rem;flex-shrink:0;
                      transition:border-color .12s,color .12s}
        .theme-toggle:hover{border-color:var(--primary);color:var(--primary)}

        nav.portal-tabs{background:var(--surface);border-bottom:1px solid var(--border)}
        nav.portal-tabs .row{display:flex;gap:.3rem;flex-wrap:wrap}
        nav.portal-tabs a{padding:.85rem 1.1rem;font-size:.9rem;font-weight:600;color:var(--muted);border-bottom:2px solid transparent;transition:color .12s,border-color .12s}
        nav.portal-tabs a:hover{color:var(--text)}
        nav.portal-tabs a.active{color:var(--primary);border-bottom-color:var(--primary)}

        .content{padding:2rem 0 3rem}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.4rem;margin-bottom:1.2rem;box-shadow:var(--shadow-sm)}
        .page-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;margin-bottom:1.2rem}
        .page-head h1{font-size:1.35rem;margin:0;letter-spacing:-.01em}
        table{width:100%;border-collapse:collapse;font-size:.9rem}
        th,td{text-align:start;padding:.65rem .5rem;border-bottom:1px solid var(--border)}
        th{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);font-weight:700}
        tr:last-child td{border-bottom:0}
        tbody tr{transition:background .12s}
        tbody tr:hover{background:var(--surface-2)}
        .btn{display:inline-flex;align-items:center;gap:.4rem;background:var(--primary);color:#fff;border:0;border-radius:10px;padding:.6rem 1.1rem;font-size:.88rem;font-weight:600;cursor:pointer;box-shadow:var(--shadow-sm);transition:background .15s,box-shadow .15s,transform .1s}
        .btn:hover{background:var(--primary-2);box-shadow:var(--shadow-md)}
        .btn:active{transform:translateY(1px)}
        .btn-ghost{background:transparent;border:1px solid var(--border);color:var(--text);box-shadow:none}
        .btn-ghost:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-soft)}
        .badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;padding:.25rem .65rem;border-radius:999px;font-weight:700}
        .badge-due{background:var(--danger-soft);color:#92400e}
        .badge-paid{background:var(--success-soft);color:#166534}
        .badge-partial{background:var(--primary-soft);color:#1e40af}
        .empty{text-align:center;color:var(--muted);padding:2.5rem 1rem}
        .alert{padding:.8rem 1rem;border-radius:10px;margin-bottom:1.1rem;font-size:.88rem}
        .alert-success{background:var(--success-soft);color:#166534;border:1px solid var(--success-soft)}
        .alert-danger{background:var(--danger-soft);color:#991b1b;border:1px solid var(--danger-soft)}
        .child-card{display:flex;align-items:center;gap:1rem}
        .child-photo{width:56px;height:56px;border-radius:50%;object-fit:cover;background:linear-gradient(135deg,var(--primary),#818cf8);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.2rem;flex-shrink:0}

        /* Toast notifications (shared visual language with the admin panel) */
        .toast-stack{position:fixed;top:18px;inset-inline-end:18px;z-index:999;display:flex;flex-direction:column;gap:10px;width:min(360px,90vw)}
        .toast{display:flex;gap:11px;align-items:flex-start;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:13px 14px;box-shadow:var(--shadow-lg);transform:translateX(120%);opacity:0;transition:transform .35s cubic-bezier(.32,.9,.35,1.15),opacity .3s ease}
        [dir="rtl"] .toast{transform:translateX(-120%)}
        .toast.show{transform:translateX(0);opacity:1}
        .toast-icon{width:26px;height:26px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800}
        .toast-success .toast-icon{background:var(--success-soft);color:var(--success)}
        .toast-error .toast-icon{background:var(--danger-soft);color:var(--danger)}
        .toast-body{flex:1;min-width:0}
        .toast-title{font-size:.84rem;font-weight:700;margin-bottom:1px;color:var(--text)}
        .toast-msg{font-size:.79rem;color:var(--muted);line-height:1.4}
        .toast-close{background:none;border:0;color:var(--muted);cursor:pointer;font-size:1.1rem;line-height:1;padding:2px;flex-shrink:0}
        .toast-close:hover{color:var(--text)}
        .toast-bar{height:2.5px;border-radius:2px;background:var(--border);margin-top:8px;overflow:hidden}
        .toast-bar i{display:block;height:100%;width:100%;transform-origin:left;animation:esToastShrink 5s linear forwards}
        .toast-success .toast-bar i{background:var(--success)}
        .toast-error .toast-bar i{background:var(--danger)}
        @keyframes esToastShrink{from{transform:scaleX(1)}to{transform:scaleX(0)}}
        @media (prefers-reduced-motion: reduce){.toast{transition:none}.toast-bar i{animation:none}}
        @media (max-width:480px){.toast-stack{inset-inline:12px;top:12px;width:auto}}
        /* ===== Mobile ===== */
        @media (max-width:640px){
            .wrap{padding:0 14px}
            header.portal .row{gap:.5rem}
            .who{gap:.5rem;font-size:.82rem}
            .who .who-name{display:none}                 /* free room in the header */
            .btn-logout{white-space:nowrap}
            nav.portal-tabs .row{flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch}  /* swipeable tab strip */
            nav.portal-tabs a{white-space:nowrap;padding:.75rem .85rem}
            .content{padding:1.25rem 0 2rem}
            .card{padding:1.1rem;overflow-x:auto;-webkit-overflow-scrolling:touch}
            .page-head h1{font-size:1.2rem}
            table{min-width:520px}
            th,td{padding:.55rem .5rem}
            input,select,textarea{font-size:16px}        /* stop iOS focus-zoom */
            .btn{min-height:40px}
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    @vite(['resources/css/app.css'])
    @yield('head')
</head>
<body>
<header class="portal">
    <div class="wrap row">
        <a href="{{ route('staffportal.dashboard') }}" class="brand">EasySchool ERP<small>{{ __('ui.staff_portal') }}</small></a>
        <div class="who">
            <button type="button" class="theme-toggle" id="themeToggle" aria-label="{{ __('ui.toggle_theme') }}" title="{{ __('ui.toggle_theme') }}"><i class="fa-solid fa-moon" aria-hidden="true"></i></button>
            <a href="{{ route('profile.edit') }}" title="{{ __('ui.my_profile') }}" class="who-name">{{ auth()->user()?->name }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="btn-logout">{{ __('ui.log_out') }}</button>
            </form>
        </div>
    </div>
</header>
<nav class="portal-tabs">
    <div class="wrap row">
        <a href="{{ route('staffportal.dashboard') }}" class="{{ request()->routeIs('staffportal.dashboard') ? 'active' : '' }}">{{ __('ui.dashboard') }}</a>
        <a href="{{ route('staffportal.payslips.index') }}" class="{{ request()->routeIs('staffportal.payslips.*') ? 'active' : '' }}">{{ __('ui.my_payslips') }}</a>
        <a href="{{ route('staffportal.attendance.index') }}" class="{{ request()->routeIs('staffportal.attendance.*') ? 'active' : '' }}">{{ __('ui.my_attendance') }}</a>
        <a href="{{ route('staffportal.resignation.index') }}" class="{{ request()->routeIs('staffportal.resignation.*') ? 'active' : '' }}">{{ __('ui.resignation') }}</a>
        <a href="{{ route('staffportal.advances.index') }}" class="{{ request()->routeIs('staffportal.advances.*') ? 'active' : '' }}">{{ __('ui.salary_advance') }}</a>
    </div>
</nav>
<main class="content">
    <div class="wrap">
        @include('partials.toast-stack')
        @yield('content')
    </div>
</main>
<script>
    // Light/dark theme toggle, persisted per-browser (shared behaviour with the admin panel).
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
