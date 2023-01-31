@extends('layouts/ecl')

@section('title', 'Profile Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Profile Dashboard" />
@endsection


@section('content')


            <h1>Profile Dashboard</h1>
            <p>
                Dashboard content
            </p>


@endsection
