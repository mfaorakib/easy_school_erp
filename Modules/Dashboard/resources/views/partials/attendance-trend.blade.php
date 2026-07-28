@php
    $W = 520; $H = 170; $padL = 34; $padR = 14; $padT = 16; $padB = 26;
    $rows = $attendanceTrend ?? [];
    $n = count($rows);
    $stepX = $n > 1 ? ($W - $padL - $padR) / ($n - 1) : 0;
    $y0 = $H - $padB;   // rate 0%
    $y100 = $padT;      // rate 100%
    $mapY = fn ($rate) => $y0 - ($rate / 100) * ($y0 - $y100);

    $points = collect($rows)->values()->map(function ($row, $i) use ($padL, $stepX, $mapY) {
        return ['x' => $padL + $i * $stepX, 'y' => $mapY($row['rate']), 'label' => $row['label'], 'rate' => $row['rate']];
    });

    $linePath = $points->map(fn ($p, $i) => ($i === 0 ? 'M' : 'L') . round($p['x'], 2) . ' ' . round($p['y'], 2))->implode(' ');
    $areaPath = $points->isNotEmpty()
        ? $linePath . ' L ' . round($points->last()['x'], 2) . ' ' . $y0 . ' L ' . round($points->first()['x'], 2) . ' ' . $y0 . ' Z'
        : '';
@endphp
<div class="widget">
    <div class="widget-title"><span>{{ __('ui.attendance_trend') }}</span></div>
    <div class="trend-chart" id="trendChart">
        <svg viewBox="0 0 {{ $W }} {{ $H }}" width="100%" preserveAspectRatio="xMidYMid meet" role="img" aria-label="{{ __('ui.attendance_trend') }}" style="max-width:100%;overflow:visible">
            @foreach([0, 50, 100] as $g)
                <line x1="{{ $padL }}" y1="{{ $mapY($g) }}" x2="{{ $W - $padR }}" y2="{{ $mapY($g) }}" stroke="var(--border)" stroke-width="1"/>
                <text x="{{ $padL - 6 }}" y="{{ $mapY($g) + 3 }}" text-anchor="end" font-size="9" fill="var(--muted)">{{ $g }}</text>
            @endforeach

            @if($points->isNotEmpty())
                <path class="area" d="{{ $areaPath }}" fill="var(--primary)" fill-opacity=".12" stroke="none"/>
                <path class="line" d="{{ $linePath }}" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            @endif

            @foreach($points as $i => $p)
                @php $isLast = $i === $points->count() - 1; @endphp
                @if($isLast)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="9" fill="none" stroke="var(--primary)" stroke-opacity=".3" stroke-width="2"/>
                @endif
                <circle class="pt" tabindex="0" cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $isLast ? 6 : 4 }}"
                    fill="var(--primary)" stroke="var(--surface)" stroke-width="2"
                    data-label="{{ $p['label'] }}" data-rate="{{ $p['rate'] }}"/>
                <circle class="pt-hit" tabindex="-1" cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="13" fill="transparent"
                    data-label="{{ $p['label'] }}" data-rate="{{ $p['rate'] }}"/>
                <text x="{{ $p['x'] }}" y="{{ $H - 6 }}" text-anchor="middle" font-size="10" fill="var(--muted)">{{ $p['label'] }}</text>
            @endforeach
        </svg>
        <div class="chart-tooltip" id="trendTip" role="tooltip"></div>
    </div>
</div>
<script>
(function(){
    var root = document.getElementById('trendChart');
    if(!root) return;
    var svg = root.querySelector('svg');
    var tip = document.getElementById('trendTip');

    function positionTip(e){
        tip.style.left = (e.clientX + 12) + 'px';
        tip.style.top = (e.clientY - 12) + 'px';
    }
    function showTip(e, el){
        tip.textContent = '';
        tip.appendChild(document.createTextNode(el.getAttribute('data-label') + ': '));
        var strong = document.createElement('b');
        strong.textContent = el.getAttribute('data-rate') + '%';
        tip.appendChild(strong);
        positionTip(e);
        tip.classList.add('show');
    }
    function hideTip(){ tip.classList.remove('show'); }

    svg.querySelectorAll('.pt, .pt-hit').forEach(function(el){
        el.addEventListener('mouseenter', function(e){ showTip(e, el); });
        el.addEventListener('mousemove', positionTip);
        el.addEventListener('mouseleave', hideTip);
    });
    svg.querySelectorAll('.pt').forEach(function(el){
        el.addEventListener('focus', function(){
            var r = el.getBoundingClientRect();
            showTip({ clientX: r.left + r.width / 2, clientY: r.top }, el);
        });
        el.addEventListener('blur', hideTip);
    });

    var line = svg.querySelector('.line');
    var area = svg.querySelector('.area');
    if(line && line.getTotalLength){
        var len = line.getTotalLength();
        line.style.strokeDasharray = len;
        line.style.strokeDashoffset = len;
        if(area) area.style.opacity = 0;
        requestAnimationFrame(function(){
            line.style.strokeDashoffset = 0;
            if(area) area.style.opacity = 1;
        });
    }
})();
</script>
