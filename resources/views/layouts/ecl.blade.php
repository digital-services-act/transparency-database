<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <head>

        <title>@yield('title', 'Home') - DSA Transparency Database</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta content="IE=edge" http-equiv="X-UA-Compatible"/>

        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.7.0/ec/styles/optional/ecl-ec-default.css"
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.7.0/ec/styles/optional/ecl-reset.css"
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.7.0/ec/styles/ecl-ec.css"
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.7.0/ec/styles/ecl-ec-print.css"
            crossorigin="anonymous"
            media="print"
        />

        @section('extra-head')
        @show
    </head>
<body class="ecl">


<div id="root">

    <header class="ecl-site-header" data-ecl-auto-init="SiteHeader">
        <div class="ecl-site-header__header">
            <div class="ecl-site-header__container ecl-container">

                <div class="ecl-site-header__top">
                    <a href="{{ route('home') }}" class="ecl-link ecl-link--standalone ecl-site-header__logo-link"
                       aria-label="European Commission">
                        <img alt="European Commission logo" title="European Commission"
                             class="ecl-site-header__logo-image ecl-site-header__logo-image-desktop"
                             src="{{asset('static/media/logo-ec--en.5055ef4f.svg')}}"/>
                    </a>
                    <div class="ecl-site-header__action">
                        <div class="ecl-site-header-core__login-container">
                            <a class="ecl-button ecl-button--ghost ecl-site-header-core__login-toggle"
                               href="{{ route('dashboard') }}">

                                <svg class="ecl-icon ecl-icon--s ecl-site-header-core__icon" focusable="false"
                                     aria-hidden="true">
                                    <x-ecl.icon icon="log-in"/>
                                </svg>

                                @auth
                                    {{auth()->user()->name}}
                                @elseauth
                                    Log In
                                @endauth

                            </a>
                        </div>

                        @include('search.searchbar')
                    </div>
                </div>

            </div>
        </div>

        <div class="ecl-site-header__banner">
            <div class="ecl-container">
                <div class="ecl-site-header__site-name">DSA Transparency Database</div>
            </div>
        </div>

        <nav class="ecl-menu ecl-menu--group1 ecl-menu--transition" data-ecl-menu="" data-ecl-auto-init="Menu"
             aria-expanded="false" data-ecl-auto-initialized="true">
            <div class="ecl-menu__overlay" data-ecl-menu-overlay="">


            </div>

            <div class="ecl-container ecl-menu__container"><a class="ecl-link ecl-link--standalone ecl-menu__open"
                                                              href="/component-library/example" data-ecl-menu-open="">
                    <svg class="ecl-icon ecl-icon--s" focusable="false" aria-hidden="true">
                        <use xlink:href="{{asset('static/media/icons.148a2e16.svg#hamburger')}}"></use>
                    </svg>
                    Menu</a>

                <section class="ecl-menu__inner" data-ecl-menu-inner="">
                    <header class="ecl-menu__inner-header">
                        <button class="ecl-menu__close ecl-button ecl-button--text" type="submit"
                                data-ecl-menu-close=""><span class="ecl-menu__close-container ecl-button__container"><svg
                                    class="ecl-icon ecl-icon--s ecl-button__icon ecl-button__icon--before"
                                    focusable="false" aria-hidden="true" data-ecl-icon=""><use
                                        xlink:href="{{asset('static/media/icons.148a2e16.svg#close-filled')}}"></use></svg><span
                                    class="ecl-button__label" data-ecl-label="true">Close</span></span></button>
                        <div class="ecl-menu__title">Menu</div>
                        <button data-ecl-menu-back="" type="submit" class="ecl-menu__back ecl-button ecl-button--text">
                            <span class="ecl-button__container"><svg
                                    class="ecl-icon ecl-icon--s ecl-icon--rotate-270 ecl-button__icon ecl-button__icon--before"
                                    focusable="false" aria-hidden="true" data-ecl-icon=""><use
                                        xlink:href="{{asset('static/media/icons.148a2e16.svg#corner-arrow')}}"></use></svg><span
                                    class="ecl-button__label" data-ecl-label="">Back</span></span></button>


                    </header>
                    <div class="demo-container ecl-u-d-flex ecl-u-justify-content-between">
                        <div>
                            <ul class="ecl-menu__list">

                                <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">
                                    <a href="{{route('home')}}" class="ecl-menu__link" data-ecl-menu-link="">Home</a>
                                </li>

                                <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">
                                    <a href="{{route('statement.index')}}" class="ecl-menu__link" data-ecl-menu-link="">Statements</a>
                                </li>

{{--                                <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">--}}
{{--                                    <a href="{{route('page.show', ['documentation'])}}" class="ecl-menu__link"--}}
{{--                                       data-ecl-menu-link="">Documentation</a>--}}
{{--                                </li>--}}

                                <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false">
                                    <a target="_blank"
                                       href="https://github.com/DG-CNECT/dsa-module2/wiki/DSA-Transparency-Database---API-Documentation"
                                       class="ecl-menu__link" data-ecl-menu-link="">Documentation</a>
                                </li>

                                @can('view dashboard')
                                    <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false"><a
                                            href="{{route('dashboard')}}" class="ecl-menu__link"
                                            data-ecl-menu-link="">Dashboard</a>
                                    </li>
                                @endcan
                            </ul>

                        </div>

                            @auth
{{--                        @can('impersonate')--}}
                            <div class="ecl-form-group ecl-u-mv-xs">
                                <form action="{{route('impersonate')}}" method="POST">
                                    @csrf
                                    <div class="ecl-select__container ecl-select__container--sm"><select class="ecl-select"
                                                                                                         id="select-default"
                                                                                                         name="username"
                                                                                                         onchange="this.form.submit()">

                                            @foreach($profiles as $profile)
                                                <option value="{{$profile->eu_login_username}}"
                                                        @if(auth()->user()->eu_login_username == $profile->eu_login_username) selected @endif>
                                                    {{$profile->name}}
                                                </option>
                                            @endforeach



                                        </select>
                                        <div class="ecl-select__icon">
                                            <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape"
                                                 focusable="false" aria-hidden="true">
                                                <x-ecl.icon icon="corner-arrow" />
                                            </svg>
                                        </div>
                                    </div>
                                </form>

                            </div>
{{--                        @endcan--}}
                            @endauth
                </section>


            </div>
        </nav>

    </header>


    <div class="ecl-container ecl-u-mb-xl">
        <div class="ecl-row">
            <div class="ecl-col-12">

                <nav class="ecl-breadcrumb ecl-page-header__breadcrumb" aria-label="You&#x20;are&#x20;here&#x3A;"
                     data-ecl-breadcrumb="true" data-ecl-auto-init="Breadcrumb">
                    <ol class="ecl-breadcrumb__container">
                        @yield('breadcrumbs')
                    </ol>
                </nav>

                @if(session('success'))
                    <x-ecl.message type="success" icon="success" title="Success" :message="session('success')"/>
                @endif

                @if ($errors->any())
                    <x-ecl.message type="error" icon="error" title="Errors" :message="$errors->all()"/>
                @endif

                @yield('content')
            </div>
        </div>
    </div>


    <footer class="ecl-footer-core">
        <div class="ecl-container ecl-footer-core__container">
            <div class="ecl-footer-core__row">

                <div class="ecl-footer-core__column">
                    <div class="ecl-footer-core__section">
                        <a href="{{ route('home') }}" class="ecl-link ecl-link--standalone ecl-footer-core__logo-link"
                           aria-label="European&#x20;Commission">
                            <img alt="European Commission logo" title="European Commission"
                                 class="ecl-footer-core__logo-image-desktop"
                                 src="{{asset('static/media/logo-ec--en.10f5072b.svg')}}"/>
                        </a>

                        <h2 class="ecl-footer-core__title">
                            <a href="/" class="ecl-link ecl-link--standalone ecl-footer-core__title-link">European
                                Commission website</a>
                        </h2>

                        <div class="ecl-footer-core__description">This site is managed by the Directorate-General for
                            Communication
                        </div>
                    </div>
                </div>

                <div class="ecl-footer-core__column">

                    <div class="ecl-footer-core__section ecl-footer-core__section--separator">
                        <ul class="ecl-footer-core__list ecl-footer-core__list--columns">
                            <li class="ecl-footer-core__list-item">
                                <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                                   aria-label="Link&#x20;to&#x20;Strategy">Link 1</a>
                            </li>
                        </ul>
                    </div>

                    <div class="ecl-footer-core__section">
                        <ul class="ecl-footer-core__list">
                            <li class="ecl-footer-core__list-item">
                                <a href="{{route('home')}}"
                                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after ecl-footer-core__link"
                                   aria-label="Link&#x20;to&#x20;Follow&#x20;the&#x20;European&#x20;Commission&#x20;on&#x20;social&#x20;media">
                                    <span class="ecl-link__label">Link 2</span>
                                    <svg class="ecl-icon ecl-icon--2xs ecl-link__icon" focusable="false"
                                         aria-hidden="true">
                                        <x-ecl.icon icon="external"/>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="ecl-footer-core__section">
                        <ul class="ecl-footer-core__list">
                            <li class="ecl-footer-core__list-item">
                                <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                                   aria-label="Link&#x20;to&#x20;Cookies">Cookies</a>
                            </li>
                            <li class="ecl-footer-core__list-item">
                                <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                                   aria-label="Link&#x20;to&#x20;Privacy&#x20;policy">Privacy policy</a>
                            </li>
                            <li class="ecl-footer-core__list-item">
                                <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                                   aria-label="Link&#x20;to&#x20;Legal&#x20;notice">Legal notice</a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </footer>

</div>


<script src="https://unpkg.com/svg4everybody@2.1.9/dist/svg4everybody.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous"></script>
<script
    src="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.7.0/ec/scripts/ecl-ec.js"
    crossorigin="anonymous"
></script>


<script>
    svg4everybody({polyfill: true});
    ECL.autoInit();
</script>


</body>
</html>

