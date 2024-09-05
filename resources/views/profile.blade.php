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




    <h2 class="ecl-u-type-heading-2">Onboarding Statistics</h2>

    <h3 class="ecl-u-type-heading-3">VLOPs</h3>

    <div class="ecl-table-responsive">
        <table class="ecl-table ecl-table--zebra">
            <thead class="ecl-table__head">
            <tr class="ecl-table__row">
                <th scope="col" class="ecl-table__header">Description</th>
                <th scope="col" class="ecl-table__header">Total</th>
            </tr>
            </thead>
            <tbody class="ecl-table__body">
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    VLOP Platforms
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $vlop_count }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    VLOP Platforms that have sent data via API
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_vlop_platforms_sending_api }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    VLOP Platforms that have sent data via webform
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_vlop_platforms_sending_webform }}
                </td>
            </tr>

            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    VLOP Valid Tokens
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_vlop_valid_tokens }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <h3 class="ecl-u-type-heading-3">Non-VLOPs</h3>

    <div class="ecl-table-responsive ecl-u-mb-6xl">
        <table class="ecl-table ecl-table--zebra">
            <thead class="ecl-table__head">
            <tr class="ecl-table__row">
                <th scope="col" class="ecl-table__header">Description</th>
                <th scope="col" class="ecl-table__header">Total</th>
            </tr>
            </thead>
            <tbody class="ecl-table__body">

            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    Non-VLOP Platforms
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $non_vlop_count }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    Non-VLOP Platforms that have sent data via API
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_non_vlop_platforms_sending_api }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    Non-VLOP Platforms that have sent data via webform
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_non_vlop_platforms_sending_webform }}
                </td>
            </tr>


            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Description">
                    Non-VLOP Valid Tokens
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_non_vlop_valid_tokens }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>



    <x-ecl.message type="info" icon="information" title="Assistance"
                   message='For any type of issues please contact: <strong><a href="mailto:CNECT-DSA-HELPDESK&#64;ec.europa.eu">CNECT-DSA-HELPDESK&#64;ec.europa.eu</a></strong>'
                   :close="true"/>

@endsection
