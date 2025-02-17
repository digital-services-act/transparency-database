@props(['label' => 'label', 'unique' => uniqid()])
<div class="ecl-expandable" data-ecl-expandable="true" data-ecl-auto-init="Expandable">
    <button class="ecl-button ecl-button--ghost ecl-expandable__toggle" type="button"
        aria-controls="expandable-{{ $unique }}" data-ecl-expandable-toggle=""
        data-ecl-label-expanded="{{ $label }}" data-ecl-label-collapsed="{{ $label }}" aria-expanded="false">
        <span class="ecl-button__container">
            <span class="ecl-button__label" data-ecl-label="true">{{ $label }}</span>
            <svg class="ecl-icon ecl-icon--fluid ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                focusable="false" aria-hidden="true" data-ecl-icon="">
                <x-ecl.icon icon="corner-arrow" />
            </svg>
        </span>
    </button>
    <div id="expandable-{{ $unique }}" class="ecl-expandable__content" hidden="">
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
