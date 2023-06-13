@extends('layouts/ecl')

@section('title', 'Request to be in a Platform')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />

    <x-ecl.breadcrumb label="Request to be in a Platform" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Request to be in a Platform</h1>

    <form method="post" action="{{ route('user.request.platform') }}">
        @csrf
        <x-user-request-platform-form :options="$options" />
        <x-ecl.button label="Request to be in a Platform" />
    </form>

    <p>
        You account does not appear to be associated with a platform.
        If you would like to create statements using the API or web form your account will need to be
        associated with one of the major platforms present in this database and given the appropriate permissions.
    </p>

    <p>
        To request that your account be associated with a platform and to be able to create statements,
        please send an email to <a href="mailto:xxxx@yyyy.zzzz">xxxx@yyyy.zzzz</a> and make a formal request.
    </p>
@endsection

