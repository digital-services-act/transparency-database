@extends('layouts/ecl')

@section('title', 'Onboarding Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}"/>
    <x-ecl.breadcrumb label="Onboarding Dashboard"/>
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">
        Onboarding Dashboard
    </h1>




    <div class="ecl-table-responsive">
        <table class="ecl-table ecl-table--zebra">
            <thead class="ecl-table__head">
            <tr class="ecl-table__row">
                <th scope="col" class="ecl-table__header">Statistic</th>
                <th scope="col" class="ecl-table__header">Total</th>
            </tr>
            </thead>
            <tbody class="ecl-table__body">
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    VLOP Platforms
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $vlop_count }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    VLOP Platforms that have sent data via API
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_vlop_platforms_sending_api }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    VLOP Platforms that have sent data via webform
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_vlop_platforms_sending_webform }}
                </td>
            </tr>

            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    VLOP Valid Tokens
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_vlop_valid_tokens }}
                </td>
            </tr>

            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    Non-VLOP Platforms
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $non_vlop_count }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    Non-VLOP Platforms that have sent data via API
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_non_vlop_platforms_sending_api }}
                </td>
            </tr>
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    Non-VLOP Platforms that have sent data via webform
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_non_vlop_platforms_sending_webform }}
                </td>
            </tr>


            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Statistic">
                    Non-VLOP Valid Tokens
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Total">
                    {{ $total_non_vlop_valid_tokens }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>




    <form class="ecl-container ecl-u-mt-2xl">
        <div class="ecl-row">
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.checkbox label="Platform is VLOP?"
                                name="vlop"
                                id="vlop"
                                value="1"
                                checked="{{ request()->get('vlop', 0) }}"

                />
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.checkbox label="Platform is Onboarded?"
                                name="onboarded"
                                id="onboarded"
                                value="1"
                                checked="{{ request()->get('onboarded', 0) }}"

                />
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.checkbox label="Platform has Tokens?"
                                name="has_tokens"
                                id="has_tokens"
                                value="1"
                                checked="{{ request()->get('has_tokens', 0) }}"

                />
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.checkbox label="Platform has Statements?"
                                name="has_statements"
                                id="has_statements"
                                value="1"
                                checked="{{ request()->get('has_statements', 0) }}"

                />
            </div>
        </div>
        <div class="ecl-row">
            <div class="ecl-col-m-6">
                <x-ecl.textfield name="s" label=""
                                 placeholder="search by name" justlabel="true" value="{{ request()->get('s', '') }}"/>
            </div>
            <div class="ecl-col-m-6 ecl-u-align-content-center ecl-u-type-align-center">
                <x-ecl.button label="Filter"/>
            </div>
        </div>
    </form>

    @foreach($platforms as $platform)

        <h2 class="ecl-u-type-heading-2">
            {{ $platform->name }}
            <a href="{{ route('platform.edit', ['platform' => $platform, 'returnto' => request()->fullUrl()]) }}"
               class="ecl-link" title="Edit Platform">
                <svg class="ecl-icon ecl-icon--m ecl-button__icon" focusable="false" aria-hidden="true" data-ecl-icon>
                    <x-ecl.icon icon="edit"/>
                </svg>
            </a>
        </h2>

        <h6 class="ecl-u-type-heading-6">Statements</h6>
        @if ($platform_ids_methods_data[$platform->id] ?? false)
            <div class="ecl-table-responsive">
                <table class="ecl-table">
                    <thead class="ecl-table__head">
                    <tr class="ecl-table__row">
                        <th class="ecl-table__header" scope="col" width="33%">API</th>
                        <th class="ecl-table__header" scope="col" width="33%">API MULTI</th>
                        <th class="ecl-table__header" scope="col" width="33%">FORM</th>
                    </tr>
                    </thead>
                    <tbody class="ecl-table__body">
                    <tr class="ecl-table__row">
                        <td class="ecl-table__cell"
                            data-ecl-table-header="API">{{ $platform_ids_methods_data[$platform->id]['API'] }}</td>
                        <td class="ecl-table__cell"
                            data-ecl-table-header="API MULTI">{{ $platform_ids_methods_data[$platform->id]['API_MULTI'] }}</
                        >
                        <td class="ecl-table__cell"
                            data-ecl-table-header="FORM">{{ $platform_ids_methods_data[$platform->id]['FORM'] }}</
                        >
                    </tr>
                    </tbody>
                </table>
            </div>
        @else
            <p class="ecl-u-type-paragraph">
                No statements found.
            </p>
        @endif


        @if(count($platform->users) === 0)
            <p class="ecl-u-type-paragraph">
                No users found.
                <x-ecl.cta-button label="Create a User"
                                  url="{{ route('user.create', ['returnto' => request()->fullUrl(), 'platform_id' => $platform->id]) }}"/>
            </p>
        @else
            <h6 class="ecl-u-type-heading-6">Users</h6>
            <x-users.table :users="$platform->users"/>
        @endif

    @endforeach


    {{ $platforms->links('paginator') }}

@endsection
