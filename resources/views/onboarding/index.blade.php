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

    <form class="ecl-u-mt-2xl">
        <div class="ecl-row">
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.select label="Select Type of Platform:"
                                name="vlop"
                                id="vlop"
                                :options="$options['vlops']"
                                :required="true"
                                default="{{ request()->get('vlop', -1) }}"
                />
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.select label="Platform is Onboarded:"
                              name="onboarded"
                              id="onboarded"
                              :options="$options['onboardeds']"
                              :required="true"
                              default="{{ request()->get('onboarded', -1) }}"
                />
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.select label="Platform has Tokens:"
                              name="has_tokens"
                              id="has_tokens"
                              :options="$options['has_tokens']"
                              :required="true"
                              default="{{ request()->get('has_tokens', -1) }}"
                />
            </div>
            <div class="ecl-col-m-3 ecl-u-align-content-center">
                <x-ecl.select label="Platform has Statements:"
                              name="has_statements"
                              id="has_statements"
                              :options="$options['has_statements']"
                              :required="true"
                              default="{{ request()->get('has_statements', -1) }}"
                />
            </div>
        </div>
        <div class="ecl-row">
            <div class="ecl-col-m-6">
                <x-ecl.textfield name="s" label=""
                                 placeholder="Search by platform name" justlabel="true" value="{{ request()->get('s', '') }}"/>
            </div>
            <div class="ecl-col-m-6 ecl-u-align-content-center ecl-u-type-align-center">
                <x-ecl.button label="Filter Results"/>
                &nbsp;&nbsp;&nbsp;
                <a href="{{ route('onboarding.index') }}" class="ecl-link">Clear Filters</a>
            </div>
        </div>
    </form>

    <h2 class="ecl-u-type-heading-2">{{ $platforms->total() }} Platforms</h2>

    @foreach($platforms as $platform)

        <h3 class="ecl-u-type-heading-3">
            {{ $platform->name }}
            <a href="{{ route('platform.edit', ['platform' => $platform, 'returnto' => request()->fullUrl()]) }}"
               class="ecl-link" title="Edit Platform">
                <svg class="ecl-icon ecl-icon--m ecl-button__icon" focusable="false" aria-hidden="true" data-ecl-icon>
                    <x-ecl.icon icon="edit"/>
                </svg>
            </a>
        </h3>

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
                        <td class="ecl-table__cell" data-ecl-table-header="API">@aif($platform_ids_methods_data[$platform->id]['API'])</td>
                        <td class="ecl-table__cell" data-ecl-table-header="API MULTI">@aif($platform_ids_methods_data[$platform->id]['API_MULTI'])</td>
                        <td class="ecl-table__cell" data-ecl-table-header="FORM">@aif($platform_ids_methods_data[$platform->id]['FORM'])</td>
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
