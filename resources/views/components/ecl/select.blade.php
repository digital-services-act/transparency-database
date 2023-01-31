@props(['label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'options' => [], 'size' => 'l', 'default' => null])
<div class="ecl-form-group ecl-u-mb-l">
    <x-ecl.label :label=$label :for=$id :name=$name :required=$required />
    <x-ecl.help :help=$help />
    <x-ecl.error-feedback :name=$name />
    <div class="ecl-select__container ecl-select__container--{{ $size }}">
        <select name="{{ $name }}" id="{{ $id }}" class="ecl-select" @if($required)required=""@endif>
            @foreach($options as $option)
                <option @if(old($name, $default) == $option['value'])selected="" @endif value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
        <div class="ecl-select__icon">
            <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="corner-arrow" />
            </svg>
        </div>
    </div>
</div>