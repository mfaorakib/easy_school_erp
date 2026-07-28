@extends('layouts.admin')
@section('title', 'Question Bank')

@section('content')
<div class="page-head"><h1>{{ $question->exists ? __('ui.edit') : __('ui.add') }} — {{ __('ui.question_bank') }}</h1>
    <a href="{{ route('onlineexam.questions.index') }}" class="btn btn-ghost">{{ __('ui.back') }}</a></div>
<div class="card" style="max-width:820px">
    <form method="POST" action="{{ $question->exists ? route('onlineexam.questions.update', $question) : route('onlineexam.questions.store') }}">
        @csrf @if($question->exists) @method('PUT') @endif
        <div class="grid">
            <div><label>{{ __('ui.question_groups') }}</label>
                <select name="question_group_id">
                    <option value="">—</option>
                    @foreach($groups as $g)<option value="{{ $g->id }}" {{ old('question_group_id', $question->question_group_id) == $g->id ? 'selected' : '' }}>{{ $g->title }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.classes') }}</label>
                <select name="class_id">
                    <option value="">—</option>
                    @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id', $question->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div><label>{{ __('ui.sections') }}</label>
                <select name="section_id">
                    <option value="">—</option>
                    @foreach($sections as $s)<option value="{{ $s->id }}" {{ old('section_id', $question->section_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label>Type *</label>
                <select name="type" id="type-select" required>
                    <option value="mcq" {{ old('type', $question->type) == 'mcq' ? 'selected' : '' }}>{{ __('ui.mcq') }}</option>
                    <option value="truefalse" {{ old('type', $question->type) == 'truefalse' ? 'selected' : '' }}>{{ __('ui.true_false') }}</option>
                    <option value="fill" {{ old('type', $question->type) == 'fill' ? 'selected' : '' }}>{{ __('ui.fill_blank') }}</option>
                </select>
            </div>
            <div><label>{{ __('ui.difficulty') }} *</label>
                <select name="difficulty" required>
                    <option value="easy" {{ old('difficulty', $question->difficulty) == 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ old('difficulty', $question->difficulty ?: 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard" {{ old('difficulty', $question->difficulty) == 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>
            <div><label>{{ __('ui.marks') }} *</label>
                <input name="marks" type="number" step="0.01" min="0" value="{{ old('marks', $question->marks ?? 1) }}" required></div>
            <div style="grid-column:1/-1"><label>Question *</label>
                <textarea name="question" rows="3" required>{{ old('question', $question->question) }}</textarea></div>
        </div>

        {{-- MCQ block --}}
        <div id="block-mcq" style="margin-top:1.25rem">
            <label>Options</label>
            @error('options')<div class="badge" style="display:block;margin:.5rem 0">{{ $message }}</div>@enderror
            <div id="mcq-options">
                @php
                    $oldOptions = old('options');
                    $oldCorrect = old('correct', []);
                @endphp
                @if($oldOptions !== null)
                    @foreach($oldOptions as $i => $opt)
                        <div class="mcq-row" style="display:flex;gap:.5rem;align-items:center;margin-bottom:.5rem">
                            <input name="options[]" type="text" maxlength="500" value="{{ $opt }}" style="flex:1">
                            <label style="white-space:nowrap"><input type="checkbox" name="correct[]" value="{{ $i }}" {{ in_array((string) $i, $oldCorrect) ? 'checked' : '' }}> Correct</label>
                        </div>
                    @endforeach
                @elseif($question->exists && $question->options->count())
                    @foreach($question->options as $i => $opt)
                        <div class="mcq-row" style="display:flex;gap:.5rem;align-items:center;margin-bottom:.5rem">
                            <input name="options[]" type="text" maxlength="500" value="{{ $opt->title }}" style="flex:1">
                            <label style="white-space:nowrap"><input type="checkbox" name="correct[]" value="{{ $i }}" {{ $opt->is_correct ? 'checked' : '' }}> Correct</label>
                        </div>
                    @endforeach
                @else
                    @for($i = 0; $i < 4; $i++)
                        <div class="mcq-row" style="display:flex;gap:.5rem;align-items:center;margin-bottom:.5rem">
                            <input name="options[]" type="text" maxlength="500" style="flex:1">
                            <label style="white-space:nowrap"><input type="checkbox" name="correct[]" value="{{ $i }}"> Correct</label>
                        </div>
                    @endfor
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-ghost" id="add-option">+ Add option</button>
        </div>

        {{-- True/False block --}}
        @php
            $cbRaw = old('correct_bool', $question->exists ? ($question->correct_bool ? '1' : '0') : null);
            $cb = $cbRaw === null ? null : (string) $cbRaw;
        @endphp
        <div id="block-truefalse" style="margin-top:1.25rem">
            <label>{{ __('ui.correct_answer') }}</label>
            <div style="display:flex;gap:1.5rem;margin-top:.5rem">
                <label><input type="radio" name="correct_bool" value="1" {{ $cb === '1' ? 'checked' : '' }}> {{ __('ui.true') }}</label>
                <label><input type="radio" name="correct_bool" value="0" {{ $cb === '0' ? 'checked' : '' }}> {{ __('ui.false') }}</label>
            </div>
        </div>

        {{-- Fill block --}}
        <div id="block-fill" style="margin-top:1.25rem">
            <label>{{ __('ui.correct_answer') }}</label>
            <textarea name="answer_text" rows="2">{{ old('answer_text', $question->answer_text) }}</textarea>
        </div>

        <div style="margin-top:1.25rem"><button class="btn">{{ $question->exists ? __('ui.update') : __('ui.create') }}</button></div>
    </form>
</div>

<script>
(function () {
    var typeSelect = document.getElementById('type-select');
    var blocks = {
        mcq: document.getElementById('block-mcq'),
        truefalse: document.getElementById('block-truefalse'),
        fill: document.getElementById('block-fill')
    };

    function toggleBlocks() {
        var t = typeSelect.value;
        for (var key in blocks) {
            if (blocks.hasOwnProperty(key)) {
                blocks[key].style.display = (key === t) ? '' : 'none';
            }
        }
    }

    function renumberCheckboxes() {
        var rows = document.querySelectorAll('#mcq-options .mcq-row');
        for (var i = 0; i < rows.length; i++) {
            var cb = rows[i].querySelector('input[type="checkbox"]');
            if (cb) { cb.value = i; }
        }
    }

    var addBtn = document.getElementById('add-option');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var container = document.getElementById('mcq-options');
            var row = document.createElement('div');
            row.className = 'mcq-row';
            row.style.cssText = 'display:flex;gap:.5rem;align-items:center;margin-bottom:.5rem';
            row.innerHTML = '<input name="options[]" type="text" maxlength="500" style="flex:1">' +
                '<label style="white-space:nowrap"><input type="checkbox" name="correct[]" value="0"> Correct</label>';
            container.appendChild(row);
            renumberCheckboxes();
        });
    }

    typeSelect.addEventListener('change', toggleBlocks);
    renumberCheckboxes();
    toggleBlocks();
})();
</script>
@endsection
