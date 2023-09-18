@extends('layouts/ecl')

@section('title', 'Datasets')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Datasets" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Datasets</h1>


    <div>
        <a href="{{$csv}}" class="ecl-button--primary ecl-button">Statements of reasons (.csv)</a>
        <a href="{{$excel}}" class="ecl-button--primary ecl-button">Statements of reasons (.xlsx)</a>
    </div>




@endsection
