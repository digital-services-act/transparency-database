@extends('layouts/ecl')

@section('title', 'User Profile')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile"/>
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">User Profile</h1>

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
                For technical issues please contact:
            <pre>
            CNECT-DIGITAL-SERVICES-TECH&#64;ec.europa.eu
        </pre>
            </p>

            <p class="ecl-u-type-paragraph" style="max-width:none !important">
                <strong>If you are an online platform</strong> and wish to set up your statement of reasons submission
                process:<br/><br/>

                Please register your intent to comply <a
                    href="https://ec.europa.eu/eusurvey/runner/DSA-ComplianceStamentsReasons">https://ec.europa.eu/eusurvey/runner/DSA-ComplianceStamentsReasons</a><br/><br/>

                Later on you will be contacted by the DSC of the country of establishment of the online platform (you
                will find the already appointed DSCs here <a
                    href="https://digital-strategy.ec.europa.eu/en/policies/dsa-cooperation">https://digital-strategy.ec.europa.eu/en/policies/dsa-cooperation</a>).
                This is the first step required to be onboarded as an online platform with obligations under Article
                24(5) of the DSA. Please note that Digital Services Coordinators should be appointed by 17th February
                2024 at the latest.<br/><br/>

                Currently, only designated VLOPs (Verified List of Platforms) have access to the API (both sandbox/test
                and production) for submissions of statements of reasons under the DSA (Digital Services Act). Other
                platforms that have notified the Commission will be contacted by their Digital Service Coordinator for
                onboarding.<br/><br/>

                Thank you

            </p>
        </div>
    </div>

@endsection
