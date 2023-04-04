@extends('layouts/ecl')

@section('title', 'Reports')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Reports" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Reports and Statistics</h1>


    <div class="ecl-fact-figures ecl-fact-figures--col-2">
        <div class="ecl-fact-figures__items">
            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="infographic" />
                </svg>
                <div class="ecl-fact-figures__value">{{ $total }} statements</div>
                <div class="ecl-fact-figures__title">Global Total</div>
                <div class="ecl-fact-figures__description">
                    Everyday very large online platforms are displaying their transparency by uploading their
                    statements of reason to the database.
                </div>
            </div>
            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="spreadsheet" />
                </svg>
                <div class="ecl-fact-figures__value">{{ $total_twentyfour }} statements</div>
                <div class="ecl-fact-figures__title">Created in the last 24 hours</div>
                <div class="ecl-fact-figures__description">
                    The API is open 24/7 for statements to be created. Nearly every programming
                    language has the ability to send a JSON payload to an API endpoint.
                </div>
            </div>
            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth" />
                </svg>
                <div class="ecl-fact-figures__value">{{ $your_count }} statements</div>
                <div class="ecl-fact-figures__title">Your Total</div>
                <div class="ecl-fact-figures__description">
                    Every statement you create in the database is counted and totalled here.
                </div>
            </div>
            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="regulation" />
                </svg>
                <div class="ecl-fact-figures__value">{{ $days_count }} statements</div>
                <div class="ecl-fact-figures__title">You have created in the last {{ $days }} days</div>
                <div class="ecl-fact-figures__description">
                    Did your ICT people say that they just uploaded 50,000 statements? Well you will know
                    for sure here.
                </div>
            </div>
        </div>
    </div>

    <h2 id="graphs">Graphs and Trends</h2>

    <p>Here we can see some basic reporting for statements that you have created over the past year.</p>

    <div>
        <a href="?#graphs" class="ecl-button--primary ecl-button">Statements</a>
        <a href="?chart=statements-by-decision#graphs" class="ecl-button--primary ecl-button">by Decision</a>
        <a href="?chart=statements-by-ground#graphs" class="ecl-button--primary ecl-button">by Ground</a>
        <a href="?chart=statements-by-method#graphs" class="ecl-button--primary ecl-button">by Method</a>
        <a href="?chart=statements-by-source#graphs" class="ecl-button--primary ecl-button">by Source</a>
        <a href="?chart=statements-by-country#graphs" class="ecl-button--primary ecl-button">by Country</a>
        <a href="?chart=statements-by-automatic-detection#graphs" class="ecl-button--primary ecl-button">by Automatic Detection</a>
{{--        <a href="?chart=statements-by-redress" class="ecl-button--primary ecl-button">by Redress</a>--}}
    </div>

    <h2 class="ecl-u-type-heading-2">{{ $title }}</h2>
    {!! $chart->renderHtml() !!}
    {!! $chart->renderChartJsLibrary() !!}
    {!! $chart->renderJs() !!}

    <p>
        * - you can turn graph data on and off by clicking the index label.
    </p>

@endsection
