@props(['url','title'])
<div class="ecl-u-pt-l ecl-u-d-inline-flex ecl-u-align-items-center">

    <div class="ecl-u-type-paragraph ecl-u-mr-l">

        <a href="mailto:CNECT-DIGITAL-SERVICES@ec.europa.eu?subject=report an issue&body=Please describe the issue with the content from the page: {{$url}}" class="ecl-link ecl-link--default ecl-link--icon ecl-link--icon-after">
            <span class="ecl-link__label">{{$title}}</span>
            <svg class="ecl-icon ecl-icon--s ecl-link__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="email">
                </x-ecl.icon>
            </svg>

        </a>
    </div>
</div>
