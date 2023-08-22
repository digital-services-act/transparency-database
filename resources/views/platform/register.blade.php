@extends('layouts/ecl')

@section('title', 'Register Your Platform')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Register Your Platform" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Register Your Platform</h1>

    <p class="ecl-u-type-paragraph">
        Are you the lead member of a platform? Do you need to register your platform in the DSA Transparency database?
        Fill in the details and the process of onboarding your platform will begin.
    </p>

    <form method="post" action="{{ route('platform.register.store') }}">
        @honeypot
        @csrf
        <x-platform.platform-register-form :options="$options" />
        <x-ecl.button label="Register Your Platform" />
    </form>

@endsection