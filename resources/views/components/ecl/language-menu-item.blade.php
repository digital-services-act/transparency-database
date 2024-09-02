@props(['lang','label'])
<li class="ecl-site-header__language-item">
    @if(strtoupper(request()->query('lang')) === $lang)

        <a href="?lang={{ strtolower($lang) }}"
           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link ecl-site-header__language-link--active"
           lang="{{ strtolower($lang) }}" hreflang="{{ strtolower($lang) }}">
            <span class="ecl-site-header__language-link-code">{{ $lang }}</span>
            <span class="ecl-site-header__language-link-label">{{ $label }}</span>
        </a>

    @else

        <a href="?lang={{ strtolower($lang) }}"
           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
           lang="{{ strtolower($lang) }}" hreflang="{{ strtolower($lang) }}">
            <span class="ecl-site-header__language-link-code">{{ $lang }}</span>
            <span class="ecl-site-header__language-link-label">{{$label}}</span>
        </a>

    @endif

</li>


