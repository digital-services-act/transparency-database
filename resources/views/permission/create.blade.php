@extends('layouts/ecl')

@section('title', 'Create a permission')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Permissions" url="{{ route('permission.index') }}" />
    <x-ecl.breadcrumb label="Create a Permission" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a Permission</h1>

    <form method="post" action="{{route('permission.store')}}">
        @csrf
        <x-permission-form :permission=$permission :options=$options />
        <x-ecl.button label="Create permission" />
    </form>


@endsection

