<header class="ecl-site-header ecl-site-header--has-menu" data-ecl-auto-init="SiteHeader">
    <div class="ecl-site-header__background">
        <div class="ecl-site-header__header">
            <div class="ecl-site-header__container ecl-container">
                <div class="ecl-site-header__top" data-ecl-site-header-top>
                    <a href="{{ route('home') }}"
                       class="ecl-link ecl-link--standalone ecl-site-header__logo-link"
                       aria-label="European Commission">
                        <picture class="ecl-picture ecl-site-header__picture" title="European Commission">
                            <source srcset="{{asset('static/ecl/images/logo/positive/logo-ec--en.svg')}}"
                                    media="(min-width: 996px)">
                            <img class="ecl-site-header__logo-image"
                                 src="{{asset('static/ecl/images/logo/positive/logo-ec--en.svg')}}"
                                 alt="European Commission logo"/>
                        </picture>
                    </a>
                    <div class="ecl-site-header__action">
                        @if(config('dsa.webt.clientId'))
                        <div class="website-translator"></div>
                        @endif
                        {{--                        <x-ecl.search-site/>--}}
                        <x-ecl.login-menu/>
                    </div>
                </div>
            </div>
        </div>
        {{--        <x-ecl.notification-site/>--}}


        <div class="ecl-site-header__banner">
            <div class="ecl-container">
                <div class="ecl-site-header__site-name">{{__('app.title')}}</div>
            </div>
        </div>


        <nav class="ecl-menu"
             data-ecl-menu
             data-ecl-menu-max-lines="2"
             data-ecl-auto-init="Menu"
             data-ecl-menu-label-open="Menu"
             data-ecl-menu-label-close="Close"
             aria-expanded="false"
             role="navigation">
            <div class="ecl-menu__overlay"></div>
            <div class="ecl-container ecl-menu__container">


                <a href="{{ route('home') }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-button ecl-button--tertiary ecl-menu__open ecl-link--icon-only"
                   data-ecl-menu-open>
                    <svg class="ecl-icon ecl-icon--m ecl-link__icon" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="hamburger"/>
                    </svg>
                    <svg class="ecl-icon ecl-icon--m ecl-link__icon" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="close"/>
                    </svg>
                    <span class="ecl-link__label">Menu</span>
                </a>

                <section class="ecl-menu__inner" data-ecl-menu-inner role="application" aria-label="Menu">

                    <header class="ecl-menu__inner-header">

                        <button class="ecl-button ecl-button--ghost ecl-menu__back"
                                type="submit" data-ecl-menu-back>
                            <span class="ecl-button__container">
                                <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-270 ecl-button__icon"
                                     focusable="false" aria-hidden="true"
                                     data-ecl-icon>
                                    <x-ecl.icon icon="corner-arrow"/>
                                </svg>
                                <span class="ecl-button__label" data-ecl-label="true">Back</span>
                            </span>
                        </button>

                    </header>


                    <ul class="ecl-menu__list" data-ecl-menu-list>

                        <li class="ecl-menu__item" data-ecl-menu-item id="ecl-menu-home">
                            <a href="{{ route('home') }}"
                               class="ecl-link ecl-link--standalone ecl-menu__link"
                               data-ecl-menu-link
                               id="ecl-menu-home-link">{{__('menu.Home')}}</a>
                        </li>

                        <li class="ecl-menu__item" data-ecl-menu-item id="ecl-menu-home">
                            <a href="{{ route('dashboard') }}"
                               class="ecl-link ecl-link--standalone ecl-menu__link"
                               data-ecl-menu-link
                               id="ecl-menu-home-link">{{__('menu.Dashboard')}}</a>
                        </li>

                        <li class="ecl-menu__item" data-ecl-menu-item id="ecl-menu-home">
                            <a href="{{ route('dayarchive.index') }}"
                               class="ecl-link ecl-link--standalone ecl-menu__link"
                               data-ecl-menu-link
                               id="ecl-menu-home-link">{{__('menu.Data Download')}}</a>
                        </li>

                        <li class="ecl-menu__item" data-ecl-menu-item id="ecl-menu-home">
                            <a href="{{ route('statement.index') }}"
                               class="ecl-link ecl-link--standalone ecl-menu__link"
                               data-ecl-menu-link
                               id="ecl-menu-home-link">{{__('menu.Search for Statements of Reasons')}}</a>
                        </li>


                        <li class="ecl-menu__item ecl-menu__item--has-children" data-ecl-menu-item=""
                            data-ecl-has-children=""
                            aria-expanded="false" id="ecl-menu-item-platforms">
                            <a href="{{  route('page.show', ['documentation']) }}"
                               class="ecl-link ecl-link--standalone ecl-menu__link"
                               data-ecl-menu-link=""
                               id="ecl-menu-item-platforms-link">{{__('menu.Documentations')}}</a>
                            <button class="ecl-button ecl-button--ghost ecl-menu__button-caret ecl-button--icon-only"
                                    type="button"
                                    data-ecl-menu-caret="" aria-label="Platforms"
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
{{--                                    @can('create statements')--}}
{{--                                        <li class="ecl-menu__subitem" data-ecl-menu-subitem="">--}}
{{--                                            <a href="{{ route('statement.create') }}"--}}
{{--                                               class="ecl-link ecl-link--standalone ecl-menu__sublink">--}}
{{--                                                {{__('menu.Submit statements of reasons')}}--}}
{{--                                            </a>--}}
{{--                                        </li>--}}
{{--                                    @endcan--}}

                                    <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                        <a href="{{  route('page.show', ['documentation']) }}"
                                           class="ecl-link ecl-link--standalone ecl-menu__sublink">
                                            {{__('menu.Overview')}}
                                        </a>
                                    </li>

                                    <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                        <a href="{{  route('page.show', ['onboarding']) }}"
                                           class="ecl-link ecl-link--standalone ecl-menu__sublink">{{__('pages.Onboarding')}}</a>
                                    </li>


                                    <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                        <a href="{{  route('page.show', ['api-documentation']) }}"
                                           class="ecl-link ecl-link--standalone ecl-menu__sublink">{{__('menu.API Documentation')}}</a>
                                    </li>



                                    <li class="ecl-menu__subitem" data-ecl-menu-subitem="">
                                        <a href="{{  route('page.show', ['webform-documentation']) }}"
                                           class="ecl-link ecl-link--standalone ecl-menu__sublink">{{__('pages.Webform Documentation')}}</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>

        </nav>
    </div>
</header>
