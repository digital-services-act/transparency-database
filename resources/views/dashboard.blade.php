@extends('layouts/ecl')

@section('title', 'Profile Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <h1>{{ $platform_name }} Dashboard</h1>

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
    </div>
    @endcan

@endsection
