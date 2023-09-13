@props(['color' => 'ecl-u-type-color-blue-75', 'icon' => '', 'link' => '#', 'title' => ''])
<div>
    <a class="ecl-button ecl-button--ghost ecl-u-type-color-blue-120 ecl-site-header__login-toggle"
       data-ecl-login-toggle="true"
       aria-controls="login-box-id" aria-expanded="false"
       href="{{$link}}">
        @if($icon !== '')
            <svg
                class="ecl-icon ecl-icon--s {{$color}} ecl-u-mr-s"
                focusable="false" aria-hidden="true">
                <x-ecl.icon icon="{{$icon}}"/>
            </svg>
        @endif
        {{$title}}</a>
</div>

