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
                                       href="{{ route('dashboard') }}">
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
                                            @can('create statements')

                                                <x-ecl.menu-item icon="gear" :link="route('api-index')"
                                                                 title="API Token Management"/>
                                                <x-ecl.menu-item icon="growth"
                                                                 :link="route('analytics.platform',auth()->user()->platform->uuid)"
                                                                 title="Your Platform Analytics"/>
                                            @endcan

                                            <hr class="ecl-site-header__login-separator"/>
                                            <x-ecl.menu-item :link="route('logout')" title="Logout"/>

                                            <br/>
                                        </div>

                                    </div>
                                    @endauth

                                    </a>
                            </div>

                            {{--                            <div class="">--}}
                            {{--                                @auth--}}
                            {{--                                    <x-impersonate />--}}
                            {{--                                @endauth--}}
                            {{--                            </div>--}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ecl-site-header__banner">
        <div class="ecl-container">
            <div class="ecl-site-header__site-name">DSA Transparency Database @env('staging')
                    (Sandbox)
                @endenv</div>
        </div>
    </div>

    <nav class="ecl-menu ecl-menu--group1" data-ecl-menu="" data-ecl-auto-init="Menu" aria-expanded="false">
        <div class="ecl-menu__overlay" data-ecl-menu-overlay=""></div>

        <div class="ecl-container ecl-menu__container">
            <a class="ecl-link ecl-link--standalone ecl-menu__open" href="/component-library/example"
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
                        <a href="{{route('home')}}" class="ecl-menu__link" data-ecl-menu-link="">Home</a>
                    </li>
                    @can('view dashboard')
                        <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">
                            <a href="{{route('statement.index')}}" class="ecl-menu__link" data-ecl-menu-link="">Statements of Reasons</a>
                        </li>
                    @endcan

                    @can('administrate')
                        <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">
                            <a href="{{route('analytics.index')}}" class="ecl-menu__link" data-ecl-menu-link="">Analytics</a>
                        </li>
                    @endcan

                    @can('view dashboard')
                        <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false"><a
                                href="{{route('dashboard')}}" class="ecl-menu__link"
                                data-ecl-menu-link="">Dashboard</a>
                        </li>
                    @endcan

                    <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">
                        <a
                            href="{{ route('page.show', ['api-documentation']) }}"
                            class="ecl-menu__link" data-ecl-menu-link="">Documentation</a>
                    </li>

                </ul>
            </section>
        </div>
    </nav>
</header>
