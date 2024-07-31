@props(['url' => null, 'label' => 'label', 'priority' => 'cta', 'icon' => true, 'fullwidth' => false])
<button class="ecl-button ecl-button--{{ $priority }}" @if($fullwidth)style="width: 100%;" @endif
        type="submit" onClick="document.location.href = '{{ $url }}'">
    <span class="ecl-button__container">
        <span class="ecl-button__label @if($fullwidth) ecl-u-width-100 @endif" data-ecl-label="true">
            {!! $label !!}
        </span>
        @if($icon)
            <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-button__icon ecl-button__icon--after"
                 focusable="false"
                 aria-hidden="true"
                 data-ecl-icon="">
                <x-ecl.icon icon="corner-arrow" />
            </svg>
        @endif
    </span>
</button>