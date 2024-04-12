<div class="ecl-site-header__language">
    <a class="ecl-button ecl-button--tertiary ecl-site-header__language-selector"
       href="{{ route('home') }}" data-ecl-language-selector role="button"
       aria-label="Change language, current language is English"
       aria-controls="language-list-overlay">
        <span class="ecl-site-header__language-icon">
            <svg class="ecl-icon ecl-icon--s ecl-site-header__icon" focusable="false"
                 aria-hidden="false" role="img">
                <title>{{app()->getLocale()}}</title>
                <x-ecl.icon incon="global"/>
            </svg>
        </span>
        {{app()->getLocale()}}
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
        <div class="ecl-site-header__language-content">
            <div class="ecl-site-header__language-category" data-ecl-language-list-eu>
                <div class="ecl-site-header__language-category-title">Official EU languages:</div>
                <ul class="ecl-site-header__language-list">

                    @foreach($languages as $lang => $label)
                        <x-ecl.language-menu-item lang="{{$lang}}" label="{{$label}}"></x-ecl.language-menu-item>
                    @endforeach




                </ul>
            </div>
            <div class="ecl-site-header__language-category" data-ecl-language-list-non-eu>
                <div class="ecl-site-header__language-category-title">Other languages:</div>
                <ul class="ecl-site-header__language-list">
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="ar" hreflang="ar">
                            <span class="ecl-site-header__language-link-code">ar</span>
                            <span class="ecl-site-header__language-link-label">عَرَبِيّ</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="ca" hreflang="ca">
                            <span class="ecl-site-header__language-link-code">ca</span>
                            <span class="ecl-site-header__language-link-label">Català</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="is" hreflang="is">
                            <span class="ecl-site-header__language-link-code">is</span>
                            <span class="ecl-site-header__language-link-label">Íslenska</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="lb" hreflang="lb">
                            <span class="ecl-site-header__language-link-code">lb</span>
                            <span class="ecl-site-header__language-link-label">Lëtzebuergesch</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="ja" hreflang="ja">
                            <span class="ecl-site-header__language-link-code">ja</span>
                            <span class="ecl-site-header__language-link-label">日本語</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="nb" hreflang="nb">
                            <span class="ecl-site-header__language-link-code">nb</span>
                            <span class="ecl-site-header__language-link-label">Norsk bokmål</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="ru" hreflang="ru">
                            <span class="ecl-site-header__language-link-code">ru</span>
                            <span class="ecl-site-header__language-link-label">русский язык</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="tr" hreflang="tr">
                            <span class="ecl-site-header__language-link-code">tr</span>
                            <span class="ecl-site-header__language-link-label">Türkçe</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="uk" hreflang="uk">
                            <span class="ecl-site-header__language-link-code">uk</span>
                            <span class="ecl-site-header__language-link-label">українська мова</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="zh" hreflang="zh">
                            <span class="ecl-site-header__language-link-code">zh</span>
                            <span class="ecl-site-header__language-link-label">中文</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
