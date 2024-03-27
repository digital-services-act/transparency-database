@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Home')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" />
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">Welcome to the DSA Transparency Database!</h1>

    <div class="ecl-row">
        <div class="ecl-col-l-8">

            <p class="ecl-u-type-paragraph">
                The Digital Services Act (DSA), obliges providers of hosting services to inform their users of the
                content moderation decisions they take and explain the reasons behind those decisions in
                so-called <strong>statements of reasons</strong>.
            </p>

            <p class="ecl-u-type-paragraph">
                To enhance transparency and facilitate scrutiny over content moderation decisions,
                <strong>providers of online platforms need to submit these statements of reasons to the
                    DSA Transparency Database</strong>. The database allows to track the content moderation decisions
                taken by providers of online platforms in almost real-time. It also offers various tools for
                accessing, analysing, and downloading the information that platforms need to make available when
                they take content moderation decisions, contributing to the monitoring of the dissemination
                of illegal and harmful content online.
            </p>

            <p class="ecl-u-type-paragraph">
                <x-ecl.cta-button label="More questions? Check our FAQ" url="{{ route('page.show', ['faq']) }}" />
            </p>

        </div>
        <div class="ecl-col-l-4">
            <x-ecl.media-link url="https://digital-strategy.ec.europa.eu/en/policies/digital-services-act-package"
                              label="Discover more about the <br />Digital Services Act"
                              image="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-text-logo.jpg" />
        </div>
    </div>

    <h2 class="ecl-u-type-heading-2">Overview of the Database</h2>

    <div class="ecl-row ecl-u-mb-l">
        <div class="ecl-col-l-8">
            <p class="ecl-u-type-paragraph">
                Below you can find some summary statistics on the statements of reasons submitted by providers
                of online platforms to the Commission.
            </p>
        </div>

        <div class="ecl-col-l-4">
            <div class="ecl-u-mb-l">
            <x-ecl.cta-button url="{{ route('dashboard') }}" priority="primary" label="Visualize
                the data in the dashboard" :icon="false" :fullwidth="true"/>
            </div>
            <div>
            <x-ecl.cta-button url="{{ route('statement.index') }}" priority="primary" label="Search
                for Statements of Reasons" :icon="false" :fullwidth="true" />
            </div>
        </div>

    </div>

    <div class="ecl-fact-figures ecl-fact-figures--col-3">
        <div class="ecl-fact-figures__items">
            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="data"/>
                </svg>
                <div class="ecl-fact-figures__value">@aif($total)</div>
                <div class="ecl-fact-figures__title">Total number of statements of reasons submitted</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="list"/>
                </svg>
                <div class="ecl-fact-figures__value">Most Reported Violations</div>
                <div class="ecl-fact-figures__description">
                    <ol class="ecl-ordered-list">
                    @foreach($top_categories as $top_category)
                        <li class="ecl-ordered-list__item">
                            {{ Statement::STATEMENT_CATEGORIES[$top_category['value']] }}
                        </li>
                    @endforeach
                    </ol>
                </div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="list"/>
                </svg>
                <div class="ecl-fact-figures__value">Top Restriction Types</div>
                <div class="ecl-fact-figures__description">
                    <ol class="ecl-ordered-list">

                        @foreach($top_decisions_visibility as $top_decision_visibility)
                            <li class="ecl-ordered-list__item">
                                {{ Statement::DECISION_VISIBILITIES[$top_decision_visibility['value']] }}
                            </li>
                        @endforeach

                    </ol>
                </div>
            </div>


            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="data"/>
                </svg>
                <div class="ecl-fact-figures__value">@aif($platforms_total)</div>
                <div class="ecl-fact-figures__title">Number of active platforms</div>
            </div>


            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth"/>
                </svg>
                <div class="ecl-fact-figures__value">@aif($automated_decision_percentage)%</div>
                <div class="ecl-fact-figures__title">of fully automated decisions</div>
            </div>

        </div>
    </div>

@endsection
