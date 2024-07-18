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
        <a href="/modal" class="ecl-link ecl-link--default" id="statistics-toggle">
            <svg class="ecl-icon ecl-icon--m ecl-button__icon" focusable="false" aria-hidden="true" data-ecl-icon>
                <x-ecl.icon icon="infographic" />
            </svg>
        </a>
    </h1>

    <dialog data-ecl-auto-init="Modal" data-ecl-modal-toggle="statistics-toggle"
            id="modal-statistics" aria-modal="true"
            class="ecl-modal ecl-modal--l" aria-labelledby="modal-example-header">
        <div class="ecl-modal__container ecl-container">
            <div class="ecl-modal__content ecl-col-12 ecl-col-m-10 ecl-col-l-8">
                <header class="ecl-modal__header">
                    <div class="ecl-modal__header-content" id="modal-example-header">Statistics</div>
                    <button class="ecl-button ecl-button--tertiary ecl-modal__close ecl-button--icon-only"
                            type="button"
                            data-ecl-modal-close>
                        <span class="ecl-button__container">
                            <span class="ecl-button__label" data-ecl-label="true">Close</span>
                            <svg class="ecl-icon ecl-icon--m ecl-button__icon" focusable="false" aria-hidden="true" data-ecl-icon>
                                <x-ecl.icon icon="close" />
                            </svg>
                        </span>
                    </button>
                </header>
                <div class="ecl-modal__body">
                    <div class="ecl-modal__body-scroll" data-ecl-modal-scroll>


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
                                {{--                <tr class="ecl-table__row">--}}
                                {{--                    <td class="ecl-table__cell" data-ecl-table-header="Statistic">--}}
                                {{--                        VLOP Platforms that have sent data via API or webform--}}
                                {{--                    </td>--}}
                                {{--                    <td class="ecl-table__cell" data-ecl-table-header="Total">--}}
                                {{--                        {{ $total_vlop_platforms_sending }}--}}
                                {{--                    </td>--}}
                                {{--                </tr>--}}
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
                                {{--                <tr class="ecl-table__row">--}}
                                {{--                    <td class="ecl-table__cell" data-ecl-table-header="Statistic">--}}
                                {{--                        Non-VLOP Platforms that have sent data via API or webform--}}
                                {{--                    </td>--}}
                                {{--                    <td class="ecl-table__cell" data-ecl-table-header="Total">--}}
                                {{--                        {{ $total_non_vlop_platforms_sending }}--}}
                                {{--                    </td>--}}
                                {{--                </tr>--}}
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


                    </div>
                    <div class="ecl-modal__body-overflow" aria-hidden="true"></div>

                </div>
                <footer class="ecl-modal__footer">
                    <div class="ecl-modal__footer-content">
                        <button class="ecl-button ecl-button--secondary ecl-modal__button"
                                type="button"
                                data-ecl-modal-close>
                            Close
                        </button>
                    </div>
                </footer>
            </div>
        </div>
    </dialog>

    <form class="ecl-container">
        <div class="ecl-row">
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.checkbox label="Platform is VLOP"
                                name="vlop"
                                id="vlop"
                                value="1"
                                checked="{{ request()->get('vlop', 0) }}"

                />
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.checkbox label="Platform is Onboarded"
                                name="onboarded"
                                id="onboarded"
                                value="1"
                                checked="{{ request()->get('onboarded', 0) }}"

                />
            </div>
            <div class="ecl-col-m-3">
                <x-ecl.textfield name="s" label=""
                                 placeholder="search by name" justlabel="true" value="{{ request()->get('s', '') }}"/>
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center ecl-u-type-align-center">
                <x-ecl.button label="Filter"/>
            </div>
        </div>
    </form>

    @foreach($platforms as $platform)

        <h2 class="ecl-u-type-heading-3">{{ $platform->name }}</h2>
        @if(strtolower((string) config('app.env_real')) === 'production')
            <p class="ecl-u-type-paragraph"> Count is not available in production</p>
        @else
            <p class="ecl-u-type-paragraph">
                <x-onboarding.label :count="$platform->api_statements->count()" label="API Statements"/>
                <x-onboarding.label :count="$platform->api_multi_statements->count()" label="API Multi Statements"/>
                <x-onboarding.label :count="$platform->form_statements->count()" label="FORM Statements"/>
            </p>
        @endif

        @if(count($platform->users) == 0)
            <p class="ecl-u-type-paragraph">
                No users found.
                <x-ecl.cta-button label="Create a User" url="{{ route('user.create') }}"/>
            </p>
        @else
            <x-users.table :users="$platform->users"/>
        @endif

    @endforeach


    {{ $platforms->links('paginator') }}

@endsection
