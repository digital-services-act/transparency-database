<div class="ecl-site-header__language">
    <a class="ecl-button ecl-button--tertiary ecl-site-header__language-selector"
       href="{{ route('home') }}" data-ecl-language-selector role="button"
       aria-controls="language-list-overlay">
        <span class="ecl-site-header__language-icon">
            <svg class="ecl-icon ecl-icon--s ecl-site-header__icon" focusable="false"
                 aria-hidden="false" role="img">
                <title>{{ request()->query('lang', 'en') }}</title>
                <x-ecl.icon incon="global"/>
            </svg>
        </span>
        {{ request()->query('lang', 'en') }}
    </a>
    <div class="ecl-site-header__language-container" id="language-list-overlay" hidden
         data-ecl-language-list-overlay aria-labelledby="ecl-site-header__language-title"
         role="dialog">
        <div class="ecl-site-header__language-header">
            <div class="ecl-site-header__language-title" id="ecl-site-header__language-title">
                Select your language
            </div>
            <button class="ecl-button ecl-button--tertiary ecl-site-header__language-close ecl-button--icon-only"
                    type="submit" data-ecl-language-list-close>
                <span class="ecl-button__container">
                    <span class="ecl-button__label" data-ecl-label="true">Close</span>
                    <svg class="ecl-icon ecl-icon--m ecl-button__icon" focusable="false"
                         aria-hidden="true" data-ecl-icon>
                        <x-ecl.icon icon="close"/>
                    </svg>
                </span>
            </button>
        </div>
        <div class="website-translator">
            <div class="ecl-site-header__language-content">
                <div class="ecl-site-header__language-category" data-ecl-language-list-eu>
                    <div class="ecl-site-header__language-category-title">Official EU languages:</div>
                    <ul class="ecl-site-header__language-list">

                        @foreach($languages as $lang => $label)
                            <x-ecl.language-menu-item lang="{{$lang}}" label="{{$label}}"></x-ecl.language-menu-item>
                        @endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
