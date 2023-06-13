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
                    <x-ecl.icon icon="growth" />
                </svg>
                <div class="ecl-fact-figures__value">{{ $your_platform_total }} statements</div>
                <div class="ecl-fact-figures__title">Your Platform Total</div>
                <div class="ecl-fact-figures__description">
                    Every statement your platform created in the database is counted and totalled here.
                </div>
            </div>

        </div>
    </div>

    <h2 id="graphs">Your Platform Statements over the Last Year</h2>

    <ol class="ecl-timeline" data-ecl-auto-init="Timeline" data-ecl-timeline="">
        @foreach($days_count as $days_ago => $count)

        <li class="ecl-timeline__item" id="{{ $days_ago }}">
            <div class="ecl-timeline__tooltip">
                <div class="ecl-timeline__tooltip-arrow"></div>
                @if($loop->first)<div class="ecl-timeline__title">Statements Created</div>@endif
                <div class="ecl-timeline__label">{{ $days_ago }} @if(!$loop->first)days @else day @endif</div>
                <div class="ecl-timeline__content">{{ $count }}</div>
            </div>
        </li>

        @endforeach

    </ol>







@endsection
