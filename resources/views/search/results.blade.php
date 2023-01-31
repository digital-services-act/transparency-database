@extends('layouts/ecl')

@section('title', 'Notice Search Results')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Notice Search Results" />
@endsection

@section('content')

    <h1 class="ecl-u-mb-l ecl-page-header__title ecl-u-type-heading-1">Search Results for <strong>"{{ $query }}"</strong></h1>

    <x-notices-table :notices=$results />

@endsection

