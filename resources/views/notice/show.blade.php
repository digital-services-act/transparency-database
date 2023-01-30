@extends('layouts/ecl')

@section('content')

    <div class="ecl-page-header">
        <div class="ecl-container">
            <nav class="ecl-breadcrumb ecl-page-header__breadcrumb" aria-label="You&#x20;are&#x20;here&#x3A;"
                 data-ecl-breadcrumb="true" data-ecl-auto-init="Breadcrumb">
                <ol class="ecl-breadcrumb__container" style="padding:0">
                    <li class="ecl-breadcrumb__segment" data-ecl-breadcrumb-item="static"><a href="{{route('home')}}"
                                                                                             class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-breadcrumb__link">Home</a>
                        <svg class="ecl-icon ecl-icon--2xs ecl-icon--rotate-90 ecl-breadcrumb__icon" focusable="false"
                             aria-hidden="true" role="presentation">
                            <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>
                        </svg>
                    </li>
                    {{--                    <li class="ecl-breadcrumb__segment ecl-breadcrumb__segment--ellipsis" data-ecl-breadcrumb-ellipsis><button class="ecl-button ecl-button--ghost ecl-breadcrumb__ellipsis" type="button" data-ecl-breadcrumb-ellipsis-button aria-label="Click&#x20;to&#x20;expand">…</button><svg class="ecl-icon ecl-icon--2xs ecl-icon--rotate-90 ecl-breadcrumb__icon" focusable="false" aria-hidden="true" role="presentation">--}}
                    {{--                            <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>--}}
                    {{--                        </svg></li>--}}
                    {{--                    <li class="ecl-breadcrumb__segment" data-ecl-breadcrumb-item="expandable"><a href="/component-library/example#fopxc" class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-breadcrumb__link">About the European Commission</a><svg class="ecl-icon ecl-icon--2xs ecl-icon--rotate-90 ecl-breadcrumb__icon" focusable="false" aria-hidden="true" role="presentation">--}}
                    {{--                            <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>--}}
                    {{--                        </svg></li>--}}
                    {{--                    <li class="ecl-breadcrumb__segment" data-ecl-breadcrumb-item="expandable"><a href="/component-library/example#md9o5" class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-breadcrumb__link">Organisational structure</a><svg class="ecl-icon ecl-icon--2xs ecl-icon--rotate-90 ecl-breadcrumb__icon" focusable="false" aria-hidden="true" role="presentation">--}}
                    {{--                            <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>--}}
                    {{--                        </svg></li>--}}
                    {{--                    <li class="ecl-breadcrumb__segment" data-ecl-breadcrumb-item="static"><a href="/component-library/example#zkh7p" class="ecl-link ecl-link--standalone ecl-link--no-visited ecl-breadcrumb__link">How the Commission is organised</a><svg class="ecl-icon ecl-icon--2xs ecl-icon--rotate-90 ecl-breadcrumb__icon" focusable="false" aria-hidden="true" role="presentation">--}}
                    {{--                            <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>--}}
                    {{--                        </svg></li>--}}
                    <li class="ecl-breadcrumb__segment ecl-breadcrumb__current-page" data-ecl-breadcrumb-item="static"
                        aria-current="page">Notice of Statement
                    </li>
                </ol>
            </nav>
            <div style="padding:0;position:relative;">
                {{--                <div class="ecl-container">--}}
                {{--                <div class="ecl-page-header__meta"><span--}}
                {{--                        class="ecl-page-header__meta-item">Notice of Statement Details</span><span--}}
                {{--                        class="ecl-page-header__meta-item">{{$notice->date_sent}}</span>--}}

                {{--                </div>--}}
                <div class="ecl-page-header__title-container">
                    <h1 class="ecl-page-header__title">{{$notice->title}}</h1>
                </div>
            </div>
        </div>
    </div>
    <div>

        @if(! $notice->entities->isEmpty())
            <div class="ecl-container">
                <h3 class="ecl-u-type-heading-3">Entities</h3>
                @foreach($notice->entities as $entity)
                    {{--                    <div class="ecl-row">--}}
                    {{--                        <div class="ecl-col-1">--}}
                    {{--                            <div--}}
                    {{--                                class="ecl-u-pa-xs ecl-u-type-l ecl-u-type-bold">--}}
                    {{--                                Entity--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}
                    {{--                        <div class="ecl-col-11">--}}
                    <div
                        class="ecl-u-pa-xs ecl-u-type-l">
                        <span class="ecl-label ecl-label--medium">{{$entity->kind}}</span> {{$entity->name}} <span
                            class="ecl-u-pa-xs ecl-u-type-xs">({{$entity->pivot->role}})</span>
                    </div>
                    {{--                        </div>--}}
                    {{--                    </div>--}}
                @endforeach
            </div>
        @endif

        <div class="ecl-container">
            <h3 class="ecl-u-type-heading-3">Main Information</h3>

            <x-info-line title="Sent by" :content="$notice->user->name"></x-info-line>
            <x-info-line title="Method" :content="$notice->method"></x-info-line>
            <x-info-line title="Language" :content="$notice->language"></x-info-line>
            <x-info-line title="Body" :content="$notice->body"></x-info-line>
            <x-info-line title="Restriction Type" :content="$notice->restriction_type"></x-info-line>
            <x-info-line title="Restriction Type Other" :content="$notice->restriction_type_other"></x-info-line>


            @if($notice->date_sent)
                <x-info-line title="Date Sent" :content="$notice->date_sent->format('d-m-Y')"></x-info-line>
            @endif

            @if($notice->date_enacted)
                <x-info-line title="Start Date" :content="$notice->date_enacted->format('d-m-Y')"></x-info-line>
            @endif

            @if($notice->date_abolished)
                <x-info-line title="End Date" :content="$notice->date_abolished->format('d-m-Y')"></x-info-line>
            @endif


            <x-info-line title="Countries List" content="{{ implode(', ', $notice->getCountriesListNames()) }}"></x-info-line>
            <x-info-line title="Source" :content="$notice->source"></x-info-line>
            <x-info-line title="Payment Status" :content="$notice->payment_status"></x-info-line>


            <h3 class="ecl-u-type-heading-3">
                Additional Information
            </h3>

            <x-info-line title="Automated Detection" :content="$notice->automated_detection"></x-info-line>
            <x-info-line title="Automated Detection: More Info" :content="$notice->automated_detection_more"></x-info-line>
            <x-info-line title="Illegal Content Explanation" :content="$notice->illegal_content_explanation"></x-info-line>
            <x-info-line title="Illegal Content Legal Ground" :content="$notice->illegal_content_legal_ground"></x-info-line>
            <x-info-line title="Terms of Copyright Explanation" :content="$notice->toc_explanation"></x-info-line>
            <x-info-line title="Terms of Copyright Contractual Ground" :content="$notice->toc_contractual_ground"></x-info-line>
            <x-info-line title="Redress Mechanism" :content="$notice->redress"></x-info-line>
            <x-info-line title="Redress Mechanism: More Info" :content="$notice->redress_more"></x-info-line>
            
        </div>
    </div>


@endsection

