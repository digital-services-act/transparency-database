@extends('layouts/ecl')

@section('title', 'Statement Details - ' . $statement->title)

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Statements" url="{{ route('statement.index') }}" />
    <x-ecl.breadcrumb label="Statement details: {{$statement->title}}" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statement Details: {{$statement->title}}</h1>

    @if(! $statement->entities->isEmpty())

        <h2 class="ecl-u-type-heading-2">Entities</h2>
        @foreach($statement->entities as $entity)
            <div class="ecl-u-pa-xs ecl-u-type-l">
                <span class="ecl-label ecl-label--medium">{{$entity->kind}}</span>
                {{$entity->name}}
                <span class="ecl-u-pa-xs ecl-u-type-xs">({{$entity->pivot->role}})</span>
            </div>
        @endforeach

    @endif


    <h2 class="ecl-u-type-heading-2">Main Information</h2>

    <x-info-line title="Sent by" :content="$statement->user->name"></x-info-line>
    <x-info-line title="Method" :content="$statement->method"></x-info-line>
    <x-info-line title="Language" :content="$statement->language"></x-info-line>
    <x-info-line title="Body" :content="$statement->body"></x-info-line>
    <x-info-line title="Restriction Type" :content="$statement->restriction_type"></x-info-line>
    <x-info-line title="Restriction Type Other" :content="$statement->restriction_type_other"></x-info-line>


    @if($statement->date_sent)
        <x-info-line title="Date Sent" :content="$statement->date_sent->format('d-m-Y')"></x-info-line>
    @endif

    @if($statement->date_enacted)
        <x-info-line title="Start Date" :content="$statement->date_enacted->format('d-m-Y')"></x-info-line>
    @endif

    @if($statement->date_abolished)
        <x-info-line title="End Date" :content="$statement->date_abolished->format('d-m-Y')"></x-info-line>
    @endif


    <x-info-line title="Countries List" content="{{ implode(', ', $statement->getCountriesListNames()) }}"></x-info-line>
    <x-info-line title="Source" :content="$statement->source"></x-info-line>
    <x-info-line title="Payment Status" :content="$statement->payment_status"></x-info-line>


    <h2 class="ecl-u-type-heading-2">Additional Information</h2>

    <x-info-line title="Automated Detection" :content="$statement->automated_detection"></x-info-line>
    <x-info-line title="Automated Detection: More Info" :content="$statement->automated_detection_more"></x-info-line>
    <x-info-line title="Illegal Content Explanation" :content="$statement->illegal_content_explanation"></x-info-line>
    <x-info-line title="Illegal Content Legal Ground" :content="$statement->illegal_content_legal_ground"></x-info-line>
    <x-info-line title="Terms of Copyright Explanation" :content="$statement->toc_explanation"></x-info-line>
    <x-info-line title="Terms of Copyright Contractual Ground" :content="$statement->toc_contractual_ground"></x-info-line>
    <x-info-line title="Redress Mechanism" :content="$statement->redress"></x-info-line>
    <x-info-line title="Redress Mechanism: More Info" :content="$statement->redress_more"></x-info-line>


@endsection

