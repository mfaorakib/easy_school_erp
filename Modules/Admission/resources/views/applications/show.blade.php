@extends('layouts.admin')
@section('title', 'Admission Application')

@section('content')
<div class="page-head">
    <h1>{{ $application->first_name }} {{ $application->last_name }}
        @if($application->status === 'pending')
            <span class="badge" style="background:#fef3c7;color:#92400e">{{ __('ui.pending') }}</span>
        @elseif($application->status === 'confirmed')
            <span class="badge" style="background:#dcfce7;color:#166534">{{ __('ui.confirmed') }}</span>
        @else
            <span class="badge" style="background:#fee2e2;color:#991b1b">{{ __('ui.rejected') }}</span>
        @endif
    </h1>
    <a href="{{ route('admission.admin.applications.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a>
</div>

<div class="card">
    <h2>Applicant Details</h2>
    <div class="grid">
        <div><label>{{ __('ui.reference_no') }}</label><p><code>{{ $application->reference_no }}</code></p></div>
        <div><label>{{ __('ui.name') }}</label><p>{{ $application->first_name }} {{ $application->last_name }}</p></div>
        <div><label>{{ __('ui.desired_class') }}</label><p>{{ $application->desiredClass->name ?? '—' }}</p></div>
        <div><label>Gender</label><p>{{ $application->gender->name ?? '—' }}</p></div>
        <div><label>Date of Birth</label><p>{{ optional($application->date_of_birth)->format('d M Y') ?? '—' }}</p></div>
        <div><label>{{ __('ui.guardian') }} — {{ __('ui.name') }}</label><p>{{ $application->guardian_name ?? '—' }}</p></div>
        <div><label>{{ __('ui.guardian') }} — Relation</label><p>{{ $application->guardian_relation ?? '—' }}</p></div>
        <div><label>{{ __('ui.guardian') }} — Mobile</label><p>{{ $application->guardian_mobile ?? '—' }}</p></div>
        <div><label>{{ __('ui.guardian') }} — Email</label><p>{{ $application->guardian_email ?? '—' }}</p></div>
        <div><label>{{ __('ui.present_address') }}</label><p>{{ $application->present_address ?? '—' }}</p></div>
        <div><label>{{ __('ui.previous_school') }}</label><p>{{ $application->previous_school ?? '—' }}</p></div>
        @if($application->photo_path)
        <div>
            <label>{{ __('ui.photo') }}</label>
            <p><img src="{{ \Modules\Builder\Services\BuilderService::media($application->photo_path) }}" style="width:120px;height:120px;object-fit:cover;border-radius:12px"></p>
        </div>
        @endif
    </div>
</div>

<div class="card">
    <h2>{{ __('ui.student_documents') }}</h2>
    @if($application->documents->isEmpty())
        <div class="empty">{{ __('ui.no_documents_yet') }}</div>
    @else
        <div class="overflow-x-auto">
        <table>
            <thead>
            <tr>
                <th>{{ __('ui.type') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($application->documents as $doc)
                <tr>
                    <td>{{ optional($doc->type)->name ?? 'Document' }}</td>
                    <td class="actions">
                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

@if($application->status === \Modules\Admission\Models\AdmissionApplication::STATUS_PENDING)
<div class="grid">
    <div class="card">
        <h2>{{ __('ui.confirm') }}</h2>
        <form method="POST" action="{{ route('admission.admin.applications.confirm', $application) }}" enctype="multipart/form-data">
            @csrf
            <div>
                <label>{{ __('ui.class') }}</label>
                <select name="class_id">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $c->id == $application->desired_class_id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('class_id')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div>
                <label>{{ __('ui.section') }} *</label>
                <select name="section_id" required>
                    <option value="">—</option>
                    @foreach($sections as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('section_id')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div>
                <label>{{ __('ui.photo') }} (optional — replaces the applicant photo)</label>
                <input type="file" name="photo" accept="image/*">
                @error('photo')<div class="badge btn-danger">{{ $message }}</div>@enderror
            </div>
            <div style="margin-top:1rem"><button class="btn">{{ __('ui.confirm') }}</button></div>
        </form>
    </div>
    <div class="card">
        <h2>{{ __('ui.reject') }}</h2>
        <form method="POST" action="{{ route('admission.admin.applications.reject', $application) }}" onsubmit="return confirm('{{ __('ui.reject') }}?')">
            @csrf
            <label>{{ __('ui.rejection_reason') }}</label>
            <textarea name="reason" rows="3" required></textarea>
            @error('reason')<div class="badge btn-danger">{{ $message }}</div>@enderror
            <div style="margin-top:1rem"><button class="btn btn-danger">{{ __('ui.reject') }}</button></div>
        </form>
    </div>
</div>
@elseif($application->status === 'confirmed')
<div class="card">
    <h2>{{ __('ui.confirmed') }}</h2>
    <div class="grid">
        <div><label>{{ __('ui.unique_id') }}</label><p><strong style="font-size:1.25rem">{{ $application->unique_id }}</strong></p></div>
        <div><label>Enrolled Student</label><p>{{ $application->student->full_name ?? '—' }}</p></div>
        <div><label>{{ __('ui.reviewed_by') }}</label><p>{{ $application->reviewer->name ?? '—' }}</p></div>
        <div><label>{{ __('ui.reviewed_at') }}</label><p>{{ optional($application->reviewed_at)->format('d M Y, h:i A') }}</p></div>
    </div>
</div>
@else
<div class="card">
    <h2>{{ __('ui.rejected') }}</h2>
    <div class="grid">
        <div><label>{{ __('ui.rejection_reason') }}</label><p>{{ $application->rejection_reason }}</p></div>
        <div><label>{{ __('ui.reviewed_by') }}</label><p>{{ $application->reviewer->name ?? '—' }}</p></div>
        <div><label>{{ __('ui.reviewed_at') }}</label><p>{{ optional($application->reviewed_at)->format('d M Y, h:i A') }}</p></div>
    </div>
</div>
@endif
@endsection
