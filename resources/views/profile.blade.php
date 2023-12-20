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

    @can('administrate')
        <h2 class="ecl-u-type-heading-2">Administration</h2>

        <div class="ecl-row ecl-u-mb-l">
            <div class="ecl-col-3">
                <a class="ecl-button ecl-button--primary" href="{{ route('user.index') }}">Users</a>
            </div>
            <div class="ecl-col-3">
                <a class="ecl-button ecl-button--primary" href="{{ route('platform.index') }}">Platforms</a>
            </div>
            <div class="ecl-col-3">
                <a class="ecl-button ecl-button--primary" href="{{ route('invitation.index') }}">Invitations</a>
            </div>
        </div>
    @endcan


    <h2 class="ecl-u-type-heading-2">Assistance</h2>

    <p class="ecl-u-type-paragraph">
        For technical issues:
    <pre>
            CNECT-DIGITAL-SERVICES-TECH&#64;ec.europa.eu
        </pre>
    </p>

    <p class="ecl-u-type-paragraph">

        If you are an online platform, please contact CNECT DIGITAL SERVICES at <a
            href="mailto:CNECT-DIGITAL-SERVICES@ec.europa.eu">CNECT-DIGITAL-SERVICES@ec.europa.eu</a> to register your
        interest for onboarding. Currently, only designated VLOPs (Verified List of Platforms) have access to
        the API (both sandbox/test and production) for submissions of statements of reasons under the DSA (Digital
        Services Act). Other platforms that have notified the Commission will be contacted for onboarding in early 2024.

    <pre>
            CNECT-DIGITAL-SERVICES&#64;ec.europa.eu
        </pre>
    </p>

@endsection
