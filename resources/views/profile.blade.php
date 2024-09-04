@extends('layouts/ecl')

@section('title', 'User Profile')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile"/>
@endsection

@section('content')

    <h1 class="ecl-u-type-heading-1 ecl-u-mb-xl-6xl">User Profile </h1>
    <p class="ecl-u-type-paragraph ecl-u-mb-xl-2xl"
       style="font-size:16pc; margin-top:-26px; font-style: italic !important">{{auth()->user()->email}}</p>

    <div class="ecl-row ecl-u-flex ecl-u-flex-wrap ecl-u-mb-xl-6xl" style="gap: 2rem; margin-left: 0;">
        @can('create statements')
            <div class="ecl-col ecl-u-flex-item-grow">
                <a class="ecl-button ecl-button--primary"
                   href="{{ route('profile.api.index') }}">API Token Management</a>
            </div>
            <div class="ecl-col ecl-u-flex-item-grow">
                <a class="ecl-button ecl-button--primary"
                   href="{{ route('statement.create') }}">Submit statements of reasons</a>
            </div>
        @endcan
        <div class="ecl-col ecl-u-flex-item-grow">
            <a class="ecl-button ecl-button--secondary" href="/logout">Logout</a>
        </div>
    </div>

    @canany(['create users','create platforms','view logs','view platforms',])

        <h2 class="ecl-u-type-heading-2">Administration</h2>

        <div class="ecl-row ecl-u-flex ecl-u-flex-wrap ecl-u-mb-xl-6xl" style="gap: 2rem; margin-left: 0">
            @can('create users')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('user.index') }}">Manage Users</a>
                </div>
            @endcan

            @can('create platforms')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('platform.index') }}">Manage Platforms</a>
                </div>
            @endcan

            @can('view logs')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('log-messages.index') }}">Log Messages</a>
                </div>
            @endcan

            @can('view platforms')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('onboarding.index') }}">Onboarding Dashboard</a>
                </div>
            @endcan
        </div>
    @endcanany

    <x-ecl.message type="info" icon="information" title="Assistance"
                   message='For any type of issues please contact: <strong><a href="mailto:CNECT-DSA-HELPDESK&#64;ec.europa.eu">CNECT-DSA-HELPDESK&#64;ec.europa.eu</a></strong>'
                   :close="true"/>

@endsection
