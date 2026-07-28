@extends('guardianportal::layouts.portal')

@section('title', __('ui.dashboard'))

@section('content')
    <div class="page-head">
        <h1>{{ auth()->user()->hasRole(\Modules\Access\Enums\Role::Student->value) ? __('ui.my_profile') : __('ui.my_children') }}</h1>
    </div>

    @if($children->isEmpty())
        <div class="card">
            <div class="empty">
                No children are linked to your account yet — please contact the school office.
            </div>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.2rem">
            @foreach($children as $student)
                @php
                    $record = $student->liveRecord()->first();
                    $className = optional(optional($record)->schoolClass)->name;
                    $sectionName = optional(optional($record)->section)->name;
                    $due = $summaries[$student->id]['due'] ?? 0;
                    $photoUrl = \Modules\Builder\Services\BuilderService::media($student->photo);
                @endphp
                <div class="card">
                    <div class="child-card">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $student->full_name }}" class="child-photo">
                        @else
                            <div class="child-photo">{{ strtoupper(substr($student->full_name, 0, 1)) }}</div>
                        @endif
                        <div>
                            <h3 style="margin:0 0 .25rem">{{ $student->full_name }}</h3>
                            <div style="color:var(--muted);font-size:.85rem">
                                {{ __('ui.class') }}: {{ $className ?? '—' }}
                                @if($sectionName)
                                    &middot; {{ __('ui.section') }}: {{ $sectionName }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap">
                        @if($due > 0)
                            <span class="badge badge-due">{{ __('ui.due') }}: {{ number_format($due, 2) }}</span>
                        @else
                            <span class="badge badge-paid">All Paid &#10003;</span>
                        @endif

                        <a href="{{ route('portal.children.show', $student->id) }}" class="btn">{{ __('ui.view') }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
