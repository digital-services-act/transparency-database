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

    @if($statement->user)
        <x-info-line title="Sent by" :content="$statement->user?->name"></x-info-line>
    @endif

    <x-info-line title="Decision Taken" :content="\App\Models\Statement::DECISIONS[$statement->decision_taken]"></x-info-line>

    <x-info-line title="Ground for Decision" :content="\App\Models\Statement::DECISION_GROUNDS[$statement->decision_ground]"></x-info-line>
    <x-info-line title="Legal ground relied on" :content="$statement->illegal_content_legal_ground"></x-info-line>
    <x-info-line title="Explanation of why the content is considered to be illegal on that ground" :content="$statement->illegal_content_explanation"></x-info-line>
    <x-info-line title="Reference to contractual ground" :content="$statement->incompatible_content_ground"></x-info-line>
    <x-info-line title="Explanation of why the content is considered as incompatible on that ground" :content="$statement->incompatible_content_explanation"></x-info-line>

        <h2 class="ecl-u-type-heading-2">Additional Information</h2>

    <x-info-line title="Facts and circumstances relied on in taking the decision" :content="\App\Models\Statement::SOURCES[$statement->source]"></x-info-line>
    <x-info-line title="Only if strictly necessary, identity of the notifier" :content="$statement->source_identity"></x-info-line>
    <x-info-line title="Other Source" :content="$statement->source_other"></x-info-line>

        <x-info-line title="Automated Detection" :content="$statement->automated_detection"></x-info-line>

    @if($statement->date_abolished)
        <x-info-line title="Valid until" :content="$statement->date_abolished->format('d-m-Y')"></x-info-line>
    @endif

    <x-info-line title="Method" :content="$statement->method"></x-info-line>
    @if($statement->redress)
    <x-info-line title="Information on possible redress available to the recipient of the decision taken" :content="\App\Models\Statement::REDRESSES[$statement->redress]"></x-info-line>
    <x-info-line title="More Information on redress" :content="$statement->redress_more"></x-info-line>
    @endif



@endsection

