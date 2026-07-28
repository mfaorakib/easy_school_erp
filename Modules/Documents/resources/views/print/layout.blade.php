@php $rtl = data_get(config('locales.available'), app()->getLocale().'.rtl', false); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Print' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#e5e7eb;font-family:'Segoe UI',system-ui,'Noto Sans Bengali','Noto Sans Arabic',sans-serif;color:#0f172a}
        .toolbar{position:sticky;top:0;z-index:10;background:#111827;color:#fff;display:flex;gap:.6rem;align-items:center;justify-content:space-between;padding:.7rem 1.2rem}
        .toolbar h1{font-size:1rem;margin:0;font-weight:600}
        .toolbar .actions{display:flex;gap:.5rem}
        .btn{border:0;border-radius:8px;padding:.5rem 1rem;font-size:.9rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem}
        .btn-print{background:linear-gradient(135deg,#4f46e5,#0ea5e9);color:#fff}
        .btn-back{background:#374151;color:#fff}
        .sheet{max-width:1100px;margin:24px auto;padding:0 16px}
        @yield('print-styles')
        @media print{
            body{background:#fff}
            .toolbar{display:none}
            .sheet{margin:0;max-width:none;padding:0}
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>{{ $title ?? 'Print' }}</h1>
        <div class="actions">
            @if(!empty($backUrl))<a href="{{ $backUrl }}" class="btn btn-back">← Back</a>@endif
            <button onclick="window.print()" class="btn btn-print"><i class="fa-solid fa-print" aria-hidden="true"></i> Print</button>
        </div>
    </div>
    <div class="sheet">
        @yield('documents')
    </div>
</body>
</html>
