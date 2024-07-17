@extends('layouts/ecl')

@section('title', 'Manage Platforms')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}"/>
    <x-ecl.breadcrumb label="Onboarding Dashboard"/>
@endsection


@section('content')


    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Onboarding Dashboard</h1>

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

    <h2 class="ecl-u-type-heading-2">Platforms</h2>

    <form>
        <div class="ecl-row">
            <div class="ecl-col-3">
                <x-ecl.radio label="Platform is VLOP"
                             name="vlop"
                             id="vlop"
                             :options="$options['vlops']"
                             default="{{ request()->get('vlop', 0) }}"
                             required="true"
                />
            </div>
            <div class="ecl-col-3">
                <x-ecl.radio label="Platform is Onboarded"
                             name="onboarded"
                             id="onboarded"
                             :options="$options['onboardeds']"
                             default="{{ request()->get('onboarded', 0) }}"
                             required="true"
                />
            </div>
            <div class="ecl-col-3">
                <x-ecl.textfield name="s" label="Search <a class='ecl-link' href='{{ route('onboarding.index') }}'>reset</a>"
                                 placeholder="search by name" justlabel="true" value="{{ request()->get('s', '') }}"/>
            </div>
            <div class="ecl-col-3 ecl-u-align-content-center ecl-u-type-align-center">
                <x-ecl.button label="Filter" />
            </div>
        </div>
    </form>

    @foreach($platforms as $platform)

        <h2 class="ecl-u-type-heading-2">{{ $platform->name }}</h2>
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


    {{--    {{ $platforms->links('paginator') }}--}}

@endsection
