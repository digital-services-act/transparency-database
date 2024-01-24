@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'User Profile')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home"/>
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">Welcome to the DSA Transparency Database!</h1>

    <div class="ecl-row">
        <div class="ecl-col-l-8">
            <div class="ecl-u-mb-l" style="width: 100% !important; color: #404040 !important;
font: normal normal 400 1rem/1.5rem arial,sans-serif !important;">
                <div class="ecl-u-mb-l" style="width: 100% !important; color: #404040 !important;
font: normal normal 400 1rem/1.5rem arial,sans-serif !important;">
                    The Digital Services Act (DSA), obliges providers of hosting services to inform their users of the
                    content moderation
                    decisions they take and explain the reasons behind those decisions in so-called <strong>statements
                        of reasons</strong>.
                </div>
                <div class="ecl-u-mb-l" style="width: 100% !important; color: #404040 !important;
font: normal normal 400 1rem/1.5rem arial,sans-serif !important;">
                    To enhance transparency and facilitate scrutiny over content moderation decisions, <strong>providers
                        of
                        online platforms need to submit these statements of reasons to the DSA Transparency
                        Database</strong>. The database
                    allows to track the content moderation decisions taken by providers of online platforms in almost
                    real-time.
                    It also offers various tools for accessing, analysing, and downloading the information that
                    platforms need to
                    make available when they take content moderation decisions, contributing to the monitoring of the
                    dissemination
                    of illegal and harmful content online.
                </div>

                <a href="{{ route('page.show', ['faq']) }}">
                    <button class="ecl-button ecl-button--call" type="submit"><span class="ecl-button__container"><span
                                class="ecl-button__label" data-ecl-label="true">More questions? Check our FAQ</span><svg
                                class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-button__icon ecl-button__icon--after"
                                focusable="false" aria-hidden="true" data-ecl-icon="">
<x-ecl.icon icon="corner-arrow"/>
</svg></span>
                    </button>
                </a>

            </div>
        </div>
        <div class="ecl-col-l-4">
            <div class="ecl-media-container">
                <figure class="ecl-media-container__figure">
                    <div class="ecl-media-container__caption">
                        <a href="https://digital-strategy.ec.europa.eu/en/policies/digital-services-act-package">
                            <picture class="ecl-picture ecl-media-container__picture"><img
                                    class="ecl-media-container__media"
                                    src="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-text-logo.jpg"
                                    alt="Digital Services Act Logo"/></picture>
                        </a>
                        <a href="https://digital-strategy.ec.europa.eu/en/policies/digital-services-act-package"
                           style="text-decoration: none !important;">
                            <h4 style="color: #004494 !important; ">Discover more about the Digital Services Act</h4>
                        </a>


                    </div>


                </figure>


            </div>


        </div>

    </div>

    <h1 class="ecl-u-type-heading-1">Overview of the Database</h1>

    <div class="ecl-row">
        <div class="ecl-col-l-8">
            <div class="ecl-u-mb-l" style="width: 100% !important; color: #404040 !important;
font: normal normal 400 1rem/1.5rem arial,sans-serif !important;">
                Below you can find some summary statistics on the statements of reasons submitted by providers of online
                platforms to
                the Commission.
            </div>
        </div>

        <div class="ecl-col-l-4">

            <a class="ecl-button ecl-button--primary" style="margin-bottom:1rem; min-width:300px" href="{{ route('dashboard') }}">Visualize
                the data in the dashboard</a>
            <a class="ecl-button ecl-button--primary" style="margin-bottom:1rem; min-width:300px" href="{{ route('statement.index') }}">Search
                for Statements of Reasons</a>

        </div>

        <div class="ecl-fact-figures ecl-fact-figures--col-3">
            <div class="ecl-fact-figures__items">

                <div class="ecl-fact-figures__item">
                    <div class="ecl-fact-figures__value">
                        <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" style="transform: translateY(10px);"
                             focusable="false" aria-hidden="true">
                            <x-ecl.icon icon="data"/>
                        </svg>
                        <span style="font-size: 1.5rem">@aif($total)</span></div>
                    <div class="ecl-fact-figures__title">Total number of statements of reasons submitted</div>
                </div>

                <div class="ecl-fact-figures__item">
                    <div class="ecl-fact-figures__value">
                        <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" style="transform: translateY(10px);"
                             focusable="false" aria-hidden="true">
                            <x-ecl.icon icon="list"/>
                        </svg>
                        <span style="font-size: 1.5rem; margin-left:10px;">Most Reported Violations</span></div>
                    @foreach($top_categories as $top_category)
                        <div class="ecl-fact-figures__title">{{$loop->iteration}}
                            . {{ Statement::STATEMENT_CATEGORIES[$top_category->value] }}</div>
                    @endforeach
                </div>


                <div class="ecl-fact-figures__item">
                    <div class="ecl-fact-figures__value">
                        <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" style="transform: translateY(10px);"
                             focusable="false" aria-hidden="true">
                            <x-ecl.icon icon="list"/>
                        </svg>
                        <span style="font-size: 1.5rem; margin-left:10px;">Top Restriction Types</span></div>
                    @foreach($top_decisions_visibility as $top_decision_visibility)
                        <div class="ecl-fact-figures__title">{{$loop->iteration}}
                            . {{ $top_decision_visibility }}</div>
                    @endforeach
                </div>

                <div class="ecl-fact-figures__item">
                    <div class="ecl-fact-figures__value">
                        <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" style="transform: translateY(10px);"
                             focusable="false" aria-hidden="true">
                            <x-ecl.icon icon="data"/>
                        </svg>
                        <span style="font-size: 1.5rem">@aif($platforms_total)</span></div>
                    <div class="ecl-fact-figures__title">Number of active platforms</div>
                </div>


                <div class="ecl-fact-figures__item">
                    <div class="ecl-fact-figures__value">
                        <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" style="transform: translateY(10px);"
                             focusable="false" aria-hidden="true">
                            <x-ecl.icon icon="growth"/>
                        </svg>
                        <span style="font-size: 1.5rem">73%</span></div>
                    <div class="ecl-fact-figures__title">of fully automated decisions</div>
                </div>


            </div>
        </div>

    </div>
@endsection
