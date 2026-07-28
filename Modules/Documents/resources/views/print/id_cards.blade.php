@extends('documents::print.layout', ['title' => $template->name])

@section('print-styles')
    @php
        $bg = $template->background_color ?: '#4f46e5';
        $tc = $template->text_color ?: '#ffffff';
        $land = $template->orientation === 'landscape';
    @endphp
    .cards{display:flex;flex-wrap:wrap;gap:18px;justify-content:center;padding:24px 0}
    .idcard{width:{{ $land ? '340px' : '215px' }};background:#fff;border-radius:16px;overflow:hidden;
            box-shadow:0 12px 30px -12px rgba(2,6,23,.35);border:1px solid #e5e7eb;page-break-inside:avoid;position:relative}
    .idcard .band{background:linear-gradient(135deg,{{ $bg }},color-mix(in srgb,{{ $bg }} 60%,#000));color:{{ $tc }};
                  padding:14px 14px 30px;text-align:center;position:relative}
    .idcard .band .logo{width:34px;height:34px;border-radius:8px;object-fit:cover;background:rgba(255,255,255,.2);margin:0 auto 6px;display:block}
    .idcard .band .school{font-weight:800;font-size:.95rem;letter-spacing:.02em;line-height:1.2}
    .idcard .band .ttl{font-size:.62rem;text-transform:uppercase;letter-spacing:.14em;opacity:.85;margin-top:2px}
    .idcard .photo{width:78px;height:78px;border-radius:50%;object-fit:cover;border:4px solid #fff;background:#e2e8f0;
                   display:block;margin:-34px auto 0;box-shadow:0 4px 12px rgba(2,6,23,.2);position:relative;z-index:2}
    .idcard .photo-ph{display:grid;place-items:center;font-size:1.8rem;color:#94a3b8}
    .idcard .body{padding:8px 16px 14px;text-align:center}
    .idcard .nm{font-weight:700;font-size:1.02rem;margin-top:6px}
    .idcard .sub{color:#64748b;font-size:.78rem;margin-bottom:8px}
    .idcard .idno{display:inline-block;background:color-mix(in srgb,{{ $bg }} 12%,#fff);color:{{ $bg }};
                  font-weight:700;font-size:.72rem;padding:.25rem .7rem;border-radius:999px;margin-bottom:10px}
    .idcard .rows{list-style:none;margin:0;padding:0;text-align:start;font-size:.74rem}
    .idcard .rows li{display:flex;justify-content:space-between;gap:8px;padding:.2rem 0;border-bottom:1px dashed #eef2f7}
    .idcard .rows li b{color:#475569;font-weight:600}
    .idcard .rows li span{color:#0f172a;text-align:end}
    .idcard .sign{margin-top:12px;text-align:center}
    .idcard .sign img{height:26px;object-fit:contain;display:block;margin:0 auto 2px}
    .idcard .sign small{font-size:.62rem;color:#64748b;border-top:1px solid #cbd5e1;padding-top:2px;display:inline-block;min-width:90px}
    .idcard .foot{background:{{ $bg }};color:{{ $tc }};text-align:center;font-size:.62rem;padding:.35rem;letter-spacing:.03em}
    @media print{ .idcard{box-shadow:none} }
@endsection

@section('documents')
@php $school = app(\Modules\Documents\Services\DocumentService::class)->schoolName(); @endphp
<div class="cards">
    @forelse($cards as $card)
        <div class="idcard">
            <div class="band">
                @if($template->logo_path)
                    <img class="logo" src="{{ \Modules\Documents\Services\DocumentService::media($template->logo_path) }}" alt="">
                @endif
                <div class="school">{{ $template->title ?: $school }}</div>
                <div class="ttl">Identity Card</div>
            </div>
            @if(!empty($card['photo']))
                <img class="photo" src="{{ $card['photo'] }}" alt="">
            @else
                <div class="photo photo-ph">{{ \Illuminate\Support\Str::substr($card['name'] ?: '?', 0, 1) }}</div>
            @endif
            <div class="body">
                <div class="nm">{{ $card['name'] }}</div>
                <div class="sub">{{ $card['subtitle'] }}</div>
                <div class="idno">{{ $card['id_label'] }}: {{ $card['id_no'] ?: '—' }}</div>
                @if(!empty($card['rows']))
                    <ul class="rows">
                        @foreach($card['rows'] as [$label, $value])
                            <li><b>{{ $label }}</b><span>{{ $value }}</span></li>
                        @endforeach
                    </ul>
                @endif
                <div class="sign">
                    @if($template->signature_path)<img src="{{ \Modules\Documents\Services\DocumentService::media($template->signature_path) }}" alt="">@endif
                    <small>{{ $template->signature_label ?: 'Authorised Signature' }}</small>
                </div>
            </div>
            @if($template->footer_text)<div class="foot">{{ $template->footer_text }}</div>@endif
        </div>
    @empty
        <p style="text-align:center;color:#64748b;padding:40px">No holders selected.</p>
    @endforelse
</div>
@endsection
