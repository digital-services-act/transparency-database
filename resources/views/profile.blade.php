@extends('layouts/ecl')

@section('title', 'User Profile')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile"/>
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">User Profile </h1>
    <p class="ecl-u-type-paragraph"
       style="font-size:16pc; margin-top:-26px; font-style: italic !important">{{auth()->user()->email}}</p>


    <div class="ecl-row ecl-u-mb-l">
        @can('create statements')
            <div class="ecl-col-3">
                <a class="ecl-button ecl-button--primary" href="{{ route('profile.api.index') }}">API Token
                    Management</a>
            </div>
        @endcan
    </div>

    @canany(['create users','create platforms','view logs','view platforms',])

        <h2 class="ecl-u-type-heading-2">Administration</h2>

        <div class="ecl-row ecl-u-mb-l">
            @can('create users')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary" href="{{ route('user.index') }}">Manage Users</a>
                </div>
            @endcan

            @can('create platforms')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary" href="{{ route('platform.index') }}">Manage Platforms</a>
                </div>
            @endcan

            @can('view logs')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary" href="{{ route('log-messages.index') }}">Log Messages</a>
                </div>
            @endcan

            @can('view platforms')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary" href="{{ route('onboarding.index') }}">Onboarding
                        Dashboard</a>
                </div>
            @endcan
        </div>
    @endcanany


    <h2 class="ecl-u-type-heading-2">Assistance</h2>
    <div class="ecl-row ecl-u-mb-l">
        <div class="ecl-col-12">
            <p class="ecl-u-type-paragraph">
                For any type of issues please contact:
            <pre>
                CNECT-DSA-HELPDESK&#64;ec.europa.eu
        </pre>
            </p>

            <p class="ecl-u-type-paragraph" style="max-width:none !important">
                <strong>If you are an online platform</strong> and wish to set up your statement of reasons submission
                process:<br/><br/>

                Please register your intent to comply <a
                    href="https://ec.europa.eu/eusurvey/runner/DSA-ComplianceStamentsReasons">here</a><br/><br/>

                This is the first step required to be onboarded as an online platform with obligations under Article 24(5) of the DSA. You will then be contacted by your Digital Service Coordinator (DSC), i.e. the DSC of the country of establishment of your online platform, for onboarding. You can find an overview of already appointed DSCs <a href="https://digital-strategy.ec.europa.eu/en/policies/dsa-dscs">here</a>.

                <br/><br/>

                Thank you

            </p>
        </div>
    </div>

@endsection
