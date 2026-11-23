@props([
    'label' => 'label',
    'unique' => uniqid(),
    'open' => false
])
<div class="ecl-accordion ecl-u-mt-l" data-ecl-auto-init="Accordion">
    <details class="ecl-accordion__item is-first" @if($open) open @endif>
        <summary class="ecl-accordion__toggle">
            {{ $label }}
            <svg class="ecl-icon ecl-icon--s ecl-accordion__toggle-icon"
                 focusable="false" aria-hidden="true">
                <x-ecl.icon icon="plus" />
            </svg>
            <svg class="ecl-icon ecl-icon--s ecl-accordion__toggle-icon"
                 focusable="false" aria-hidden="true">
                <x-ecl.icon icon="minus" />
            </svg>
        </summary>
        <div class="ecl-accordion__content">
            {{ $slot }}
        </div>
    </details>
</div>
