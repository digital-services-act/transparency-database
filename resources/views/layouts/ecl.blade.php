<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <head>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta content="IE=edge" http-equiv="X-UA-Compatible"/>

        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.2.4/ec/styles/optional/ecl-ec-default.css"
            integrity="sha256-KdBAkS2Bqu0Dlfe5nms9s03whQ2NFFuEv5fAVqm7Q3c= sha384-scf8FsT7VkEqGJLUPmCas914wgsoeoqP1qQq5OfkdCKvd4h2rC+AWHhtAXABQN4N sha512-S1wPoSuYkgzTOmw8xoCrMxM9YoLWCD3uYeWMnRnFYAt8OqmrEWzh7NWEUqPVe/JZoNpx/bc9WaV17vv9OhiV6Q=="
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.2.4/ec/styles/optional/ecl-reset.css"
            integrity="sha256-Qymrl/xyII+YTpf1/bX5OgZaAn87bUF1rZJJVH0JH+4= sha384-9Gr0pTu3ZUZRzeoI9FJoEYem/8+S11zMx1F7GP0D2XpopjL5HirBtGJPXA0YgUuX
  sha512-5FjY5Xw+H/DjmKBpCHrLEeesSdMXUtJWIbb/dzA0ef9bm7XJTMTae3XoHlgQtcjvjBtsIBGc+T8loL5D0B5kQg=="
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.2.4/ec/styles/ecl-ec.css"
            integrity="sha256-/3/ZcStWcFZtCo57fGP1VwqOkza3nPS3YNRB1KWuQMo= sha384-+r7eFG9IMuPNO9/qK6D3NHq/UWxvDzMmuCT6i9JfPKmbzTZm3M8tmCWsxoNVBafz sha512-noQGDTA9Xz/cq22vPsSpMhol9pWOByJ1xP0n08f3Tbf/qtYetvoX8FMvcX6U9DlSQ2ZhGn88ZpREouVqrBTiPA=="
            crossorigin="anonymous"
            media="screen"
        />
        <link
            rel="stylesheet"
            href="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.2.4/ec/styles/ecl-ec-print.css"
            integrity="sha256-Tq6qlXJdSTOlqVo9lNS89v8UIIkNa0Bw96kcaJUTknw= sha384-z5/w0fWe/XpAqnBWbzz6srelERapzuw9j9CcNkqxH0ysNExlKduQMzirMjEV6R4L sha512-jwN7V1a0fnmn0Ohbeg64UYSJ/YfieolqHb03qIWXSJHqtXn+2QBAHqs8W31trjPoZtLdfMjQsrLBop8YR//oOA=="
            crossorigin="anonymous"
            media="print"
        />

        <title>{{ config('app.name') }}</title>

        @section('extra-head')
        @show
    </head>
<body class="ecl">


<div id="root">
    <header class="ecl-site-header-core" data-ecl-auto-init="SiteHeaderCore" data-ecl-has-menu="" data-ecl-auto-initialized="true">
        <div class="ecl-site-header-core__container ecl-container">
            <div class="ecl-site-header-core__top"><a href="/component-library/example" class="ecl-link ecl-link--standalone ecl-site-header-core__logo-link" aria-label="Home (European Commission)"><img alt="European Commission logo" title="Home (European Commission)" class="ecl-site-header-core__logo-image ecl-site-header-core__logo-image-desktop" src="static/media/logo-ec--en.5055ef4f.svg"></a>
                <div class="ecl-site-header-core__action">
                    <div class="ecl-site-header-core__login-container"><a class="ecl-button ecl-button--ghost ecl-site-header-core__login-toggle" href="{{route('dashboard')}}">
                            <svg class="ecl-icon ecl-icon--s ecl-site-header-core__icon" focusable="false" aria-hidden="true">
                                <use xlink:href="static/media/icons.148a2e16.svg#log-in"></use>
                            </svg>

                            @auth
                                {{auth()->user()->name}}
                            @elseauth
                                Log In
                            @endauth

                        </a></div>

                </div>
            </div>
        </div>
        <nav class="ecl-menu ecl-menu--group1 ecl-menu--transition" data-ecl-menu="" data-ecl-auto-init="Menu" aria-expanded="false" data-ecl-auto-initialized="true">
            <div class="ecl-menu__overlay" data-ecl-menu-overlay=""></div>
            <div class="ecl-container ecl-menu__container"><a class="ecl-link ecl-link--standalone ecl-menu__open" href="/component-library/example" data-ecl-menu-open="">
                    <svg class="ecl-icon ecl-icon--s" focusable="false" aria-hidden="true">
                        <use xlink:href="static/media/icons.148a2e16.svg#hamburger"></use>
                    </svg>
                    Menu</a>
                <section class="ecl-menu__inner" data-ecl-menu-inner="">
                    <header class="ecl-menu__inner-header">
                        <button class="ecl-menu__close ecl-button ecl-button--text" type="submit" data-ecl-menu-close=""><span class="ecl-menu__close-container ecl-button__container"><svg class="ecl-icon ecl-icon--s ecl-button__icon ecl-button__icon--before" focusable="false" aria-hidden="true" data-ecl-icon=""><use xlink:href="static/media/icons.148a2e16.svg#close-filled"></use></svg><span class="ecl-button__label" data-ecl-label="true">Close</span></span></button>
                        <div class="ecl-menu__title">Menu</div>
                        <button data-ecl-menu-back="" type="submit" class="ecl-menu__back ecl-button ecl-button--text"><span class="ecl-button__container"><svg class="ecl-icon ecl-icon--s ecl-icon--rotate-270 ecl-button__icon ecl-button__icon--before" focusable="false" aria-hidden="true" data-ecl-icon=""><use xlink:href="static/media/icons.148a2e16.svg#corner-arrow"></use></svg><span class="ecl-button__label" data-ecl-label="">Back</span></span></button>
                    </header>
                    <ul class="ecl-menu__list">
                        <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false"><a href="{{route('welcome')}}" class="ecl-menu__link" data-ecl-menu-link="">Home</a></li>
                        <li class="ecl-menu__item" data-ecl-menu-item="" aria-expanded="false"><a href="{{route('dashboard')}}" class="ecl-menu__link" data-ecl-menu-link="">Dashboard</a></li>

                    </ul>
                </section>
            </div>
        </nav>

    </header>

    <div class="ecl-container ecl-u-mb-xl">
        @yield('content')
    </div>


    <footer class="ecl-footer-core">
        <div class="ecl-container ecl-footer-core__container">
            <div class="ecl-footer-core__row">
                <div class="ecl-footer-core__column">
                    <div class="ecl-footer-core__section"><a href="/component-library/example" class="ecl-link ecl-link--standalone ecl-footer-core__logo-link" aria-label="European&#x20;Commission"><img alt="European Commission logo" title="European Commission" class="ecl-footer-core__logo-image-desktop" src="static/media/logo-ec--en.10f5072b.svg"/></a>
                        <h2 class="ecl-footer-core__title"><a href="/component-library/example" class="ecl-link ecl-link--standalone ecl-footer-core__title-link">European Commission website</a></h2>
                        <div class="ecl-footer-core__description">This site is managed by the Directorate-General for Communication</div>
                    </div>
                </div>
                <div class="ecl-footer-core__column">
                    <div class="ecl-footer-core__section ecl-footer-core__section--separator">
                        <ul class="ecl-footer-core__list ecl-footer-core__list--columns">
                            <li class="ecl-footer-core__list-item"><a href="{{route('welcome')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link" aria-label="Link&#x20;to&#x20;Strategy">Link 1</a></li>

                        </ul>
                    </div>
                    <div class="ecl-footer-core__section">
                        <ul class="ecl-footer-core__list">

                            <li class="ecl-footer-core__list-item"><a href="{{route('welcome')}}" class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after ecl-footer-core__link" aria-label="Link&#x20;to&#x20;Follow&#x20;the&#x20;European&#x20;Commission&#x20;on&#x20;social&#x20;media"><span class="ecl-link__label">Link 2</span>
                                    <svg class="ecl-icon ecl-icon--2xs ecl-link__icon" focusable="false" aria-hidden="true">
                                        <use xlink:href="static/media/icons.148a2e16.svg#external"></use>
                                    </svg>
                                </a></li>

                        </ul>
                    </div>
                    <div class="ecl-footer-core__section">
                        <ul class="ecl-footer-core__list">
                            <li class="ecl-footer-core__list-item"><a href="{{route('welcome')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link" aria-label="Link&#x20;to&#x20;Cookies">Cookies</a></li>
                            <li class="ecl-footer-core__list-item"><a href="{{route('welcome')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link" aria-label="Link&#x20;to&#x20;Privacy&#x20;policy">Privacy policy</a></li>
                            <li class="ecl-footer-core__list-item"><a href="{{route('welcome')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link" aria-label="Link&#x20;to&#x20;Legal&#x20;notice">Legal notice</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</div>


<script src="{{asset('js/svg4everybody.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
<script
    src="https://cdn1.fpfis.tech.ec.europa.eu/ecl/v3.2.4/ec/scripts/ecl-ec.js"
    integrity="sha256-bh7GCOcDJcaHHCbdg21xa9W6C0j1aIGLhkjRLs+JFNQ= sha384-vH63fuwjb8P0RUlT1+RD3rFVCXdMBxKTA7jEDcAamt5ibFos0+IfCdd3JjLYrRUi
  sha512-6QdUW/tcDT2xkoz2wREK7DFdepQD1QMHIfVQvOqcpO/TItalRMz9cDA38EDxsOQ1rL+puPsEyyJwM7XvDyoekw=="
    crossorigin="anonymous"
></script>


<script>
    svg4everybody({polyfill: true});
    ECL.autoInit();
</script>

</body>
</html>

