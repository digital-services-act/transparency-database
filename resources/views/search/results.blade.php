@extends('layouts/ecl')

@section('title', 'Statement Search Results')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Statement Search Results" />
@endsection

@section('content')

    <h1 class="ecl-u-mb-l ecl-page-header__title ecl-u-type-heading-1">Search Results for <strong>"{{ $query }}"</strong></h1>

    <x-statements-table :statements=$results />

@endsection

