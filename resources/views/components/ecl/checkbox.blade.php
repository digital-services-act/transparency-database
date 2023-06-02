@props(['label' => null, 'name' => null, 'id' => null, 'checked' => false, 'value' => null])
<div class="ecl-checkbox" id="div_{{$id}}">
    <input class="ecl-checkbox__input"
           id="{{ $id }}"
           name="{{ $name }}"
           value="{{ $value }}" type="checkbox"
           @if($checked)checked @endif
    />
    <label for="{{ $id }}" class="ecl-checkbox__label">
        <span class="ecl-checkbox__box">
            <svg class="ecl-icon ecl-icon--m ecl-checkbox__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="check" />
            </svg>
        </span>
        <span class="ecl-checkbox__text">{{ $label }}</span>
    </label>
</div>
