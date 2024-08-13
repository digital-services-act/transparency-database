@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Home')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}"/>
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">{{__('home.title')}}</h1>

    <div class="ecl-row">
        <div class="ecl-col-l-8">

            <p class="ecl-u-type-paragraph">
                {!!__('home.content.0')!!}
            </p>

            <p class="ecl-u-type-paragraph">
                {!!__('home.content.1')!!}

            </p>

            <p class="ecl-u-type-paragraph">
                <x-ecl.cta-button label="{{__('home.More questions? Check our FAQ')}}"
                                  url="https://digital-strategy.ec.europa.eu/en/faqs/dsa-transparency-database-questions-and-answers"/>
            </p>

        </div>
        <div class="ecl-col-l-4">
            <x-ecl.media-link url="https://digital-strategy.ec.europa.eu/en/policies/digital-services-act-package"
                              label="{!!  __('home.Discover more about the Digital Services Act') !!}"
                              image="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-text-logo.jpg"/>
        </div>
    </div>

    <h2 class="ecl-u-type-heading-2">{{__('home.Overview of the Database')}}</h2>

    <div class="ecl-row ecl-u-mb-l">
        <div class="ecl-col-l-8">
            <p class="ecl-u-type-paragraph">
                {!!__('home.content.2')!!}
            </p>
        </div>

        <div class="ecl-col-l-4">
            <div class="ecl-u-mb-l">
                <x-ecl.cta-button url="{{ route('dashboard') }}" priority="primary"
                                  label="{{__('home.Visualize the data in the dashboard')}}" :icon="false"
                                  :fullwidth="true"/>
            </div>
            <div>
                <x-ecl.cta-button url="{{ route('statement.index') }}" priority="primary"
                                  label="{{__('menu.Search for Statements of Reasons')}}" :icon="false"
                                  :fullwidth="true"/>
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
                <div class="ecl-fact-figures__title">{{__('home.Total number of statements of reasons submitted')}}</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="list"/>
                </svg>
                <div class="ecl-fact-figures__value">{{__('home.Most Reported Violations')}}</div>
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
                <div class="ecl-fact-figures__value">{{__('home.Top Restriction Types')}}</div>
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
                <div class="ecl-fact-figures__title">{{__('home.Number of active platforms')}}</div>
            </div>


            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth"/>
                </svg>
                <div class="ecl-fact-figures__value">@aif($automated_decision_percentage)%</div>
                <div class="ecl-fact-figures__title">{{__('home.of fully automated decisions')}}</div>
            </div>

        </div>
    </div>

@endsection
