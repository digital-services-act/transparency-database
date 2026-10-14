@extends('layouts/ecl')

@section('title', 'Edit a user')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="User profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Users" url="{{ route('user.index') }}" />
    <x-ecl.breadcrumb label="Edit a user" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Edit "{{ $user->email }}" user</h1>

    <form method="post" action="{{ route('user.update', [$user]) }}">
        @method('PUT')
        @csrf
        <x-user-form :user=$user :options=$options action="edit"/>
        <x-ecl.button label="Update user" />
    </form>


@endsection
