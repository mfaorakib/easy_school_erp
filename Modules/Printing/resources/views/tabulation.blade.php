@extends('layouts.admin')
@section('title', __('ui.tabulation'))

@section('content')
<div class="page-head"><h1>{{ __('ui.tabulation') }}</h1></div>

<div class="card">
    <form method="POST" action="{{ route('printing.tabulation.generate') }}" target="_blank">
        @csrf
        <div class="grid">
            <div>
                <label>Exam</label>
                <select name="exam_id">
                    <option value="">—</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                            {{ $exam->name }}{{ optional($exam->type)->name ? ' — '.$exam->type->name : '' }}
                        </option>
                    @endforeach
                </select>
                @error('exam_id')<p class="badge">{{ $message }}</p>@enderror
            </div>
            <div>
                <label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                @error('class_id')<p class="badge">{{ $message }}</p>@enderror
            </div>
            <div>
                <label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
                @error('section_id')<p class="badge">{{ $message }}</p>@enderror
            </div>
            <div style="align-self:end">
                <button class="btn btn-sm" type="submit">{{ __('ui.generate_print') }}</button>
            </div>
        </div>
    </form>
    <p class="empty">Pick an exam, class and section, then Generate opens the printable tabulation in a new tab.</p>
</div>
@endsection
