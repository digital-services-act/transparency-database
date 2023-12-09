@extends('layouts/ecl')

@section('title', 'Create a user')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Invitations" url="{{ route('invitation.index') }}" />
    <x-ecl.breadcrumb label="Create an Invitation" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create an Invitation</h1>

    <form method="post" action="{{route('invitation.store')}}">
        @csrf
        <x-invitation-form :invitation=$invitation :options=$options />
        <x-ecl.button label="Create invitation" />
    </form>


@endsection

