@extends('layouts/ecl')

@section('title', 'Create a Notice')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Notices" url="{{ route('notice.index') }}" />
    <x-ecl.breadcrumb label="Create a Notice" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a Notice</h1>

    @if ($errors->any())
        <x-ecl.message type="error" icon="error" title="Errors in the form" :message="$errors->all()" />
    @endif

    <form method="post" action="{{route('notice.store')}}">
        @csrf
        <x-notice-form :notice=$notice :options=$options />
        <x-ecl.button label="Create Notice" />
    </form>


@endsection

