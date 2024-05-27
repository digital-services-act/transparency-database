@extends('layouts/ecl')

@section('title', 'Create a platform')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Platforms" url="{{ route('platform.index') }}" />
    <x-ecl.breadcrumb label="Create a Platform" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a Platform</h1>

    <form method="post" action="{{route('platform.store')}}">
        @csrf
        <x-platform.form :platform=$platform :options=$options />
        <x-ecl.button label="Create platform" />
    </form>


@endsection

