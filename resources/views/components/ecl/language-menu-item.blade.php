@props(['lang','label'])
<li class="ecl-site-header__language-item">
    @if(app()->getLocale() == $lang)

        <a href="/setlocale/?locale={{$lang}}"
           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link ecl-site-header__language-link--active"
           lang="{{$lang}}" hreflang="{{$lang}}">
            <span class="ecl-site-header__language-link-code">{{$lang}}</span>
            <span class="ecl-site-header__language-link-label">{{$label}}</span>
        </a>

    @else

        <a href="/setlocale/?locale={{$lang}}"
           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
           lang="{{$lang}}" hreflang="{{$lang}}">
            <span class="ecl-site-header__language-link-code">{{$lang}}</span>
            <span class="ecl-site-header__language-link-label">{{$label}}</span>
        </a>

    @endif

</li>


