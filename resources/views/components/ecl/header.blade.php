<header class="ecl-site-header" data-ecl-auto-init="SiteHeader">
    <div class="ecl-site-header__header">
        <div class="ecl-site-header__container ecl-container">
            <div class="ecl-site-header__top">
                <a href="{{ route('home') }}" class="ecl-link ecl-link--standalone ecl-site-header__logo-link"
                   aria-label="European Commission">
                    <img alt="European Commission logo"
                         title="European Commission"
                         class="ecl-site-header__logo-image ecl-site-header__logo-image-desktop"
                         src="{{asset('static/ecl/images/logo/positive/logo-ec--en.svg')}}"/>
                </a>

                <div class="ecl-site-header__action">
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
                                            <svg
                                                class="ecl-icon ecl-icon--xs ecl-icon--rotate-0 ecl-site-header__login-arrow"
                                                focusable="false" aria-hidden="true">
                                                <x-ecl.icon icon="corner-arrow"/>
                                            </svg>
                                        </a>

                                        <div id="login-box-id" class="ecl-site-header__login-box"
                                             data-ecl-login-box="true">


                                            <x-ecl.menu-item icon="log-in" :link="route('profile.start')"
                                                             title="Your Profile"/>

                                            @can('create statements')

                                                <x-ecl.menu-item icon="gear" :link="route('profile.api.index')"
                                                                 title="API Token Management"/>

                                            @endcan

                                            <hr class="ecl-site-header__login-separator"/>
                                            <x-ecl.menu-item link="/logout" title="Logout"/>


                                            <br/>
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

            <section class="ecl-menu__inner" data-ecl-menu-inner="">
                <header class="ecl-menu__inner-header">
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
                </header>


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
                                aria-describedby="ecl-menu-item-demo-2-link"
                                aria-expanded="false"><span class="ecl-button__container"><svg
                                    class="ecl-icon ecl-icon--xs ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                                    focusable="false" aria-hidden="true" data-ecl-icon="">
                                    <x-ecl.icon icon="corner-arrow"/>


              </svg></span></button>
                        <div class="ecl-menu__mega" data-ecl-menu-mega="">
                            <ul class="ecl-menu__sublist">
                                <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                        href="{{route('dashboard')}}"
                                        class="ecl-menu__sublink">Dashboard</a></li>
                                <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                        href="{{route('dayarchive.index')}}"
                                        class="ecl-menu__sublink">Data Download</a></li>
{{--                                <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a--}}
{{--                                        href="{{route('analytics.index')}}"--}}
{{--                                        class="ecl-menu__sublink">Analytics</a></li>--}}
                                <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                        href="{{route('statement.index')}}"
                                        class="ecl-menu__sublink">Search for Statements of Reasons</a></li>
                            </ul>
                        </div>
                    </li>


                    {{--                    <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">--}}
                    {{--                        <a href="{{route('analytics.index')}}" class="ecl-menu__link"--}}
                    {{--                           data-ecl-menu-link="{{route('analytics.index')}}">Analytics</a>--}}
                    {{--                    </li>--}}

                    {{--                    <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">--}}
                    {{--                        <a href="{{route('dayarchive.index')}}" class="ecl-menu__link"--}}
                    {{--                           data-ecl-menu-link="{{route('dayarchive.index')}}">Data Download</a>--}}
                    {{--                    </li>--}}

                    {{--                    <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">--}}
                    {{--                        <a href="{{ route('page.show', ['faq']) }}" class="ecl-menu__link"--}}
                    {{--                           data-ecl-menu-link="{{ route('page.show', ['faq']) }}">FAQ</a>--}}
                    {{--                    </li>--}}

                    <li class="ecl-menu__item ecl-menu__item--has-children" data-ecl-menu-item=""
                        data-ecl-has-children=""
                        aria-expanded="false" id="ecl-menu-item-faq"><a
                            href="#"
                            class="ecl-menu__link" data-ecl-menu-link="" id="ecl-menu-item-faq-link">FAQ</a>
                        <button class="ecl-button ecl-button--primary ecl-menu__button-caret" type="button"
                                data-ecl-menu-caret="" aria-label="Access item&#x27;s children"
                                aria-describedby="ecl-menu-item-demo-2-link"
                                aria-expanded="false"><span class="ecl-button__container"><svg
                                    class="ecl-icon ecl-icon--xs ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                                    focusable="false" aria-hidden="true" data-ecl-icon="">
                                    <x-ecl.icon icon="corner-arrow"/>

              </svg></span></button>
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

                <ul class="ecl-menu__list ecl-u-f-r" style="margin-top:-52px">

                    <li class="ecl-menu__item ecl-menu__item--has-children" data-ecl-menu-item=""
                        data-ecl-has-children=""
                        aria-expanded="false" id="ecl-menu-item-platforms"><a
                            href="#"
                            class="ecl-menu__link" data-ecl-menu-link="" id="ecl-menu-item-platforms-link">Platforms</a>
                        <button class="ecl-button ecl-button--primary ecl-menu__button-caret" type="button"
                                data-ecl-menu-caret="" aria-label="Access item&#x27;s children"
                                aria-describedby="ecl-menu-item-demo-2-link"
                                aria-expanded="false"><span class="ecl-button__container"><svg
                                    class="ecl-icon ecl-icon--xs ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                                    focusable="false" aria-hidden="true" data-ecl-icon="">
                                    <x-ecl.icon icon="corner-arrow"/>

              </svg></span></button>
                        <div class="ecl-menu__mega" data-ecl-menu-mega="">
                            <ul class="ecl-menu__sublist">
                                @can('create statements')
                                <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                        href="{{ route('statement.create') }}"
                                        class="ecl-menu__sublink">Submit statements of reasons</a></li>
                                @endcan
                                <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                        href="{{  route('profile.page.show', ['documentation']) }}"
                                        class="ecl-menu__sublink">Global Documentation</a></li>
                                <li class="ecl-menu__subitem" data-ecl-menu-subitem=""><a
                                        href="{{  route('profile.page.show', ['api-documentation']) }}"
                                        class="ecl-menu__sublink">API Documentation</a></li>
                            </ul>
                        </div>
                    </li>

                </ul>
            </section>
        </div>
    </nav>
</header>
