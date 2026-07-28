@extends('documents::print.layout', ['title' => $template->name])

@section('print-styles')
    @php
        $land = ($template->orientation ?? 'landscape') === 'landscape';
        $accent = $template->accent_color ?: '#b9942f';
        $fontStack = match ($template->font_family ?? 'serif') {
            'sans' => "'Segoe UI',system-ui,sans-serif",
            'elegant' => "'Book Antiqua','Palatino Linotype',Georgia,serif",
            default => "Georgia,'Times New Roman',serif",
        };
        $borderStyle = $template->border_style ?? 'classic';
    @endphp
    @page{size:A4 {{ $land ? 'landscape' : 'portrait' }};margin:0}
    .cert{position:relative;background:#fff;margin:24px auto;
          width:{{ $land ? '1040px' : '760px' }};min-height:{{ $land ? '735px' : '1040px' }};
          box-shadow:0 16px 40px -16px rgba(2,6,23,.35);page-break-after:always;overflow:hidden}
    .cert:last-child{page-break-after:auto}
    .cert .bgimg{position:absolute;inset:0;background-size:cover;background-position:center;opacity:.08}
    @if($borderStyle === 'classic')
    .cert .frame{position:absolute;inset:24px;border:3px solid {{ $accent }};border-radius:6px;pointer-events:none}
    .cert .frame::before{content:"";position:absolute;inset:7px;border:1px solid {{ $accent }}99;border-radius:4px}
    @elseif($borderStyle === 'simple')
    .cert .frame{position:absolute;inset:24px;border:2px solid {{ $accent }};border-radius:4px;pointer-events:none}
    @else
    .cert .frame{display:none}
    @endif
    .cert .inner{position:relative;padding:70px 90px;text-align:center;height:100%;display:flex;flex-direction:column}
    .cert .head{margin-bottom:10px}
    .cert .head img{max-height:70px;object-fit:contain;margin-bottom:8px}
    .cert .school{font-size:1.15rem;font-weight:800;letter-spacing:.06em;color:#334155;text-transform:uppercase}
    .cert h1{font-family:{{ $fontStack }};font-size:2.6rem;color:{{ $accent }};margin:.4rem 0 .2rem;letter-spacing:.04em}
    .cert .rule{width:120px;height:3px;background:linear-gradient(90deg,transparent,{{ $accent }},transparent);margin:.4rem auto 1.4rem}
    .cert .body{font-family:{{ $fontStack }};font-size:1.15rem;line-height:2;color:#1f2937;max-width:640px;margin:0 auto;flex:1}
    .cert .body .holder{font-size:1.6rem;font-weight:700;color:#0f172a;display:block;margin:.3rem 0}
    .cert .signs{display:flex;justify-content:space-between;gap:40px;margin-top:40px;padding:0 20px}
    .cert .sign{flex:1;text-align:center}
    .cert .sign img{height:40px;object-fit:contain;display:block;margin:0 auto 4px}
    .cert .sign .line{border-top:1.5px solid #475569;padding-top:6px;font-size:.85rem;color:#475569;font-weight:600}
    @media print{ .cert{box-shadow:none;margin:0;width:auto;min-height:auto} }
@endsection

@section('documents')
@php $svc = app(\Modules\Documents\Services\DocumentService::class); @endphp
@forelse($documents as $doc)
    <div class="cert">
        @if($template->background_image_path)
            <div class="bgimg" style="background-image:url('{{ \Modules\Documents\Services\DocumentService::media($template->background_image_path) }}')"></div>
        @endif
        <div class="frame"></div>
        <div class="inner">
            <div class="head">
                @if($template->header_image_path)
                    <img src="{{ \Modules\Documents\Services\DocumentService::media($template->header_image_path) }}" alt="">
                @endif
                <div class="school">{{ $svc->schoolName() }}</div>
            </div>
            <h1>{{ $template->heading ?: 'Certificate' }}</h1>
            <div class="rule"></div>
            <div class="body">{!! $doc['body'] !!}</div>
            <div class="signs">
                <div class="sign">
                    @if($template->signature_left_path)<img src="{{ \Modules\Documents\Services\DocumentService::media($template->signature_left_path) }}" alt="">@endif
                    <div class="line">{{ $template->signature_left_label ?: 'Class Teacher' }}</div>
                </div>
                <div class="sign">
                    @if($template->signature_right_path)<img src="{{ \Modules\Documents\Services\DocumentService::media($template->signature_right_path) }}" alt="">@endif
                    <div class="line">{{ $template->signature_right_label ?: 'Principal' }}</div>
                </div>
            </div>
        </div>
    </div>
@empty
    <p style="text-align:center;color:#64748b;padding:40px">No recipients selected.</p>
@endforelse
@endsection
