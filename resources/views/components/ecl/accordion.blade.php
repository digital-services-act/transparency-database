@props(['label' => 'label', 'unique' => uniqid()])
<div class="ecl-accordion ecl-u-mt-l" data-ecl-auto-init="Accordion" data-ecl-accordion="" id="accordion-{{ $unique }}">
    <div class="ecl-accordion__item">
        <h3 class="ecl-accordion__title">
            <button type="button"
                    class="ecl-accordion__toggle"
                    data-ecl-accordion-toggle=""
                    data-ecl-label-expanded="Close"
                    data-ecl-label-collapsed="Open"
                    aria-controls="accordion-content-{{ $unique }}">
                <span class="ecl-accordion__toggle-flex">
                    <span class="ecl-accordion__toggle-indicator">
                        <span class="ecl-accordion__toggle-label">Open</span>
                        <svg class="ecl-icon ecl-icon--m ecl-accordion__toggle-icon" focusable="false" aria-hidden="true" data-ecl-accordion-icon="">
                            <x-ecl.icon icon="plus" />
                        </svg>
                    </span>
                    <span class="ecl-accordion__toggle-title">
                        {{ $label }}
                    </span>
                </span>
            </button>
        </h3>
        <div class="ecl-accordion__content" hidden="" id="accordion-content-{{ $unique }}" role="region">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', (event) => {
    var elt = document.querySelector('#accordion-{{ $unique }}');
    var accordion = new ECL.Accordion(elt);
    accordion.init();
  })
</script>