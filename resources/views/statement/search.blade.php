@extends('layouts/ecl')

@section('title', 'Statements')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Search for Statements of Reasons" url="{{ route('statement.index') }}"/>
    <x-ecl.breadcrumb label="Advanced Search"/>
@endsection

@section('content')



    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statements of Reasons: Advanced Search</h1>


    <p>
        With the form below you can now specify many more filters on the statements of reasons.
    </p>

    <x-statement.search-form :options="$options" />


@endsection

