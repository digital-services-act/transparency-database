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



                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="es" hreflang="es">
                            <span class="ecl-site-header__language-link-code">es</span>
                            <span class="ecl-site-header__language-link-label">español</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="cs" hreflang="cs">
                            <span class="ecl-site-header__language-link-code">cs</span>
                            <span class="ecl-site-header__language-link-label">čeština</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="da" hreflang="da">
                            <span class="ecl-site-header__language-link-code">da</span>
                            <span class="ecl-site-header__language-link-label">dansk</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="de" hreflang="de">
                            <span class="ecl-site-header__language-link-code">de</span>
                            <span class="ecl-site-header__language-link-label">Deutsch</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="et" hreflang="et">
                            <span class="ecl-site-header__language-link-code">et</span>
                            <span class="ecl-site-header__language-link-label">eesti</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="el" hreflang="el">
                            <span class="ecl-site-header__language-link-code">el</span>
                            <span class="ecl-site-header__language-link-label">ελληνικά</span>
                        </a>
                    </li>

                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="ga" hreflang="ga">
                            <span class="ecl-site-header__language-link-code">ga</span>
                            <span class="ecl-site-header__language-link-label">Gaeilge</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="hr" hreflang="hr">
                            <span class="ecl-site-header__language-link-code">hr</span>
                            <span class="ecl-site-header__language-link-label">hrvatski</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="it" hreflang="it">
                            <span class="ecl-site-header__language-link-code">it</span>
                            <span class="ecl-site-header__language-link-label">italiano</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="lv" hreflang="lv">
                            <span class="ecl-site-header__language-link-code">lv</span>
                            <span class="ecl-site-header__language-link-label">latviešu</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="lt" hreflang="lt">
                            <span class="ecl-site-header__language-link-code">lt</span>
                            <span class="ecl-site-header__language-link-label">lietuvių</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="hu" hreflang="hu">
                            <span class="ecl-site-header__language-link-code">hu</span>
                            <span class="ecl-site-header__language-link-label">magyar</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="mt" hreflang="mt">
                            <span class="ecl-site-header__language-link-code">mt</span>
                            <span class="ecl-site-header__language-link-label">Malti</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="nl" hreflang="nl">
                            <span class="ecl-site-header__language-link-code">nl</span>
                            <span class="ecl-site-header__language-link-label">Nederlands</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="pl" hreflang="pl">
                            <span class="ecl-site-header__language-link-code">pl</span>
                            <span class="ecl-site-header__language-link-label">polski</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="pt" hreflang="pt">
                            <span class="ecl-site-header__language-link-code">pt</span>
                            <span class="ecl-site-header__language-link-label">português</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="ro" hreflang="ro">
                            <span class="ecl-site-header__language-link-code">ro</span>
                            <span class="ecl-site-header__language-link-label">română</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="sk" hreflang="sk">
                            <span class="ecl-site-header__language-link-code">sk</span>
                            <span class="ecl-site-header__language-link-label">slovenčina</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="sl" hreflang="sl">
                            <span class="ecl-site-header__language-link-code">sl</span>
                            <span class="ecl-site-header__language-link-label">slovenščina</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="fi" hreflang="fi">
                            <span class="ecl-site-header__language-link-code">fi</span>
                            <span class="ecl-site-header__language-link-label">suomi</span>
                        </a>
                    </li>
                    <li class="ecl-site-header__language-item">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                           lang="sv" hreflang="sv">
                            <span class="ecl-site-header__language-link-code">sv</span>
                            <span class="ecl-site-header__language-link-label">svenska</span>
                        </a>
                    </li>
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
