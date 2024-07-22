@extends('layouts/ecl')

@section('title', 'User Profile')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="{{__('profile.User Profile')}}"/>
@endsection

@section('content')

    <h1 class="ecl-u-type-heading-1 ecl-u-mb-xl-6xl">{{__('profile.User Profile')}} </h1>
    <p class="ecl-u-type-paragraph ecl-u-mb-xl-2xl"
       style="font-size:16pc; margin-top:-26px; font-style: italic !important">{{auth()->user()->email}}</p>

    <div class="ecl-row ecl-u-flex ecl-u-flex-wrap ecl-u-mb-xl-6xl" style="gap: 2rem; margin-left: 0;">
        @can('create statements')
            <div class="ecl-col ecl-u-flex-item-grow">
                <a class="ecl-button ecl-button--primary"
                   href="{{ route('profile.api.index') }}">{{__('profile.API Token Management')}}</a>
            </div>
            <div class="ecl-col ecl-u-flex-item-grow">
                <a class="ecl-button ecl-button--primary"
                   href="{{ route('statement.create') }}">{{__('menu.Submit statements of reasons')}}</a>
            </div>
        @endcan
        <div class="ecl-col ecl-u-flex-item-grow">
            <a class="ecl-button ecl-button--secondary" href="/logout">{{__('profile.Logout')}}</a>
        </div>
    </div>

    @canany(['create users','create platforms','view logs','view platforms',])

        <h2 class="ecl-u-type-heading-2">{{__('profile.Administration')}}</h2>

        <div class="ecl-row ecl-u-flex ecl-u-flex-wrap ecl-u-mb-xl-6xl" style="gap: 2rem; margin-left: 0">
            @can('create users')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('user.index') }}">{{__('profile.Manage Users')}}</a>
                </div>
            @endcan

            @can('create platforms')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('platform.index') }}">{{__('profile.Manage Platforms')}}</a>
                </div>
            @endcan

            @can('view logs')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('log-messages.index') }}">{{__('profile.Log Messages')}}</a>
                </div>
            @endcan

            @can('view platforms')
                <div class="ecl-col ecl-u-flex-item-grow">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('onboarding.index') }}">{{__('profile.Onboarding Dashboard')}}</a>
                </div>
            @endcan
        </div>
    @endcanany

    <x-ecl.message type="info" icon="information" title="{{__('profile.Assistance')}}"
                   message="{!! __('profile.For any type of issues please contact:') !!}"
                   :close="true"/>

@endsection
