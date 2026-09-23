@extends('layouts/ecl')

@section('title', 'Create a user')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="User profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Users" url="{{ route('user.index') }}" />
    <x-ecl.breadcrumb label="Create a user" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a user</h1>

    <form method="post" action="{{route('user.store')}}">
        @csrf
        <x-user-form :user=$user :options=$options />
        <x-ecl.button label="Create user" />
    </form>


@endsection
