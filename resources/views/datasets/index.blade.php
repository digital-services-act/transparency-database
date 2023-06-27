@extends('layouts/ecl')

@section('title', 'Datasets')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Datasets" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Datasets</h1>


    <div>
        <a href="statements.csv" class="ecl-button--primary ecl-button">Statements (.csv)</a>
        <a href="statements.xlsx" class="ecl-button--primary ecl-button">Statements (.xlsx)</a>
    </div>




@endsection
