<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <head>

        <title>Login - DSA Transparency Database</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta content="IE=edge" http-equiv="X-UA-Compatible"/>

        <link
            rel="stylesheet"
            href="{{ asset('static/styles/ecl-ec-default.css') }}"
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="{{ asset('static/styles/ecl-reset.css') }}"
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="{{ asset('static/styles/ecl-ec.css') }}"
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="{{ asset('static/styles/ecl-ec-print.css') }}"
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
                    <a href="{{ route('home') }}" class="ecl-link ecl-link--standalone ecl-site-header__logo-link" aria-label="European Commission">
                        <img alt="European Commission logo"
                             title="European Commission"
                             class="ecl-site-header__logo-image ecl-site-header__logo-image-desktop"
                             src="{{asset('static/media/logo-ec--en.65cfd447.svg')}}"/>
                    </a>

{{--                    <div class="ecl-site-header__action">--}}
{{--                        <div class="ecl-site-header-core__login-container">--}}

{{--                            <div class="ecl-u-d-flex ecl-u-justify-content-end">--}}

{{--                                <div class="">--}}
{{--                                    <a class="ecl-button ecl-button--ghost ecl-site-header-core__login-toggle" href="{{ route('dashboard') }}">--}}
{{--                                        <svg class="ecl-icon ecl-icon--s ecl-site-header-core__icon" focusable="false"--}}
{{--                                             aria-hidden="true">--}}
{{--                                            <x-ecl.icon icon="log-in"/>--}}
{{--                                        </svg>--}}

{{--                                        @auth--}}
{{--                                            {{ auth()->user()->name }} &nbsp;--}}
{{--                                        @endauth--}}
{{--                                        @guest--}}
{{--                                            Log In--}}
{{--                                        @endguest--}}
{{--                                    </a>--}}
{{--                                </div>--}}

{{--                                <div class="">--}}
{{--                                    @auth--}}
{{--                                        <x-impersonate />--}}
{{--                                    @endauth--}}
{{--                                </div>--}}

{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
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
                <a class="ecl-link ecl-link--standalone ecl-menu__open" href="/component-library/example" data-ecl-menu-open="">
                    <svg class="ecl-icon ecl-icon--s" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="hamburger" />
                    </svg>
                    Menu
                </a>

                <section class="ecl-menu__inner" data-ecl-menu-inner="">
                    <header class="ecl-menu__inner-header">
                        <button class="ecl-menu__close ecl-button ecl-button--text" type="submit" data-ecl-menu-close="">
                            <span class="ecl-menu__close-container ecl-button__container">
                                <svg class="ecl-icon ecl-icon--s ecl-button__icon ecl-button__icon--before" focusable="false" aria-hidden="true" data-ecl-icon="">
                                    <x-ecl.icon icon="close-filled" />
                                </svg>
                                <span class="ecl-button__label" data-ecl-label="true">Close</span>
                            </span>
                        </button>

                        <div class="ecl-menu__title">Menu</div>
                        <button data-ecl-menu-back="" type="submit" class="ecl-menu__back ecl-button ecl-button--text">
                                <span class="ecl-button__container">
                                    <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-270 ecl-button__icon ecl-button__icon--before" focusable="false" aria-hidden="true" data-ecl-icon="">
                                        <x-ecl.icon icon="corner-arrow" />
                                    </svg>
                                    <span class="ecl-button__label" data-ecl-label="">Back</span>
                                </span>
                        </button>
                    </header>
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
                        <li class="ecl-breadcrumb__segment ecl-breadcrumb__current-page " data-ecl-breadcrumb-item="static">
                        </li><li class="ecl-breadcrumb__segment ecl-breadcrumb__current-page" data-ecl-breadcrumb-item="static" aria-current="page">Login</li>
                    </ol>
                </nav>
                @if(session('success'))
                    <x-ecl.message type="success" icon="success" title="Success" :message="session('success')"/>
                @endif

                @if ($errors->any())
                    <x-ecl.message type="error" icon="error" title="Errors" :message="$errors->all()"/>
                @endif
            </div>
        </div>
        <div class="ecl-row">
            <div class="ecl-col-12">
                <form method="post" action="{{route('login.submit')}}" id="welcome-login">
                    @csrf
                    <x-ecl.textfield label="User" name="username" id="username" required="true"/>

                    <div class="ecl-form-group ecl-u-mb-l" id="div_password">
                        <x-ecl.label label='Password' for='Password' name='password' :required=true />
                        <input type="password" name="password" id="password" class="ecl-text-input ecl-text-input--l"/>
                    </div>

                    <button type="submit" class="ecl-button ecl-button--primary">Log In</button>
                </form>


            </div>
        </div>
    </div>
    <x-ecl.footer />
</div>


<script src="https://unpkg.com/svg4everybody@2.1.9/dist/svg4everybody.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous"></script>
<script
    src="{{ asset('static/scripts/ecl-ec.js') }}"
    crossorigin="anonymous"
></script>
<script>
    svg4everybody({polyfill: true});
    ECL.autoInit();
</script>
</body>
</html>

