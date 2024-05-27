@extends('layouts/ecl')

@section('title', 'Edit a Role')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Roles" url="{{ route('role.index') }}" />
    <x-ecl.breadcrumb label="Edit a Role" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Edit "{{ $role->name }}" Role</h1>

    <form method="post" action="{{ route('role.update', [$role]) }}">
        @method('PUT')
        @csrf
        <x-role-form :role=$role :options=$options :permissions=$permissions />
        <x-ecl.button label="Save role" />
    </form>


@endsection

