@props(['label' => 'popover'])
<div class="ecl-popover" data-ecl-auto-init="Popover">
    <a href="#popover"
       class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-before ecl-popover__toggle"
       aria-controls="popover-example"
       data-ecl-popover-toggle=""
       aria-expanded="false"
       aria-label="Popover toggle"
    ><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="information" /></svg> <span class="ecl-link__label">{{ $label }}</span></a>
    <div id="popover-example" class="ecl-popover__container" hidden="">
        <div class="ecl-popover__content">
            {{ $slot }}
        </div>
    </div>
</div>