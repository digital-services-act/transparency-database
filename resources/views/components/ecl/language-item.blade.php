@props(['lang'])
<div>
    @if(app()->getLocale() == $lang)
        <li class="ecl-language-list__item ecl-language-list__item--is-active"><a
                href="/setlocale/?locale={$lang}"
                class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after ecl-language-list__link"
                lang="{{$lang}}" hreflang="{{$lang}}" rel="alternate"><span
                    class="ecl-link__label">{{__("base.languages_menu.{$lang}")}}</span>
                <svg class="ecl-icon ecl-icon--xs ecl-link__icon" focusable="false"
                     aria-hidden="true">
                    <use xlink:href="{{asset('static/media/icons.148a2e16.svg#check')}}"></use>
                </svg>
            </a></li>
    @else
        <li class="ecl-language-list__item">
            <a class="ecl-link ecl-link--standalone ecl-language-list__link"
               href="/setlocale/?locale={{$lang}}">{{__("base.languages_menu.{$lang}")}}</a>
        </li>

    @endif
</div>
