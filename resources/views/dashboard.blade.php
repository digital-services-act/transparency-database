@extends('layouts/ecl')

@section('title', 'Profile Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">{{ $platform_name }} Dashboard</h1>

    <div class="ecl-row ecl-u-mb-l">
        @can('view reports')
            <div class="ecl-col-4">
                <a class="ecl-button ecl-button--primary" href="{{ route('reports') }}">Reports</a>
            </div>
        @endcan
        @can('create statements')
            <div class="ecl-col-4">
                <a class="ecl-button ecl-button--primary" href="{{ route('api-index') }}">API</a>
            </div>
        @endcan
    </div>

    <div class="ecl-row ">
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--secondary" href="{{ route('logout') }}">Logout</a>
        </div>
    </div>

    @can('administrate')
    <h2 class="ecl-u-type-heading-2">Administration</h2>

    <div class="ecl-row ecl-u-mb-l">
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('user.index') }}">Users</a>
        </div>
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('platform.index') }}">Platforms</a>
        </div>
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('invitation.index') }}">Invitations</a>
        </div>
    </div>
    @endcan


    <h2 class="ecl-u-type-heading-2">Assistance</h2>

    <p class="ecl-u-type-paragraph">
        For technical issues or access rights requests:
        <pre>
            CNECT-DIGITAL-SERVICES-TECH&#64;ec.europa.eu
        </pre>
    </p>

    <p class="ecl-u-type-paragraph">
        Other inquiries email:

        <pre>
            CNECT-DIGITAL-SERVICES&#64;ec.europa.eu
        </pre>
    </p>

@endsection
