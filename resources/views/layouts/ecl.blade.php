<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">


    <title>@yield('title', 'Home') - DSA Transparency Database</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/optional/ecl-ec-default.css') }}"
            crossorigin="anonymous"
            media="screen"
    >
    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/optional/ecl-reset.css') }}"
            crossorigin="anonymous"
            media="screen"
    >
    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/ecl-ec.css') }}"
            crossorigin="anonymous"
            media="screen"
    >
    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/optional/ecl-ec-default-print.css') }}"
            crossorigin="anonymous"
            media="print"
    >
    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/ecl-ec-print.css') }}"
            crossorigin="anonymous"
            media="print"
    >
    <style>
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4a4e54; /* Change the background color to your preference */
            color: #ffffff; /* Change the text color to your preference */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Optional: Add a box shadow for a subtle effect */
        }

        /* Optional: Add hover effect */
        .scroll-to-top:hover {
            background-color: #0056b3; /* Change the background color on hover */
        }
    </style>
    <script defer src="https://europa.eu/webtools/load.js?theme=ec"></script>

    @section('extra-head')
    @show
</head>
<body class="ecl">
<div id="root" style="padding-top: 0 !important;">

    <x-ecl.header />

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

                @if(session('error'))
                    <x-ecl.message type="error" icon="error" title="Error" :message="session('error')"/>
                @endif

                @if ($errors->any())
                    <x-ecl.message type="error" icon="error" title="Errors"
                                   message="Your request contained multiple errors. Please make sure to fill in all of the mandatory fields."/>
                @endif

            </div>
        </div>
        <div class="ecl-row">
            <div class="ecl-col-12">
                @yield('content')
                <div class="scroll-to-top" onclick="scrollToTop()">
                    <svg class="ecl-icon ecl-icon--s  ecl-button__icon ecl-button__icon--before"
                         focusable="false" aria-hidden="true" data-ecl-icon="">
                        <x-ecl.icon icon="corner-arrow"/>
                    </svg>
                </div>

            </div>
        </div>
    </div>
    <x-ecl.footer/>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous"></script>
<script src="{{ asset('static/ecl/scripts/ecl-ec.js') }}"
        crossorigin="anonymous"></script>
<script>
    @if($ecl_init)
    ECL.autoInit()
    @endif

    function scrollToTop () {
      window.scrollTo({
        top: 0, behavior: 'smooth',
      })
    }
</script>
@if(config('dsa.SITEID', false) && config('dsa.SITEPATH', false))
{{--DO NOT SPLIT THIS LINE--}}
<script type="application/json">{"utility":"analytics","siteID":"{{ config('dsa.SITEID') }}","sitePath":["{{ config('dsa.SITEPATH') }}"],"instance":"ec"}</script>
@endif
<script type="application/json">{"utility": "cck","url": "{{ route('page.show', ['page' => 'cookie-policy']) }}"}
</script>
</body>
</html>

