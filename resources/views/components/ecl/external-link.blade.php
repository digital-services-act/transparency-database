@props(['href' => '', 'label' => 'label'])
<a href="{{ $href }}"
   class="ecl-link ecl-link--default ecl-link--icon ecl-link--icon-after"
   aria-label="{{ $label }}">
    <span class="ecl-link__label">{{ $label }}</span>
    <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
        <x-ecl.icon icon="external" />
    </svg>
</a>