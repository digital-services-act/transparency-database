@extends('layouts/ecl')

@section('title', 'Create a role')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Roles" url="{{ route('role.index') }}" />
    <x-ecl.breadcrumb label="Create a Role" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a Role</h1>

    <form method="post" action="{{route('role.store')}}">
        @csrf
        <x-role-form :role=$role :options=$options :permissions=$permissions />
        <x-ecl.button label="Create role" />
    </form>


@endsection

