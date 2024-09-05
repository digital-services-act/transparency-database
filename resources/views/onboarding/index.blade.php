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
        <div class="ecl-row ecl-u-mb-2xl">
            <div class="ecl-col-m-6">
                <x-ecl.textfield name="s" label=""
                                 placeholder="Search for platform name" justlabel="true"
                                 value="{{ request()->get('s', '') }}"/>
            </div>
            <div class="ecl-col-m-6 ecl-u-align-content-center">
                <x-ecl.button label="Filter results"/>
                &nbsp;&nbsp;&nbsp;
                @if($tags)
                    <a href="{{ route('onboarding.index') }}" class="ecl-link ecl-link--standalone">Reset all</a>
                @else
                    <span class="ecl-u-type-color-primary-20" style="font-family: Arial, sans-serif;">Reset all</span>
                @endif
            </div>
        </div>
        <input type="hidden" name="sorting" value="{{ request()->get('sorting', 'name:asc') }}" />
    </form>


    <x-ecl.tags :tags="$tags" />

    <div class="ecl-row" style="font-family: Arial, sans-serif;">
        <div class="ecl-col-m-6">
            <h2 class="ecl-u-type-heading-2">Platforms {{ $platforms->total() }} of {{ $all_platforms_count }}</h2>
        </div>
        <div class="ecl-col-m-6 ecl-u-align-content-center ecl-u-type-align-right">
            Sort:
            @foreach($options['sorting'] as $option)
                @if(request()->get('sorting', 'name:asc') === $option['value'])
                    <strong>{{ $option['label'] }}</strong>
                @else
                    <a href="{{ $sorting_query_base }}&sorting={{ $option['value'] }}" class="ecl-link ecl-link--standalone" style="@if(request()->get('sorting', 'name:asc') === $option['value'])font-weight: bold; @endif">{{ $option['label'] }}</a>
                @endif

                @if(! $loop->last)
                    |
                @endif
            @endforeach
        </div>
    </div>


    @foreach($platforms as $platform)

        <div class="ecl-u-mb-6xl">


            <h3 class="ecl-u-type-heading-3">
                {{ $platform->name }}
                <a href="{{ route('platform.edit', ['platform' => $platform, 'returnto' => request()->fullUrl()]) }}"
                   class="ecl-link" title="Edit Platform">
                    <svg class="ecl-icon ecl-icon--m ecl-button__icon" focusable="false" aria-hidden="true"
                         data-ecl-icon>
                        <x-ecl.icon icon="edit"/>
                    </svg>
                </a>
            </h3>

            <div class="ecl-row ecl-u-mb-l" style="font-family: Arial, sans-serif;">
                <div class="ecl-col-l-6">
                    <strong>Statements</strong>
                    @if ($platform_ids_methods_data[$platform->id] ?? false)
                        <span class="ecl-u-ml-2xl ecl-u-mr-2xl">API: @aif($platform_ids_methods_data[$platform->id]['API'])</span>
                        <span class="ecl-u-mr-2xl">API MULTI: @aif($platform_ids_methods_data[$platform->id]['API_MULTI'])</span>
                        <span class="">FORM: @aif($platform_ids_methods_data[$platform->id]['FORM'])</span>
                    @else
                        No statements found.
                    @endif
                </div>
                <div class="ecl-col-l-6 ecl-u-align-content-end ecl-u-type-align-right">
                    <strong>Created at:</strong> {{ $platform->created_at->format('d-m-Y') }}
                </div>
            </div>


            @if(count($platform->users) === 0)
                <p class="ecl-u-type-paragraph">
                    No users found.
                    <x-ecl.cta-button label="Create a User"
                                      url="{{ route('user.create', ['returnto' => request()->fullUrl(), 'platform_id' => $platform->id]) }}"/>
                </p>
            @else
                <x-users.table :users="$platform->users"/>
            @endif
        </div>

    @endforeach


    {{ $platforms->links('paginator') }}

@endsection
