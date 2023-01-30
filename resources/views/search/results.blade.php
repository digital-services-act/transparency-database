@extends('layouts/ecl')

@section('title', 'Search Notices')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Search Notices" />
@endsection

@section('content')

    <h1>Search Results for <strong>"{{ $query }}"</strong></h1>

    <x-notices-table :notices=$results />

@endsection

