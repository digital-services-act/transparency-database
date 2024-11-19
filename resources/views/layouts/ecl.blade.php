<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">

    <title>@yield('title', 'Home') - DSA Transparency Database</title>

    <meta content="width=device-width,initial-scale=1" name="viewport" />
    <meta content="IE=edge" http-equiv="X-UA-Compatible" />
    <script>
      var cl = document.querySelector('html').classList;
      cl.remove('no-js');
      cl.add('has-js');
    </script>

    <script
            src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.2/pikaday.js"
            crossorigin="anonymous"
    ></script>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/optional/ecl-reset.css') }}"
            media="screen"
    />

    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/ecl-ec.css') }}"
            crossorigin="anonymous"
            media="screen"
    >

    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/optional/ecl-ec-utilities.css') }}"
            crossorigin="anonymous"
            media="screen"
    />

    <link
            rel="stylesheet"
            href="{{ asset('static/ecl/styles/ecl-ec-print.css') }}"
            crossorigin="anonymous"
            media="print"
    >


{{--    <script defer src="https://europa.eu/webtools/load.js?theme=ec"></script>--}}
    <script src="{{ asset('static/ecl/scripts/ecl-ec.js') }}" crossorigin="anonymous"></script>

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
            </div>
        </div>
    </div>
    <x-ecl.footer/>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous"></script>
<script>
    @if($ecl_init)
        ECL.autoInit();
    @endif
</script>
@if(config('dsa.SITEID', false) && config('dsa.SITEPATH', false))
{{--DO NOT SPLIT THIS LINE--}}
<script type="application/json">{"utility":"analytics","siteID":"{{ config('dsa.SITEID') }}","sitePath":["{{ config('dsa.SITEPATH') }}"],"instance":"ec"}</script>
@endif
<script type="application/json">{"utility": "cck","url": "{{ route('page.show', ['page' => 'cookie-policy']) }}"}</script>
<script defer src="https://webtools.europa.eu/load.js?globan=1110" type="text/javascript"></script>
@if(config('dsa.webt.clientId', false))
<script type="text/javascript" src="https://unpkg.com/@tilde-nlp/website-translator/dist/widget.js"></script>
<script>
  WebsiteTranslator.Options.api.clientId = "{{ config('dsa.webt.clientId') }}";
  WebsiteTranslator.Options.api.url = "{{ config('dsa.webt.url') }}";
  WebsiteTranslator.Options.api.version = {{ config('dsa.webt.version') }};
  WebsiteTranslator.Options.ui.layout = "menu";
  WebsiteTranslator.Initialize();
  console.log(WebsiteTranslator);
</script>
@endif
</body>
</html>
