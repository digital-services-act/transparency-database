<header class="ecl-site-header" data-ecl-auto-init="SiteHeader">
    <div class="ecl-site-header__background">
        <div class="ecl-site-header__header">
            <div class="ecl-site-header__container ecl-container">
                <div class="ecl-site-header__top" data-ecl-site-header-top>
                    <a href="{{ route('home') }}" class="ecl-link ecl-link--standalone ecl-site-header__logo-link"
                       aria-label="European Commission">
                        <img alt="European Commission logo"
                             title="European Commission"
                             class="ecl-site-header__logo-image ecl-site-header__logo-image-desktop"
                             src="{{asset('static/ecl/images/logo/positive/logo-ec--en.svg')}}">
                    </a>
                    <div class="ecl-site-header__action">

                        <div class="ecl-site-header__language"><a
                                class="ecl-button ecl-button--tertiary ecl-site-header__language-selector"
                                href="/component-library/example#k5ng8"
                                data-ecl-language-selector
                                role="button"
                                aria-label="Change language, current language is English"
                                aria-controls="language-list-overlay"
                            ><span class="ecl-site-header__language-icon"><svg
                                        class="ecl-icon ecl-icon--s ecl-site-header__icon"
                                        focusable="false"
                                        aria-hidden="false"
                                        role="img"><title
                                        >EN</title>
                                        <x-ecl.icon icon="global"/>
                                        </svg></span>EN</a>
                            <div class="ecl-site-header__language-container" id="language-list-overlay" hidden
                                 data-ecl-language-list-overlay aria-labelledby="ecl-site-header__language-title"
                                 role="dialog">
                                <div class="ecl-site-header__language-header">
                                    <div
                                        class="ecl-site-header__language-title"
                                        id="ecl-site-header__language-title"
                                    >Select your language
                                    </div>
                                    <button
                                        class="ecl-button ecl-button--tertiary ecl-site-header__language-close ecl-button--icon-only"
                                        type="submit" data-ecl-language-list-close><span
                                            class="ecl-button__container"><span class="ecl-button__label"
                                                                                data-ecl-label="true">Close</span><svg
                                                class="ecl-icon ecl-icon--m ecl-button__icon"
                                                focusable="false"
                                                aria-hidden="true"
                                                data-ecl-icon><use
                                                    xlink:href="https://v3--europa-component-library.netlify.app/playground/ec/images/icons/sprites/icons.svg#close"></use></svg></span>
                                    </button>
                                </div>
                                <div class="ecl-site-header__language-content">
                                    <div class="ecl-site-header__language-category" data-ecl-language-list-eu>
                                        <div class="ecl-site-header__language-category-title">Official EU languages:
                                        </div>
                                        <ul class="ecl-site-header__language-list">
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#t2wx8"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="bg" hreflang="bg"
                                                ><span class="ecl-site-header__language-link-code">bg</span><span
                                                        class="ecl-site-header__language-link-label">български</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#trbga"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="es" hreflang="es"
                                                ><span class="ecl-site-header__language-link-code">es</span><span
                                                        class="ecl-site-header__language-link-label">español</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#ymj02"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="cs" hreflang="cs"
                                                ><span class="ecl-site-header__language-link-code">cs</span><span
                                                        class="ecl-site-header__language-link-label">čeština</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#awr16"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="da" hreflang="da"
                                                ><span class="ecl-site-header__language-link-code">da</span><span
                                                        class="ecl-site-header__language-link-label">dansk</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#33hd3"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="de" hreflang="de"
                                                ><span class="ecl-site-header__language-link-code">de</span><span
                                                        class="ecl-site-header__language-link-label">Deutsch</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#st7k1"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="et" hreflang="et"
                                                ><span class="ecl-site-header__language-link-code">et</span><span
                                                        class="ecl-site-header__language-link-label">eesti</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#tpigq"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="el" hreflang="el"
                                                ><span class="ecl-site-header__language-link-code">el</span><span
                                                        class="ecl-site-header__language-link-label">ελληνικά</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#71ire"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link ecl-site-header__language-link--active"
                                                    lang="en" hreflang="en"
                                                ><span class="ecl-site-header__language-link-code">en</span><span
                                                        class="ecl-site-header__language-link-label">English</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#4sb9t"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="fr" hreflang="fr"
                                                ><span class="ecl-site-header__language-link-code">fr</span><span
                                                        class="ecl-site-header__language-link-label">français</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#9dgt5"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="ga" hreflang="ga"
                                                ><span class="ecl-site-header__language-link-code">ga</span><span
                                                        class="ecl-site-header__language-link-label">Gaeilge</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#anlk5"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="hr" hreflang="hr"
                                                ><span class="ecl-site-header__language-link-code">hr</span><span
                                                        class="ecl-site-header__language-link-label">hrvatski</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#cu1su"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="it" hreflang="it"
                                                ><span class="ecl-site-header__language-link-code">it</span><span
                                                        class="ecl-site-header__language-link-label">italiano</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#bd6xd"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="lv" hreflang="lv"
                                                ><span class="ecl-site-header__language-link-code">lv</span><span
                                                        class="ecl-site-header__language-link-label">latviešu</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#ii36c"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="lt" hreflang="lt"
                                                ><span class="ecl-site-header__language-link-code">lt</span><span
                                                        class="ecl-site-header__language-link-label">lietuvių</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#5pja1"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="hu" hreflang="hu"
                                                ><span class="ecl-site-header__language-link-code">hu</span><span
                                                        class="ecl-site-header__language-link-label">magyar</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#ff4v7"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="mt" hreflang="mt"
                                                ><span class="ecl-site-header__language-link-code">mt</span><span
                                                        class="ecl-site-header__language-link-label">Malti</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#rwueb"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="nl" hreflang="nl"
                                                ><span class="ecl-site-header__language-link-code">nl</span><span
                                                        class="ecl-site-header__language-link-label">Nederlands</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#hs5xb"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="pl" hreflang="pl"
                                                ><span class="ecl-site-header__language-link-code">pl</span><span
                                                        class="ecl-site-header__language-link-label">polski</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#njbgr"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="pt" hreflang="pt"
                                                ><span class="ecl-site-header__language-link-code">pt</span><span
                                                        class="ecl-site-header__language-link-label">português</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#uvppm"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="ro" hreflang="ro"
                                                ><span class="ecl-site-header__language-link-code">ro</span><span
                                                        class="ecl-site-header__language-link-label">română</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#quu5t"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="sk" hreflang="sk"
                                                ><span class="ecl-site-header__language-link-code">sk</span><span
                                                        class="ecl-site-header__language-link-label">slovenčina</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#81qx7"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="sl" hreflang="sl"
                                                ><span class="ecl-site-header__language-link-code">sl</span><span
                                                        class="ecl-site-header__language-link-label">slovenščina</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#izqwu"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="fi" hreflang="fi"
                                                ><span class="ecl-site-header__language-link-code">fi</span><span
                                                        class="ecl-site-header__language-link-label">suomi</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#gyvr3"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="sv" hreflang="sv"
                                                ><span class="ecl-site-header__language-link-code">sv</span><span
                                                        class="ecl-site-header__language-link-label">svenska</span></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="ecl-site-header__language-category" data-ecl-language-list-non-eu>
                                        <div class="ecl-site-header__language-category-title">Other languages:</div>
                                        <ul class="ecl-site-header__language-list">
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#yhzs9"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="ar" hreflang="ar"
                                                ><span class="ecl-site-header__language-link-code">ar</span><span
                                                        class="ecl-site-header__language-link-label">عَرَبِيّ</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#ujm30"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="ca" hreflang="ca"
                                                ><span class="ecl-site-header__language-link-code">ca</span><span
                                                        class="ecl-site-header__language-link-label">Català</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#o8hnn"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="is" hreflang="is"
                                                ><span class="ecl-site-header__language-link-code">is</span><span
                                                        class="ecl-site-header__language-link-label">Íslenska</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#gbz02"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="lb" hreflang="lb"
                                                ><span class="ecl-site-header__language-link-code">lb</span><span
                                                        class="ecl-site-header__language-link-label">Lëtzebuergesch</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#d3ae0"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="ja" hreflang="ja"
                                                ><span class="ecl-site-header__language-link-code">ja</span><span
                                                        class="ecl-site-header__language-link-label">日本語</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#hhq0r"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="nb" hreflang="nb"
                                                ><span class="ecl-site-header__language-link-code">nb</span><span
                                                        class="ecl-site-header__language-link-label">Norsk bokmål</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#ctkse"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="ru" hreflang="ru"
                                                ><span class="ecl-site-header__language-link-code">ru</span><span
                                                        class="ecl-site-header__language-link-label">русский язык</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#a09oa"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="tr" hreflang="tr"
                                                ><span class="ecl-site-header__language-link-code">tr</span><span
                                                        class="ecl-site-header__language-link-label">Türkçe</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#5wprk"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="uk" hreflang="uk"
                                                ><span class="ecl-site-header__language-link-code">uk</span><span
                                                        class="ecl-site-header__language-link-label">українська мова</span></a>
                                            </li>
                                            <li class="ecl-site-header__language-item"><a
                                                    href="/component-library/example#idwni"
                                                    class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-site-header__language-link"
                                                    lang="zh" hreflang="zh"
                                                ><span class="ecl-site-header__language-link-code">zh</span><span
                                                        class="ecl-site-header__language-link-label">中文</span></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ecl-site-header-core__login-container">

                                                    <div class="ecl-u-d-flex ecl-u-justify-content-end">

                                                        <div class="">
                                                            @guest
                                                                <a class="ecl-button ecl-button--ghost ecl-site-header-core__login-toggle"
                                                                   href="{{ route('profile.start') }}">
                                                                    <svg class="ecl-icon ecl-icon--s ecl-site-header-core__icon" focusable="false"
                                                                         aria-hidden="true">
                                                                        <x-ecl.icon icon="log-in"/>
                                                                    </svg>
                                                                    Log In</a>
                                                            @endguest
                                                            @auth
                                                                <div class="ecl-site-header__login-container">
                                                                    <a class="ecl-button ecl-button--ghost ecl-site-header__login-toggle" href="#"
                                                                       data-ecl-login-toggle="true"
                                                                       aria-controls="login-box-id" aria-expanded="false">
                                                                        <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-0 ecl-site-header__icon"
                                                                             focusable="false" aria-hidden="true">
                                                                            <x-ecl.icon icon="logged-in"/>
                                                                        </svg>
                                                                        Logged in
                                                                        <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-0 ecl-site-header__login-arrow"
                                                                             focusable="false" aria-hidden="true">
                                                                            <x-ecl.icon icon="corner-arrow"/>
                                                                        </svg>
                                                                    </a>

                                                                    <div id="login-box-id" class="ecl-site-header__login-box"
                                                                         data-ecl-login-box="true">
                                                                        <x-ecl.menu-item icon="log-in" :link="route('profile.start')" title="Your Profile" />
                                                                        @can('create statements')
                                                                            <x-ecl.menu-item icon="gear" :link="route('profile.api.index')" title="API Token Management" />
                                                                        @endcan
                                                                        <hr class="ecl-site-header__login-separator">
                                                                        <x-ecl.menu-item link="/logout" title="Logout" />
                                                                    </div>
                                                                </div>
                                                            @endauth
                                                        </div>
                                                    </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

      <div class="ecl-site-header__banner">
        <div class="ecl-container">
            <div class="ecl-site-header__site-name">DSA Transparency Database</div>
        </div>
    </div>

    <nav class="ecl-menu ecl-menu--group1" data-ecl-menu="" data-ecl-auto-init="Menu" aria-expanded="false">
        <div class="ecl-menu__overlay" data-ecl-menu-overlay=""></div>

        <div class="ecl-container ecl-menu__container">
            <a class="ecl-link ecl-link--standalone ecl-menu__open" href="#"
               data-ecl-menu-open="">
                <svg class="ecl-icon ecl-icon--s" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="hamburger"/>
                </svg>
                Menu
            </a>

            <div class="ecl-menu__inner" data-ecl-menu-inner="">
                <div class="ecl-menu__inner-header">
                    <button class="ecl-menu__close ecl-button ecl-button--text" type="submit" data-ecl-menu-close="">
                            <span class="ecl-menu__close-container ecl-button__container">
                                <svg class="ecl-icon ecl-icon--s ecl-button__icon ecl-button__icon--before"
                                     focusable="false" aria-hidden="true" data-ecl-icon="">
                                    <x-ecl.icon icon="close-filled"/>
                                </svg>
                                <span class="ecl-button__label" data-ecl-label="true">Close</span>
                            </span>
                    </button>

                    <div class="ecl-menu__title">Menu</div>
                    <button data-ecl-menu-back="" type="submit" class="ecl-menu__back ecl-button ecl-button--text">
                                <span class="ecl-button__container">
                                    <svg
                                        class="ecl-icon ecl-icon--s ecl-icon--rotate-270 ecl-button__icon ecl-button__icon--before"
                                        focusable="false" aria-hidden="true" data-ecl-icon="">
                                        <x-ecl.icon icon="corner-arrow"/>
                                    </svg>
                                    <span class="ecl-button__label" data-ecl-label="">Back</span>
                                </span>
                    </button>
                </div>

                <div class="ecl-u-d-flex ecl-u-justify-content-between">
                    <div>
                        <ul class="ecl-menu__list">

                            <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">
                                <a href="{{route('home')}}" class="ecl-menu__link"
                                   data-ecl-menu-link="{{route('home')}}">Home</a>
                            </li>
                            <li class="ecl-menu__item ecl-menu__item--has-children" data-ecl-menu-item=""
                                data-ecl-has-children=""
                                aria-expanded="false" id="ecl-menu-item-database"><a
                                    href="#"
                                    class="ecl-menu__link" data-ecl-menu-link="" id="ecl-menu-item-database-link">The
                                    Database</a>
                                <button class="ecl-button ecl-button--primary ecl-menu__button-caret" type="button"
                                        data-ecl-menu-caret="" aria-label="Access item&#x27;s children"
                                        aria-expanded="false">
                                    <span class="ecl-button__container">
                                        <svg
                                            class="ecl-icon ecl-icon--xs ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                                            focusable="false" aria-hidden="true" data-ecl-icon="">
                                            <x-ecl.icon icon="corner-arrow"/>
                                        </svg>
                                    </span>
                                </button>
                                <div class="ecl-menu__mega" data-ecl-menu-mega="">
                                    <ul class="ecl-menu__sublist">
                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                            <a href="{{route('dashboard')}}"
                                               class="ecl-menu__sublink">Dashboard</a>
                                        </li>
                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                            <a href="{{route('dayarchive.index')}}"
                                               class="ecl-menu__sublink">Data Download</a>
                                        </li>
                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                            <a href="{{route('statement.index')}}"
                                               class="ecl-menu__sublink">Search for Statements of Reasons</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="ecl-menu__item ecl-menu__item--has-children" data-ecl-menu-item=""
                                data-ecl-has-children=""
                                aria-expanded="false" id="ecl-menu-item-faq"><a
                                    href="#"
                                    class="ecl-menu__link" data-ecl-menu-link="" id="ecl-menu-item-faq-link">FAQ</a>
                                <button class="ecl-button ecl-button--primary ecl-menu__button-caret" type="button"
                                        data-ecl-menu-caret="" aria-label="Access item&#x27;s children"
                                        aria-expanded="false">
                                    <span class="ecl-button__container">
                                        <svg
                                            class="ecl-icon ecl-icon--xs ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                                            focusable="false" aria-hidden="true" data-ecl-icon="">
                                            <x-ecl.icon icon="corner-arrow"/>
                                        </svg>
                                    </span>
                                </button>
                                <div class="ecl-menu__mega" data-ecl-menu-mega="">
                                    <ul class="ecl-menu__sublist">
                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                                href="{{ route('page.show', ['faq#general-faq']) }}"
                                                class="ecl-menu__sublink">General</a></li>
                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                                href="{{ route('page.show', ['faq#technical-faq']) }}"
                                                class="ecl-menu__sublink">Technical</a></li>
                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                                href="{{ route('page.show', ['faq#platform-faq']) }}"
                                                class="ecl-menu__sublink">Platforms</a></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <ul class="ecl-menu__list">
                            <li class="ecl-menu__item ecl-menu__item--has-children" data-ecl-menu-item=""
                                data-ecl-has-children=""
                                aria-expanded="false" id="ecl-menu-item-platforms"><a
                                    href="#"
                                    class="ecl-menu__link" data-ecl-menu-link="" id="ecl-menu-item-platforms-link">Platforms</a>
                                <button class="ecl-button ecl-button--primary ecl-menu__button-caret" type="button"
                                        data-ecl-menu-caret="" aria-label="Access item&#x27;s children"
                                        aria-expanded="false">
                            <span class="ecl-button__container">
                                <svg
                                    class="ecl-icon ecl-icon--xs ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                                    focusable="false"
                                    aria-hidden="true"
                                    data-ecl-icon="">
                                    <x-ecl.icon icon="corner-arrow"/>
                                </svg>
                            </span>
                                </button>

                                <div class="ecl-menu__mega" data-ecl-menu-mega="">
                                    <ul class="ecl-menu__sublist">
                                        @can('create statements')
                                            <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                                <a href="{{ route('statement.create') }}"
                                                   class="ecl-menu__sublink">
                                                    Submit statements of reasons
                                                </a>
                                            </li>
                                        @endcan

                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                            <a href="{{  route('profile.page.show', ['documentation']) }}"
                                               class="ecl-menu__sublink">
                                                Global Documentation
                                            </a>
                                        </li>
                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                            <a href="{{  route('profile.page.show', ['api-documentation']) }}"
                                               class="ecl-menu__sublink">API Documentation</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
