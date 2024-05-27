@extends('layouts/ecl')

@section('title', 'Manage Platforms')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}"/>
    <x-ecl.breadcrumb label="Onboarding Dashboard"/>
@endsection


@section('content')


    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Onboarding Dashboard</h1>

    <p class="ecl-u-type-paragraph">
        Total number of VLOP platforms {{ $vlop_count }}<br />
        Total number of non-VLOP platforms {{ $platforms->count() }}<br />
        Total number of platforms that have sent data via API or webform {{ $total_platforms_sending }}
    </p>

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
