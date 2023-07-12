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

        <script defer src="https://europa.eu/webtools/load.js?theme=ec" type="text/javascript"></script>

        @section('extra-head')
        @show
    </head>
<body class="ecl">
    <div id="root">

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

                    @if ($errors->any())
                        <x-ecl.message type="error" icon="error" title="Errors" :message="$errors->all()"/>
                    @endif
                </div>
            </div>
            <div class="ecl-row">
                <div class="ecl-col-12">
                    @yield('content')
                </div>
            </div>
        </div>
        <x-ecl.footer />
    </div>



<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous"></script>
<script
    src="{{ asset('static/scripts/ecl-ec.js') }}"
    crossorigin="anonymous"
></script>
<script>
    @if($ecl_init)
        ECL.autoInit();
    @endif
</script>
@if(env('SITEID', false) && env('SITEPATH', false))
<script type="application/json">{"utility":"analytics","siteID":"{{ env('SITEID') }}","sitePath":["{{ env('SITEPATH') }}"],"instance":"ec"}</script>
@endif
<script type="application/json">{"utility": "cck","url": "{{ route('page.show', ['page' => 'cookie-policy']) }}"}</script>
</body>
</html>

