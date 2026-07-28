@php
    // $series = [ ['label'=>, 'income'=>, 'expense'=>], ... ]. Dependency-free grouped SVG bars.
    $max = max(1, collect($series)->flatMap(fn ($r) => [$r['income'], $r['expense']])->max());
    $n = max(1, count($series));
    $W = 520; $H = 180; $pad = 24; $gap = 14;
    $groupW = ($W - $pad * 2) / $n;
    $barW = ($groupW - $gap) / 2;
    $scale = fn ($v) => ($H - $pad * 2) * ($v / $max);
@endphp
<div class="fin-chart" id="finChart">
    <svg viewBox="0 0 {{ $W }} {{ $H }}" width="100%" preserveAspectRatio="xMidYMid meet" role="img" aria-label="{{ __('ui.income_expense') }}" style="max-width:100%;overflow:visible">
        {{-- baseline --}}
        <line x1="{{ $pad }}" y1="{{ $H - $pad }}" x2="{{ $W - $pad }}" y2="{{ $H - $pad }}" stroke="var(--border)" stroke-width="1"/>
        @foreach($series as $i => $row)
            @php
                $gx = $pad + $i * $groupW;
                $ih = $scale($row['income']); $eh = $scale($row['expense']);
                $baseY = $H - $pad;
            @endphp
            <rect class="bar" tabindex="0" data-series="income"
                  data-label="{{ $row['label'] }}" data-value="{{ number_format($row['income'], 0) }}"
                  x="{{ round($gx + $gap / 2, 2) }}" y="{{ round($baseY - $ih, 2) }}" width="{{ round($barW, 2) }}" height="{{ round(max(0, $ih), 2) }}" rx="4" fill="var(--success)"/>
            <rect class="bar" tabindex="0" data-series="expense"
                  data-label="{{ $row['label'] }}" data-value="{{ number_format($row['expense'], 0) }}"
                  x="{{ round($gx + $gap / 2 + $barW, 2) }}" y="{{ round($baseY - $eh, 2) }}" width="{{ round($barW, 2) }}" height="{{ round(max(0, $eh), 2) }}" rx="4" fill="var(--warning)"/>
            <text x="{{ round($gx + $groupW / 2, 2) }}" y="{{ $H - 6 }}" text-anchor="middle" font-size="10" fill="var(--muted)">{{ $row['label'] }}</text>
        @endforeach
    </svg>
    <div class="chart-tooltip" id="finChartTip" role="tooltip"></div>
    <div class="legend" id="finLegend">
        <button type="button" class="legend-toggle" data-series="income" aria-pressed="true">
            <span class="sw" style="background:var(--success)"></span>{{ __('ui.income') }}
        </button>
        <button type="button" class="legend-toggle" data-series="expense" aria-pressed="true">
            <span class="sw" style="background:var(--warning)"></span>{{ __('ui.expense') }}
        </button>
    </div>
</div>
<script>
(function(){
    var root = document.getElementById('finChart');
    if(!root) return;
    var svg = root.querySelector('svg');
    var tip = document.getElementById('finChartTip');
    var seriesNames = { income: @json(__('ui.income')), expense: @json(__('ui.expense')) };

    function setTip(prefix, value){
        tip.textContent = '';
        tip.appendChild(document.createTextNode(prefix + ': '));
        var strong = document.createElement('b');
        strong.textContent = value;
        tip.appendChild(strong);
    }
    function positionTip(e){
        tip.style.left = (e.clientX + 12) + 'px';
        tip.style.top = (e.clientY - 12) + 'px';
    }
    function showTip(e, bar){
        var series = bar.getAttribute('data-series');
        var label = bar.getAttribute('data-label');
        setTip(seriesNames[series] + ' — ' + label, bar.getAttribute('data-value'));
        positionTip(e);
        tip.classList.add('show');
    }
    function hideTip(){ tip.classList.remove('show'); }

    svg.querySelectorAll('.bar').forEach(function(bar){
        bar.addEventListener('mouseenter', function(e){ showTip(e, bar); });
        bar.addEventListener('mousemove', positionTip);
        bar.addEventListener('mouseleave', hideTip);
        bar.addEventListener('focus', function(){
            var r = bar.getBoundingClientRect();
            showTip({ clientX: r.left + r.width / 2, clientY: r.top }, bar);
        });
        bar.addEventListener('blur', hideTip);
    });

    root.querySelectorAll('.legend-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            var series = btn.getAttribute('data-series');
            var isOff = btn.classList.toggle('off');
            btn.setAttribute('aria-pressed', isOff ? 'false' : 'true');
            svg.querySelectorAll('.bar[data-series="' + series + '"]').forEach(function(bar){
                bar.classList.toggle('faded', isOff);
            });
        });
    });

    requestAnimationFrame(function(){ svg.classList.add('grown'); });
})();
</script>
