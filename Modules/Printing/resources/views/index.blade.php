@extends('layouts.admin')
@section('title', __('ui.print_center'))

@section('content')
<div class="page-head"><h1>{{ __('ui.print_center') }}</h1></div>

<div class="grid">
    <a class="card" href="{{ route('printing.marksheet') }}" style="text-decoration:none;display:block;padding:20px;transition:transform .1s ease,box-shadow .1s ease" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="font-size:2.5rem;line-height:1"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></div>
        <h3 style="margin:12px 0 4px">{{ __('ui.marksheet') }}</h3>
        <p style="margin:0;color:#6b7280">Per-student exam marksheet</p>
    </a>

    <a class="card" href="{{ route('printing.tabulation') }}" style="text-decoration:none;display:block;padding:20px;transition:transform .1s ease,box-shadow .1s ease" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="font-size:2.5rem;line-height:1"><i class="fa-solid fa-table" aria-hidden="true"></i></div>
        <h3 style="margin:12px 0 4px">{{ __('ui.tabulation') }}</h3>
        <p style="margin:0;color:#6b7280">Whole-class result grid</p>
    </a>

    <a class="card" href="{{ route('printing.progress') }}" style="text-decoration:none;display:block;padding:20px;transition:transform .1s ease,box-shadow .1s ease" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="font-size:2.5rem;line-height:1"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></div>
        <h3 style="margin:12px 0 4px">{{ __('ui.progress_card') }}</h3>
        <p style="margin:0;color:#6b7280">Term-by-term progress</p>
    </a>

    <a class="card" href="{{ route('printing.invoice') }}" style="text-decoration:none;display:block;padding:20px;transition:transform .1s ease,box-shadow .1s ease" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="font-size:2.5rem;line-height:1"><i class="fa-solid fa-receipt" aria-hidden="true"></i></div>
        <h3 style="margin:12px 0 4px">{{ __('ui.fee_statement') }}</h3>
        <p style="margin:0;color:#6b7280">Student fee statement</p>
    </a>

    <a class="card" href="{{ route('documents.idCards.index') }}" style="text-decoration:none;display:block;padding:20px;transition:transform .1s ease,box-shadow .1s ease" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="font-size:2.5rem;line-height:1"><i class="fa-solid fa-id-card" aria-hidden="true"></i></div>
        <h3 style="margin:12px 0 4px">{{ __('ui.id_cards') }}</h3>
        <p style="margin:0;color:#6b7280">Student / staff ID cards</p>
    </a>

    <a class="card" href="{{ route('documents.certificates.index') }}" style="text-decoration:none;display:block;padding:20px;transition:transform .1s ease,box-shadow .1s ease" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="font-size:2.5rem;line-height:1"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
        <h3 style="margin:12px 0 4px">{{ __('ui.certificates') }}</h3>
        <p style="margin:0;color:#6b7280">Certificates</p>
    </a>
</div>
@endsection
