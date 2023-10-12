@extends('layouts/ecl')

@section('title', 'Day Archives')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Day Archives" />
@endsection


@section('content')


    <div class="ecl-fact-figures ecl-fact-figures--col-1">
        <div class="ecl-fact-figures__description">
            On this page you can download archives of statement of reasons by date.
        </div>
    </div>

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Day Archives</h1>


    <x-dayarchive.table :dayarchives="$dayarchives" />


@endsection
