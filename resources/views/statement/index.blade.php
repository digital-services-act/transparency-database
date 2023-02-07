@extends('layouts/ecl')

@section('title', 'Statements')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Statements"/>
@endsection

@section('content')



    @auth
        <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
            <x-ecl.cta-button label="Create a Statement" url="{{ route('statement.create') }}"/>
        </div>
    @endauth

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statements</h1>

    <x-statements-table :statements=$statements />

@endsection

