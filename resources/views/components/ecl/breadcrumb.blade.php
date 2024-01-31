@props(['url' => false, 'label' => 'label'])
<li class="ecl-breadcrumb__segment @if(!$url)ecl-breadcrumb__current-page @endif" data-ecl-breadcrumb-item="static">
    @if($url)
    <a href="{{ $url }}" class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-breadcrumb__link">{{ $label }}</a>
    <svg class="ecl-icon ecl-icon--2xs ecl-icon--rotate-90 ecl-breadcrumb__icon" focusable="false" aria-hidden="true" role="presentation">
        <x-ecl.icon icon="corner-arrow" />
    </svg>
    @else
    <li class="ecl-breadcrumb__segment ecl-breadcrumb__current-page" data-ecl-breadcrumb-item="static" aria-current="page">{{ $label }}</li>
    @endif
