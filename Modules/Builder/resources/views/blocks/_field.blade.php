@php
    /** @var array $field  @var \Modules\Builder\Models\CmsBlock $block */
    $key = $field['key'];
    $type = $field['type'] ?? 'text';
@endphp
@if($type === 'repeater')
    @php
        $rows = $block->get($key, []);
        // Some repeaters (e.g. Columns' "Column Content") are tied to a sibling
        // select — e.g. "Number of columns" — so at least that many rows show up
        // front, and the sibling's onchange (see blocks/index.blade.php) reveals more live.
        $syncWith = $field['sync_with'] ?? null;
        $min = $syncWith ? (int) $block->get($syncWith, 2) : 2;
        $count = max(count($rows) + 1, $min);
    @endphp
    <div class="fld-rep" @if($syncWith) data-sync-with="data[{{ $syncWith }}]" @endif>
        <label class="fld-lbl fld-rep-lbl">{{ $field['label'] }}</label>
        <div class="rep-rows">
            @for($i = 0; $i < $count; $i++)
                <div class="rep-row">
                    <span class="rep-no">{{ $i + 1 }}</span>
                    <div class="rep-row-fields">
                        @foreach($field['subfields'] as $sub)
                            @include('builder::blocks._input', [
                                'name'  => 'data['.$key.']['.$i.']['.$sub['key'].']',
                                'field' => $sub,
                                'value' => data_get($rows, $i.'.'.$sub['key']),
                            ])
                        @endforeach
                    </div>
                </div>
            @endfor
        </div>
        @if($syncWith)
            <template class="rep-template">
                <div class="rep-row">
                    <span class="rep-no"></span>
                    <div class="rep-row-fields">
                        @foreach($field['subfields'] as $sub)
                            @include('builder::blocks._input', [
                                'name'  => 'data['.$key.'][__INDEX__]['.$sub['key'].']',
                                'field' => $sub,
                                'value' => null,
                            ])
                        @endforeach
                    </div>
                </div>
            </template>
        @endif
        <p class="rep-hint">↑ Each numbered row is one item — fill a blank row to add it, clear a row to remove it.</p>
    </div>
@else
    @include('builder::blocks._input', ['name' => 'data['.$key.']', 'field' => $field, 'value' => $block->get($key)])
@endif
