@extends('layouts/ecl')

@section('title', 'Edit a User')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Users" url="{{ route('user.index') }}" />
    <x-ecl.breadcrumb label="Edit a User" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Edit "{{ $user->name }}" User</h1>

    <form method="post" action="{{ route('user.update', [$user]) }}">
        @method('PUT')
        @csrf
        <x-user-form :user=$user :options=$options />
        <x-ecl.button label="Save User" />
    </form>


@endsection

