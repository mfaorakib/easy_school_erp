@php
    $rows = $studentsByClass ?? [];
    $n = count($rows);
    $totals = array_column($rows, 'total');
    $max = $totals ? max(1, max($totals)) : 1;
    $W = 520; $rowH = 28; $padT = 8; $padB = 8; $labelW = 100; $padR = 46;
    $H = $padT + $rowH * max(1, $n) + $padB;
    $barX0 = $labelW;
    $chartW = max(10, $W - $labelW - $padR);
@endphp
<div class="widget">
    <div class="widget-title"><span>{{ __('ui.students_by_class') }}</span></div>
    @if(empty($rows))
        <p class="dash-empty">{{ __('ui.no_classes_yet') }}</p>
    @else
        <div class="hbar-chart" id="classesChart">
            <svg viewBox="0 0 {{ $W }} {{ $H }}" width="100%" preserveAspectRatio="xMidYMid meet" role="img" aria-label="{{ __('ui.students_by_class') }}" style="max-width:100%;overflow:visible">
                <line x1="{{ $barX0 }}" y1="{{ $padT }}" x2="{{ $barX0 }}" y2="{{ $H - $padB }}" stroke="var(--border)" stroke-width="1"/>
                @foreach($rows as $i => $row)
                    @php
                        $rowY = $padT + $i * $rowH;
                        $barH = 15;
                        $barY = $rowY + ($rowH - $barH) / 2;
                        $w = $chartW * ($row['total'] / $max);
                        $shortLabel = \Illuminate\Support\Str::limit($row['label'], 15, '…');
                    @endphp
                    <text x="{{ $labelW - 8 }}" y="{{ round($rowY + $rowH / 2 + 3, 2) }}" text-anchor="end" font-size="10.5" fill="var(--text)">{{ $shortLabel }}</text>
                    <rect class="hbar" tabindex="0" x="{{ $barX0 }}" y="{{ round($barY, 2) }}" width="{{ round(max(0, $w), 2) }}" height="{{ $barH }}" rx="4"
                          fill="var(--primary)" data-label="{{ $row['label'] }}" data-value="{{ $row['total'] }}"/>
                    <text x="{{ round($barX0 + $w + 6, 2) }}" y="{{ round($rowY + $rowH / 2 + 3, 2) }}" font-size="10" fill="var(--muted)">{{ $row['total'] }}</text>
                @endforeach
            </svg>
            <div class="chart-tooltip" id="classesChartTip" role="tooltip"></div>
        </div>
        <script>
        (function(){
            var root = document.getElementById('classesChart');
            if(!root) return;
            var svg = root.querySelector('svg');
            var tip = document.getElementById('classesChartTip');

            function positionTip(e){
                tip.style.left = (e.clientX + 12) + 'px';
                tip.style.top = (e.clientY - 12) + 'px';
            }
            function showTip(e, bar){
                tip.textContent = '';
                tip.appendChild(document.createTextNode(bar.getAttribute('data-label') + ': '));
                var strong = document.createElement('b');
                strong.textContent = bar.getAttribute('data-value');
                tip.appendChild(strong);
                positionTip(e);
                tip.classList.add('show');
            }
            function hideTip(){ tip.classList.remove('show'); }

            svg.querySelectorAll('.hbar').forEach(function(bar){
                bar.addEventListener('mouseenter', function(e){ showTip(e, bar); });
                bar.addEventListener('mousemove', positionTip);
                bar.addEventListener('mouseleave', hideTip);
                bar.addEventListener('focus', function(){
                    var r = bar.getBoundingClientRect();
                    showTip({ clientX: r.left + r.width, clientY: r.top }, bar);
                });
                bar.addEventListener('blur', hideTip);
            });

            requestAnimationFrame(function(){ svg.classList.add('grown'); });
        })();
        </script>
    @endif
</div>
