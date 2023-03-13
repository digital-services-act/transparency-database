@props(['label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'options' => [], 'size' => 'l', 'default' => null])
<div class="ecl-form-group ecl-u-mb-l" id="div_{{$id}}">
    <x-ecl.label :label=$label :for=$id :name=$name :required=$required />
    <x-ecl.help :help=$help />
    <x-ecl.error-feedback :name=$name />
    <div class="ecl-select__container ecl-select__container--{{ $size }}">
        <select name="{{ $name }}" id="{{ $id }}" class="ecl-select" @if($required)required=""@endif>
            <option selected disabled value="">Choose here</option>
            @foreach($options as $option)
                <option @if(old($name, $default) == $option['value'])selected="" @endif value="{{ $option['value'] }}">{{ ucfirst($option['label']) }}</option>
            @endforeach
        </select>
        <div class="ecl-select__icon">
            <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="corner-arrow" />
            </svg>
        </div>
    </div>
</div>
