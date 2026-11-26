@props(['label' => 'label', 'justlabel' => false, 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'options' => [], 'size' => 'l', 'default' => null, 'allow_null' => false, 'grouped' => false])
<div class="ecl-form-group ecl-u-mb-2xl" id="div_{{$id}}">
    <x-ecl.label :label="$label" :for="$id" :name="$name" :required="$required" :justlabel="$justlabel" />
    <x-ecl.help :help="$help" />
    <div class="ecl-select__container ecl-select__container--{{ $size }}">
        <select name="{{ $name }}" id="{{ $id }}" class="ecl-select" data-ecl-auto-init="Select" @if($required)required=""@endif>
            @if ($allow_null)
                <option @if(old($name, $default) === '')selected @endif value="">-- Choose here --</option>
            @else
                <option disabled @if(old($name, $default) === '')selected @endif value="">-- Choose here --</option>
            @endif
            @foreach($options as $option)
                <option @if(old($name, $default) == $option['value'])selected @endif value="{{ $option['value'] }}">@if($grouped && !str_contains($option['label'], '--'))&nbsp;&nbsp;&nbsp;&nbsp;@endif {{ ucfirst($option['label']) }}</option>
            @endforeach
        </select>
        <div class="ecl-select__icon">
            <button class="ecl-button ecl-button--tertiary ecl-button--icon-only" tabindex="-1" type="button">
                <span class="ecl-button__container">
                    <span class="ecl-button__label" data-ecl-label="true">Toggle dropdown</span>
                    <x-ecl.dropdown-caret size="xs" extra-classes="ecl-button__icon" />
                </span>
            </button>
        </div>
    </div>
    <x-ecl.error-feedback :name="$name" />
</div>
