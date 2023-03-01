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

        @section('extra-head')
        @show
    </head>
<body class="ecl">
    <div id="root">

        <x-ecl.header />


        <div class="ecl-container ecl-u-mb-xl">
            <div class="ecl-row">
                <div class="ecl-col-12">



                    @auth
                        {{--                        @can('impersonate')--}}

                        <form class="ecl-u-f-r ecl-u-mt-l" action="{{route('impersonate')}}" method="POST">
                            @csrf
                            <div class="ecl-form-group">
                                <div class="ecl-select__container ecl-select__container--m">
                                    <select class="ecl-select" id="select-default" name="username" onchange="this.form.submit()">

                                        @foreach($profiles as $profile)
                                            <option value="{{$profile->eu_login_username}}"
                                                    @if(auth()->user()->eu_login_username == $profile->eu_login_username) selected @endif>
                                                {{$profile->name}}
                                            </option>
                                        @endforeach

                                    </select>
                                    <div class="ecl-select__icon">
                                        <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape" focusable="false" aria-hidden="true">
                                            <x-ecl.icon icon="corner-arrow" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </form>


                        {{--                        @endcan--}}
                    @endauth

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

