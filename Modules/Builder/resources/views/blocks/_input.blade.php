@php
    /** @var array $field  keys: type,label,(options)  @var string $name  @var mixed $value */
    $t = $field['type'] ?? 'text';
@endphp
@if($t === 'toggle')
    <label class="tgl">
        <input type="checkbox" name="{{ $name }}" value="1" @checked(filter_var($value, FILTER_VALIDATE_BOOL))>
        <span>{{ $field['label'] }}</span>
    </label>
@else
    <div class="fld @if(in_array($t, ['textarea', 'richtext'])) fld-wide @endif">
        <label class="fld-lbl">{{ $field['label'] }}</label>
        @if($t === 'richtext')
            <div class="rte" data-rte>
                <div class="rte-toolbar">
                    <button type="button" data-cmd="bold" title="Bold"><b>B</b></button>
                    <button type="button" data-cmd="italic" title="Italic"><i>I</i></button>
                    <button type="button" data-cmd="underline" title="Underline"><u>U</u></button>
                    <span class="rte-sep"></span>
                    <button type="button" data-cmd="formatBlock" data-val="h3" title="Heading">H2</button>
                    <button type="button" data-cmd="formatBlock" data-val="p" title="Paragraph">¶</button>
                    <button type="button" data-cmd="formatBlock" data-val="blockquote" title="Quote">❝</button>
                    <span class="rte-sep"></span>
                    <button type="button" data-cmd="insertUnorderedList" title="Bullet list">• List</button>
                    <button type="button" data-cmd="insertOrderedList" title="Numbered list">1. List</button>
                    <span class="rte-sep"></span>
                    <button type="button" data-cmd="createLink" title="Insert link">🔗 Link</button>
                    <button type="button" data-cmd="unlink" title="Remove link">Unlink</button>
                    <button type="button" data-cmd="insertImage" title="Insert image">🖼 Image</button>
                    <span class="rte-sep"></span>
                    <button type="button" data-cmd="removeFormat" title="Clear formatting">✕ Clear</button>
                </div>
                <div class="rte-editor" contenteditable="true" data-placeholder="Write here…">{!! $value !!}</div>
                <textarea name="{{ $name }}" class="rte-hidden" hidden>{{ $value }}</textarea>
            </div>
        @elseif($t === 'textarea')
            <textarea name="{{ $name }}" rows="2">{{ $value }}</textarea>
        @elseif($t === 'select')
            <select name="{{ $name }}">
                @foreach($field['options'] as $ov => $ol)
                    <option value="{{ $ov }}" @selected((string) $value === (string) $ov)>{{ $ol }}</option>
                @endforeach
            </select>
        @elseif($t === 'image')
            <input name="{{ $name }}" value="{{ $value }}" placeholder="Image URL or storage path">
        @elseif($t === 'icon')
            <input name="{{ $name }}" value="{{ $value }}" placeholder="😀" style="max-width:90px">
        @else
            <input name="{{ $name }}" value="{{ $value }}" @if($t === 'url') placeholder="https://…" @endif>
        @endif
    </div>
@endif
