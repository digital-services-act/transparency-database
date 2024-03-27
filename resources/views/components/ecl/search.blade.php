@props(['label' => 'Search', 'name' => 'name', 'id' => 'id', 'size' => 'm', 'placeholder' => '', 'value' => null])

<div class="ecl-form-group">
    <label for="{{ $id }}" class="ecl-form-label ecl-search-form__label">{{ $label }}</label>
    <input type="search"
           class="ecl-text-input ecl-text-input--{{ $size }} ecl-search-form__text-input"
           name="{{ $name }}"
           id="{{ $id }}"
           placeholder="{{ $placeholder }}"
           value="{{ $value }}"
    />
</div>
<button class="ecl-button ecl-button--search ecl-search-form__button" type="submit" aria-label="{{ $label }}">
    <span class="ecl-button__container">
        <span class="ecl-button__label" data-ecl-label="true">{{ $label }}</span>&nbsp;
        <svg class="ecl-icon ecl-icon--xs ecl-button__icon ecl-button__icon--after"
             focusable="false"
             aria-hidden="true"
             data-ecl-icon=""><x-ecl.icon icon="search"/></svg>
    </span>
</button>
