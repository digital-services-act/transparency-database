@extends('layouts/ecl')

@section('title', 'Profile Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')


    <div style="position: absolute; right: 15px;">
        <p style="text-align: right;">
            Current Environment: <strong>{{ env('APP_ENV') == 'staging' ? 'sandbox' : env('APP_ENV') }}</strong><br />
            Goto:
            @if(env('APP_ENV') != 'production')<a target="_blank" href="{{ env('PRODUCTION_URL') }}">production</a>@endif
            @if(env('APP_ENV') != 'staging')<a target="_blank" href="{{ env('SANDBOX_URL') }}">sandbox</a>@endif
        <p>
    </div>

    <h1>Dashboard</h1>

    <div class="ecl-row">
        @can('view logs')
            <div class="ecl-col-4">
                <a class="ecl-button ecl-button--primary" href="{{ route('logs') }}">Logs</a>
            </div>
        @endcan
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

    @can('administrate')
    <h2 class="ecl-u-type-heading-2">Administration</h2>

    <div class="ecl-row ecl-u-mb-l">
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('user.index') }}">Users</a>
        </div>
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('platform.index') }}">Platforms</a>
        </div>
{{--        <div class="ecl-col-4">--}}
{{--            <a class="ecl-button ecl-button--primary" href="{{ route('role.index') }}">Roles</a>--}}
{{--        </div>--}}
{{--        <div class="ecl-col-4">--}}
{{--            <a class="ecl-button ecl-button--primary" href="{{ route('permission.index') }}">Permissions</a>--}}
{{--        </div>--}}
    </div>
    @endcan

@endsection
