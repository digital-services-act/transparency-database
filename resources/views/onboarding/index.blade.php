@extends('layouts/ecl')

@section('title', 'Manage Platforms')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}"/>
    <x-ecl.breadcrumb label="Onboarding Dashboard"/>
@endsection


@section('content')


    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Onboarding Dashboard</h1>


    @foreach($platforms as $platform)

        <h2>{{ $platform->name }}</h2>
        @if(strtolower((string) config('app.env_real')) === 'production')
        <p> Count is not available in production</p>
        @else
        <p>
            <span class="ecl-label ecl-label--medium">API Statements: {{$platform->api_statements->count()}}</span>
            <span class="ecl-label ecl-label--medium">API Multi Statements: {{$platform->api_multi_statements->count()}}</span>
            <span class="ecl-label ecl-label--medium">FORM Statements: {{$platform->form_statements->count()}}</span>
        </p>
        @endif

        @if(count($platform->users) == 0)
            <p>No users found.
            <x-ecl.cta-button label="Create a User" url="{{ route('user.create') }}"/>
            </p>
        @else
        <x-users.table :users="$platform->users"/>
        @endif

    @endforeach


    {{--    {{ $platforms->links('paginator') }}--}}

@endsection
