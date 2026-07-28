@extends('layouts.admin')
@section('title', 'Evaluations')

@section('content')
<div class="page-head"><h1>{{ $evaluation->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.evaluations') }}</h1>
    <a href="{{ route('leave.evaluations.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>

<div class="card" style="max-width:760px">
    <form method="POST" action="{{ $evaluation->exists ? route('leave.evaluations.update', $evaluation) : route('leave.evaluations.store') }}">
        @csrf @if($evaluation->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>Staff *</label>
                <select name="staff_id" required>
                    <option value="">—</option>
                    @foreach($teachers as $t)<option value="{{ $t->id }}" {{ old('staff_id', $evaluation->staff_id) == $t->id ? 'selected' : '' }}>{{ $t->displayName() }}</option>@endforeach
                </select>
                @error('staff_id')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>{{ __('ui.term') }}</label>
                <input name="term" type="text" maxlength="100" value="{{ old('term', $evaluation->term) }}">
                @error('term')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div><label>Date *</label>
                <input name="evaluation_date" type="date" required value="{{ old('evaluation_date', optional($evaluation->evaluation_date)->format('Y-m-d') ?: date('Y-m-d')) }}">
                @error('evaluation_date')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>
            <div style="grid-column:1/-1"><label>Remarks</label>
                <textarea name="remarks" rows="3" maxlength="500">{{ old('remarks', $evaluation->remarks) }}</textarea>
                @error('remarks')<small style="color:#c00">{{ $message }}</small>@enderror
            </div>

            <div style="grid-column:1/-1">
                <label>{{ __('ui.criteria') }}</label>
                <p style="color:#777;font-size:.85rem;margin:.25rem 0 .75rem">Score each 0–10; total is the average.</p>
                @php $rows = count($evaluation->criteria ?? []) + 3; @endphp
                @for($i = 0; $i < $rows; $i++)
                    <div class="grid" style="grid-template-columns:2fr 1fr;margin-bottom:.5rem">
                        <div>
                            <input name="criteria[{{ $i }}][name]" type="text" maxlength="120" placeholder="{{ __('ui.name') }}"
                                value="{{ old('criteria.'.$i.'.name', data_get($evaluation->criteria, $i.'.name')) }}">
                        </div>
                        <div>
                            <input name="criteria[{{ $i }}][score]" type="number" step="0.5" min="0" max="10" placeholder="{{ __('ui.score') }} /10"
                                value="{{ old('criteria.'.$i.'.score', data_get($evaluation->criteria, $i.'.score')) }}">
                        </div>
                    </div>
                @endfor
            </div>
        </div>
        <div style="margin-top:1.25rem"><button class="btn">{{ $evaluation->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>
@endsection
