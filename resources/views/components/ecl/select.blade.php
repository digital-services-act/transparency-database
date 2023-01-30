@props(['label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'options' => [], 'size' => 'l'])
<div class="ecl-form-group ecl-u-mb-l">
    <label for="select-default" class="ecl-form-label">{{ $label }}@if($required)<span class="ecl-form-label__required"> *</span>@else<span class="ecl-form-label__optional"> (optional)</span>@endif</label>
    @if($help)<div class="ecl-help-block">{{ $help }}</div>@endif
    @error($name)
    <div class="ecl-feedback-message">
        <svg class="ecl-icon ecl-icon--m ecl-feedback-message__icon" focusable="false" aria-hidden="true">
            <x-ecl.icon icon="error"/>
        </svg>
        {{ $message }}
    </div>
    @enderror
    <div class="ecl-select__container ecl-select__container--{{ $size }}">
        <select name="{{ $name }}" id="{{ $id }}" class="ecl-select" id="select-default" @if($required)required=""@endif>
            @foreach($options as $option)
                <option @if(old($name) == $option['value'])selected="" @endif value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
        <div class="ecl-select__icon">
            <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="corner-arrow" />
            </svg>
        </div>
    </div>
</div>