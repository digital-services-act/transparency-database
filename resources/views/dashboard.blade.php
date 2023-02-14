@extends('layouts/ecl')

@section('title', 'Profile Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <h1>Dashboard</h1>
    <div class="ecl-row">
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('user.index') }}">Manage Users</a>
        </div>
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('role.index') }}">Manage Roles</a>
        </div>
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('permission.index') }}">Manage Permissions</a>
        </div>
    </div>

@endsection
