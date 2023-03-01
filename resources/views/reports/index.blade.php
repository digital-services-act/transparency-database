@extends('layouts/ecl')

@section('title', 'Reports')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Reports" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Reports</h1>

    <h2 class="">Standard Reports</h2>
    <p>
        <a href="?" class="ecl-button--primary ecl-button">Statements</a>
        <a href="?chart=statements-by-method" class="ecl-button--primary ecl-button">by Method</a>
        <a href="?chart=statements-by-source" class="ecl-button--primary ecl-button">by Source</a>
        <a href="?chart=statements-by-redress" class="ecl-button--primary ecl-button">by Redress</a>
    </p>

    <h2 class="ecl-u-type-heading-2">{{ $title }}</h2>
    {!! $chart->renderHtml() !!}
    {!! $chart->renderChartJsLibrary() !!}
    {!! $chart->renderJs() !!}

@endsection
