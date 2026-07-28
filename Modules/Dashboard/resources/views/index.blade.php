@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<style>
    .dash-hello{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
        background:linear-gradient(135deg,var(--primary),#0ea5e9);color:#fff;border-radius:16px;padding:1.3rem 1.5rem;margin-bottom:1.1rem;
        box-shadow:0 14px 30px -14px rgba(79,70,229,.5)}
    .dash-hello h1{margin:0;font-size:1.4rem}
    .dash-hello p{margin:.2rem 0 0;opacity:.9;font-size:.9rem}
    .dash-hello .quick{display:flex;gap:.5rem;flex-wrap:wrap}
    .qbtn{background:rgba(255,255,255,.16);color:#fff;border:1px solid rgba(255,255,255,.28);border-radius:10px;
        padding:.5rem .85rem;font-size:.85rem;font-weight:600;text-decoration:none;transition:background .15s}
    .qbtn:hover{background:rgba(255,255,255,.28)}

    .kpi-row{display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.1rem}
    @media(max-width:1100px){.kpi-row{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:620px){.kpi-row{grid-template-columns:repeat(2,1fr)}}
    .kpi{position:relative;overflow:hidden;background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1.1rem 1.2rem;
        box-shadow:0 8px 22px -16px rgba(2,6,23,.5)}
    .kpi .ic{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;font-size:1.3rem;margin-bottom:.6rem}
    .kpi .n{font-size:1.7rem;font-weight:800;line-height:1;letter-spacing:-.02em}
    .kpi .l{font-size:.78rem;color:var(--muted);margin-top:.25rem}

    .dash-grid{display:grid;gap:1rem;margin-bottom:1rem}
    .dash-2{grid-template-columns:1.6fr 1fr}
    .dash-11{grid-template-columns:1fr 1fr}
    @media(max-width:900px){.dash-2,.dash-11{grid-template-columns:1fr}}
    .widget{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1.15rem 1.25rem}
    .widget-title{display:flex;align-items:center;justify-content:space-between;margin:0 0 .85rem;font-size:1rem}
    .widget-title a{font-size:.78rem;color:var(--primary);text-decoration:none;font-weight:600}
    .mini{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-top:.8rem}
    .mini .box{background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:.6rem .7rem;text-align:center}
    .mini .box b{display:block;font-size:1.15rem}
    .mini .box small{color:var(--muted);font-size:.72rem}
    /* --- interactive chart additions (tooltip, legend, bar/donut/line animation) --- */
    .chart-tooltip{position:fixed;z-index:9999;pointer-events:none;background:var(--text);color:var(--surface);
        font-size:.74rem;line-height:1.35;padding:.35rem .55rem;border-radius:8px;box-shadow:var(--shadow-md);
        opacity:0;transform:translateY(2px);transition:opacity .12s ease,transform .12s ease;max-width:220px}
    .chart-tooltip.show{opacity:1;transform:translateY(0)}
    .chart-tooltip b{font-weight:700}

    .legend{display:flex;gap:1rem;justify-content:center;margin-top:.5rem;font-size:.75rem;color:var(--muted);flex-wrap:wrap}
    .legend button{display:inline-flex;align-items:center;gap:.4rem;background:none;border:0;padding:.15rem .4rem;
        font:inherit;color:inherit;cursor:pointer;border-radius:6px;opacity:1;transition:opacity .15s,background .15s}
    .legend button.off{opacity:.35}
    .legend button .sw{display:inline-block;width:10px;height:10px;border-radius:3px;flex:none}
    .legend button:hover{background:var(--surface-2)}
    .legend button:focus-visible{outline:2px solid var(--primary);outline-offset:2px}

    svg .bar{transform-box:fill-box;transform-origin:bottom;transform:scaleY(0);opacity:0;
        transition:transform .6s cubic-bezier(.34,1.56,.64,1),opacity .35s ease}
    svg.grown .bar{transform:scaleY(1);opacity:1}
    svg .hbar{transform-box:fill-box;transform-origin:left;transform:scaleX(0);opacity:0;
        transition:transform .6s cubic-bezier(.34,1.56,.64,1),opacity .35s ease}
    svg.grown .hbar{transform:scaleX(1);opacity:1}
    svg .bar:hover,svg .hbar:hover{filter:brightness(1.08)}
    svg .bar.faded,svg .hbar.faded{opacity:.12}
    svg .bar:focus-visible,svg .hbar:focus-visible,.donut-seg:focus-visible,.pt:focus-visible{
        outline:2px solid var(--primary);outline-offset:2px}

    .donut-wrap{position:relative;width:130px;height:130px;margin:0 auto}
    .donut-seg{transition:stroke-dasharray .8s ease,filter .15s ease;cursor:pointer}
    .donut-seg:hover{filter:brightness(1.12)}
    .donut-wrap .inner{position:absolute;inset:0;display:grid;place-items:center;text-align:center;pointer-events:none}
    .donut-wrap .inner b{font-size:1.4rem;display:block}
    .donut-wrap .inner small{color:var(--muted);font-size:.7rem}

    .trend-chart .line{transition:stroke-dashoffset 1s ease}
    .trend-chart .area{transition:opacity .8s ease .3s}
    .trend-chart .pt{cursor:pointer;transition:r .12s ease}
    .trend-chart .pt:hover{filter:brightness(1.15)}

    .mini.mini-4{grid-template-columns:repeat(4,1fr)}
    @media(max-width:480px){.mini.mini-4{grid-template-columns:repeat(2,1fr)}}

    @media (prefers-reduced-motion: reduce){
        svg .bar,svg .hbar,.donut-seg,.trend-chart .line,.trend-chart .area{transition:none!important}
    }
    .feed{list-style:none;margin:0;padding:0}
    .feed li{display:flex;gap:.7rem;padding:.55rem 0;border-bottom:1px solid var(--border);font-size:.88rem}
    .feed li:last-child{border-bottom:0}
    .feed li .t{color:var(--muted);font-size:.72rem;margin-inline-start:auto;white-space:nowrap}
    .todo-list{list-style:none;margin:.4rem 0 0;padding:0}
    .todo-list li{display:flex;align-items:center;gap:.55rem;padding:.4rem 0;border-bottom:1px solid var(--border)}
    .todo-list li:last-child{border-bottom:0}
    .todo-list li.done label{text-decoration:line-through;color:var(--muted)}
    .todo-list li form{display:inline;margin:0}
    .todo-list li label{flex:1;font-size:.9rem;cursor:default}
    .dash-empty{color:var(--muted);font-size:.85rem;padding:.6rem 0}
</style>

@php $hour = (int) now()->format('G'); $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening'); @endphp
<div class="dash-hello">
    <div>
        <h1>{{ $greet }}, {{ auth()->user()->name }} <i class="fa-solid fa-hand" aria-hidden="true"></i></h1>
        <p>{{ now()->format('l, d F Y') }} · {{ optional(\Modules\Foundation\Models\AcademicYear::current())->title }}</p>
    </div>
    @include('dashboard::partials.quick')
</div>

@include('dashboard::partials.kpis')

<div class="dash-grid dash-2">
    @include('dashboard::partials.finance')
    @include('dashboard::partials.attendance')
</div>

<div class="dash-grid dash-11">
    @include('dashboard::partials.notices')
    @include('dashboard::partials.events')
</div>

<div class="dash-grid dash-11">
    @include('dashboard::partials.activity')
    @include('dashboard::partials.todos')
</div>

<div class="dash-grid dash-11">
    @include('dashboard::partials.students-by-class')
    @include('dashboard::partials.attendance-trend')
</div>
@endsection
