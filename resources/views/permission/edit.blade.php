@extends('layouts/ecl')

@section('title', 'Edit a Permission')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Permissions" url="{{ route('permission.index') }}" />
    <x-ecl.breadcrumb label="Edit a Permission" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Edit a Permission</h1>

    <form method="post" action="{{ route('permission.update', [$permission]) }}">
        @method('PUT')
        @csrf
        <x-permission-form :permission=$permission :options=$options />
        <x-ecl.button label="Save permission" />
    </form>


@endsection

