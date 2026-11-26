@props(['label' => 'label', 'unique' => uniqid()])
<div class="ecl-expandable" data-ecl-expandable="true" data-ecl-auto-init="Expandable">
    <button class="ecl-button ecl-button--tertiary ecl-expandable__toggle" type="button"
        aria-controls="expandable-{{ $unique }}-content" data-ecl-expandable-toggle=""
        data-ecl-label-expanded="{{ $label }}" data-ecl-label-collapsed="{{ $label }}" aria-expanded="false">
        <span class="ecl-button__container">
            <span class="ecl-button__label" data-ecl-label="true">{{ $label }}</span>
            <x-ecl.dropdown-caret size="fluid" extra-classes="ecl-button__icon" />
        </span>
    </button>
    <div id="expandable-{{ $unique }}-content" class="ecl-expandable__content" hidden="">
        {{ $slot }}
    </div>
</div>

{{-- <script> --}}
{{--  document.addEventListener('DOMContentLoaded', (event) => { --}}
{{--    var elt = document.querySelector('#expandable-{{ $unique }}') --}}
{{--    var expandable = new ECL.Expandable(elt); --}}
{{--    expandable.init(); --}}
{{--  }) --}}
{{-- </script> --}}
