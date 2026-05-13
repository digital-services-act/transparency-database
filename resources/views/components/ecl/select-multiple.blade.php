@props([
    'enter_keyword'=>'Enter keyword',
    'select_item'=>'Select an Item',
    'justlabel' => false,
    'select_all' => 'Select All',
    'label' => 'label',
    'required' => false,
    'help' => false,
    'name' => 'name',
    'id' => 'id',
    'options' => [],
    'size' => 'l',
    'default' => [],
    'invalid' => false,
    'error' => null,
])
<div class="ecl-form-group ecl-u-mb-2xl" id="{{ $id }}-multisel">
    <x-ecl.label :label="$label" :for="$id" :name="$name" :required="$required" :justlabel="$justlabel" />
    <x-ecl.help :help="$help"/>
    <div class="ecl-select__container ecl-select__container--{{ $size }} {{ $invalid ? 'ecl-select__container--invalid' : '' }}">
        <select name="{{ $name }}[]" id="{{ $id }}"
            class="ecl-select"
            @if($required)required="" @endif multiple=""
            data-ecl-auto-init="Select"
            data-ecl-select-multiple=""
            data-ecl-select-default="{{$select_item}}"
            data-ecl-select-search="{{$enter_keyword}}"
            data-ecl-select-no-results="No results found"
            data-ecl-select-all="{{$select_all}}"
        >
            @foreach($options as $option)
                <option @if(in_array($option['value'], old($name, $default)))selected=""
                        @endif value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach

        </select>


        <div class="ecl-select__icon">
            <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape" focusable="false"
                 aria-hidden="true">
                <x-ecl.icon icon="corner-arrow"/>
            </svg>
        </div>
    </div>
    <x-ecl.error-feedback :name="$name" :error="$error"/>

</div>

