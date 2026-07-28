<div class="kpi-row">
    <div class="kpi">
        <div class="ic" style="background:#eef2ff;color:#4f46e5"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
        <div class="n" data-count="{{ (int) $kpis['students'] }}">{{ number_format($kpis['students']) }}</div>
        <div class="l">{{ __('ui.total_students') }}</div>
    </div>
    <div class="kpi">
        <div class="ic" style="background:#ecfeff;color:#0891b2"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></div>
        <div class="n" data-count="{{ (int) $kpis['teachers'] }}">{{ number_format($kpis['teachers']) }}</div>
        <div class="l">{{ __('ui.total_teachers') }}</div>
    </div>
    <div class="kpi">
        <div class="ic" style="background:#f0fdf4;color:#16a34a"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
        <div class="n" data-count="{{ (int) $kpis['staff'] }}">{{ number_format($kpis['staff']) }}</div>
        <div class="l">{{ __('ui.total_staff') }}</div>
    </div>
    <div class="kpi">
        <div class="ic" style="background:#fff7ed;color:#ea580c"><i class="fa-solid fa-people-roof" aria-hidden="true"></i></div>
        <div class="n" data-count="{{ (int) $kpis['guardians'] }}">{{ number_format($kpis['guardians']) }}</div>
        <div class="l">{{ __('ui.total_guardians') }}</div>
    </div>
    <div class="kpi">
        <div class="ic" style="background:#fdf2f8;color:#db2777"><i class="fa-solid fa-chalkboard" aria-hidden="true"></i></div>
        <div class="n" data-count="{{ (int) $kpis['classes'] }}">{{ number_format($kpis['classes']) }}</div>
        <div class="l">{{ __('ui.total_classes') }}</div>
    </div>
</div>
<script>
(function(){
    var els = document.querySelectorAll('.kpi-row .kpi .n[data-count]');
    if(!els.length) return;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(reduceMotion) return; // keep the server-rendered final numbers, no animation

    var DURATION = 650;
    els.forEach(function(el){
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        if(target <= 0){ return; }
        var startTime = null;
        function frame(ts){
            if(startTime === null) startTime = ts;
            var progress = Math.min(1, (ts - startTime) / DURATION);
            var eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            var value = Math.round(eased * target);
            el.textContent = value.toLocaleString('en-US');
            if(progress < 1){
                requestAnimationFrame(frame);
            } else {
                el.textContent = target.toLocaleString('en-US');
            }
        }
        el.textContent = '0';
        requestAnimationFrame(frame);
    });
})();
</script>
