@extends('layouts/ecl')

@section('title', 'Notice Details - ' . $notice->title)

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Notices" url="{{ route('notice.index') }}" />
    <x-ecl.breadcrumb label="Notice details: {{$notice->title}}" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1">Notice Details: {{$notice->title}}</h1>

    @if(! $notice->entities->isEmpty())

        <h2 class="ecl-u-type-heading-2">Entities</h2>
        @foreach($notice->entities as $entity)
            <div class="ecl-u-pa-xs ecl-u-type-l">
                <span class="ecl-label ecl-label--medium">{{$entity->kind}}</span>
                {{$entity->name}}
                <span class="ecl-u-pa-xs ecl-u-type-xs">({{$entity->pivot->role}})</span>
            </div>
        @endforeach

    @endif


    <h2 class="ecl-u-type-heading-2">Main Information</h2>

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


    <h2 class="ecl-u-type-heading-2">Additional Information</h2>

    <x-info-line title="Automated Detection" :content="$notice->automated_detection"></x-info-line>
    <x-info-line title="Automated Detection: More Info" :content="$notice->automated_detection_more"></x-info-line>
    <x-info-line title="Illegal Content Explanation" :content="$notice->illegal_content_explanation"></x-info-line>
    <x-info-line title="Illegal Content Legal Ground" :content="$notice->illegal_content_legal_ground"></x-info-line>
    <x-info-line title="Terms of Copyright Explanation" :content="$notice->toc_explanation"></x-info-line>
    <x-info-line title="Terms of Copyright Contractual Ground" :content="$notice->toc_contractual_ground"></x-info-line>
    <x-info-line title="Redress Mechanism" :content="$notice->redress"></x-info-line>
    <x-info-line title="Redress Mechanism: More Info" :content="$notice->redress_more"></x-info-line>


@endsection

