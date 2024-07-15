@extends('layouts/ecl')

@section('title', 'User Profile')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="{{__('profile.User Profile')}}"/>
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">{{__('profile.User Profile')}} </h1>
    <p class="ecl-u-type-paragraph"
       style="font-size:16pc; margin-top:-26px; font-style: italic !important">{{auth()->user()->email}}</p>


    <div class="ecl-row ecl-u-mb-l">
        @can('create statements')
            <div class="ecl-col-3">
                <a class="ecl-button ecl-button--primary"
                   href="{{ route('profile.api.index') }}">{{__('profile.API Token Management')}}</a>
            </div>
        @endcan
        <div class="ecl-col-3">
            <a class="ecl-button ecl-button--primary" href="/logout">{{__('profile.Logout')}}</a>
        </div>
    </div>

    @canany(['create users','create platforms','view logs','view platforms',])

        <h2 class="ecl-u-type-heading-2">{{__('profile.Administration')}}</h2>

        <div class="ecl-row ecl-u-mb-l">
            @can('create users')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('user.index') }}">{{__('profile.Manage Users')}}</a>
                </div>
            @endcan

            @can('create platforms')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('platform.index') }}">{{__('profile.Manage Platforms')}}</a>
                </div>
            @endcan

            @can('view logs')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('log-messages.index') }}">{{__('profile.Log Messages')}}</a>
                </div>
            @endcan

            @can('view platforms')
                <div class="ecl-col-3">
                    <a class="ecl-button ecl-button--primary"
                       href="{{ route('onboarding.index') }}">{{__('profile.Onboarding Dashboard')}}</a>
                </div>
            @endcan
        </div>
    @endcanany

    <h1 class="ecl-u-type-heading-1">{{__('profile.Assistance')}}</h1>
    <p class="ecl-u-type-paragraph"
       style="font-size:16pc; margin-top:-26px;">{{__('profile.For any type of issues please contact:')}} <strong><a href="mailto:CNECT-DSA-HELPDESK&#64;ec.europa.eu">CNECT-DSA-HELPDESK&#64;ec.europa.eu</a></strong></p>


@endsection
