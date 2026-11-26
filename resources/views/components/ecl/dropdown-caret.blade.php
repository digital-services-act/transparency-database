@props(['size' => 'xs', 'extraClasses' => ''])

<svg class="ecl-icon ecl-icon--{{ $size }} {{ $extraClasses }} ecl-dropdown-caret"
     focusable="false"
     aria-hidden="true"
     data-ecl-icon
     style="transform: rotate(180deg)">
    <x-ecl.icon icon="corner-arrow" />
</svg>
