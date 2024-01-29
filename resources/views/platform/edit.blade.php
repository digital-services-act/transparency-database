@extends('layouts/ecl')

@section('title', 'Edit a Platform')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Platforms" url="{{ route('platform.index') }}" />
    <x-ecl.breadcrumb label="Edit a Platform" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Edit "{{ $platform->name }}" Platform</h1>

    <form method="post" action="{{ route('platform.update', [$platform]) }}">
        @method('PUT')
        @csrf
        <x-platform.form :platform=$platform :options=$options />
        <x-ecl.button label="Save platform" />
    </form>

    @if($platform->users->count())
        <h2 class="ecl-u-type-heading-2">Users in this Platform</h2>
        <x-users.table :users="$platform->users" />
    @endif

@endsection

