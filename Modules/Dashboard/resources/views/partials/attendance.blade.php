@php
    $segs = [
        ['key' => 'present', 'value' => $attendance['present'], 'color' => 'var(--success)', 'label' => __('ui.present')],
        ['key' => 'late',    'value' => $attendance['late'],    'color' => 'var(--warning)', 'label' => __('ui.late')],
        ['key' => 'half',    'value' => $attendance['half'],    'color' => 'var(--primary)', 'label' => __('ui.half_day')],
        ['key' => 'absent',  'value' => $attendance['absent'],  'color' => 'var(--danger)',  'label' => __('ui.absent')],
    ];
    $total = max(1, $attendance['total']);
    $cum = 0;
@endphp
<div class="widget">
  <div class="widget-title"><span>{{ __('ui.todays_attendance') }}</span></div>
  @if($attendance['total'] > 0)
     <div class="donut-wrap" id="attnDonut">
        <svg viewBox="0 0 120 120" width="130" height="130" role="img" aria-label="{{ __('ui.todays_attendance') }}">
            <circle cx="60" cy="60" r="45" fill="none" stroke="var(--border)" stroke-width="14"/>
            @foreach($segs as $s)
                @php
                    $pct = round($s['value'] / $total * 100, 1);
                    $gapUnit = $pct > 0 ? 0.8 : 0;
                    $visible = max(0, $pct - $gapUnit);
                    $offset = -$cum;
                    $cum += $pct;
                @endphp
                <circle class="donut-seg" tabindex="0"
                    cx="60" cy="60" r="45" fill="none" stroke="{{ $s['color'] }}" stroke-width="14"
                    stroke-linecap="butt" pathLength="100"
                    style="stroke-dasharray:0 100"
                    data-target="{{ $visible }} {{ 100 - $visible }}"
                    stroke-dashoffset="{{ $offset }}"
                    transform="rotate(-90 60 60)"
                    data-label="{{ $s['label'] }}" data-value="{{ $s['value'] }}" data-pct="{{ $pct }}"></circle>
            @endforeach
        </svg>
        <div class="inner"><b>{{ $attendance['rate'] }}%</b><small>{{ __('ui.present') }}</small></div>
        <div class="chart-tooltip" id="attnDonutTip" role="tooltip"></div>
     </div>
     <div class="mini mini-4">
        <div class="box"><b>{{ $attendance['present'] }}</b><small>{{ __('ui.present') }}</small></div>
        <div class="box"><b>{{ $attendance['absent'] }}</b><small>{{ __('ui.absent') }}</small></div>
        <div class="box"><b>{{ $attendance['late'] }}</b><small>{{ __('ui.late') }}</small></div>
        <div class="box"><b>{{ $attendance['half'] }}</b><small>{{ __('ui.half_day') }}</small></div>
     </div>
     <script>
     (function(){
        var wrap = document.getElementById('attnDonut');
        if(!wrap) return;
        var tip = document.getElementById('attnDonutTip');

        function positionTip(e){
            tip.style.left = (e.clientX + 12) + 'px';
            tip.style.top = (e.clientY - 12) + 'px';
        }
        function showTip(e, seg){
            tip.textContent = '';
            tip.appendChild(document.createTextNode(seg.getAttribute('data-label') + ': '));
            var strong = document.createElement('b');
            strong.textContent = seg.getAttribute('data-value') + ' (' + seg.getAttribute('data-pct') + '%)';
            tip.appendChild(strong);
            positionTip(e);
            tip.classList.add('show');
        }
        function hideTip(){ tip.classList.remove('show'); }

        wrap.querySelectorAll('.donut-seg').forEach(function(seg){
            seg.addEventListener('mouseenter', function(e){ showTip(e, seg); });
            seg.addEventListener('mousemove', positionTip);
            seg.addEventListener('mouseleave', hideTip);
            seg.addEventListener('focus', function(){
                var r = seg.getBoundingClientRect();
                showTip({ clientX: r.left + r.width / 2, clientY: r.top }, seg);
            });
            seg.addEventListener('blur', hideTip);
        });

        requestAnimationFrame(function(){
            wrap.querySelectorAll('.donut-seg').forEach(function(seg){
                seg.style.strokeDasharray = seg.getAttribute('data-target');
            });
        });
     })();
     </script>
  @else
     <p class="dash-empty">{{ __('ui.no_attendance_today') }}</p>
  @endif
</div>
