<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EasySchool ERP') — {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
               background: #0f172a; color: #e2e8f0; display: grid; min-height: 100vh; place-items: center; padding: 1rem; }
        .auth-card, .panel { background: #1e293b; padding: 2rem; border-radius: 14px; width: 100%; max-width: 380px;
               box-shadow: 0 10px 40px rgba(0,0,0,.4); }
        h1 { margin: 0 0 .25rem; font-size: 1.5rem; }
        .muted { color: #94a3b8; margin: 0 0 1.5rem; font-size: .9rem; }
        label { display: block; margin: 1rem 0 .35rem; font-size: .85rem; color: #cbd5e1; }
        label.check { display: flex; align-items: center; gap: .5rem; font-size: .85rem; }
        input[type=text], input[type=password], input:not([type]) {
               width: 100%; padding: .6rem .75rem; border-radius: 8px; border: 1px solid #334155; font-size: 16px;
               background: #0f172a; color: #e2e8f0; }
        button { margin-top: 1.5rem; width: 100%; padding: .7rem; border: 0; border-radius: 8px;
               background: #6366f1; color: #fff; font-weight: 600; cursor: pointer; }
        button:hover { background: #4f46e5; }
        .alert { background: #7f1d1d; color: #fecaca; padding: .6rem .75rem; border-radius: 8px; font-size: .85rem; }
        .panel { max-width: 640px; }
        a.logout { color: #94a3b8; }
    </style>
    @vite(['resources/css/app.css'])
</head>
<body>
    @yield('content')
</body>
</html>
