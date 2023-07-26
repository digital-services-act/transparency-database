@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Statement Details - ' . $statement->title)

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Statements" url="{{ route('statement.index') }}"/>
    <x-ecl.breadcrumb label="Statement details: {{$statement->uuid}}"/>
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statement Details: {{$statement->uuid}}</h1>

    @if($statement->user && $statement->user->platform)
        <x-infoline title="Sent by" :content="$statement->user->platform->name"></x-infoline>
    @endif

    <x-infoline title="Received" :content="$statement->created_at->format('d-m-Y g:i A')"></x-infoline>

    @if(!is_null($statement->decision_visibility))
        <x-infoline :title="Statement::LABEL_STATEMENT_DECISION_VISIBILITY"
                    :content="Statement::DECISION_VISIBILITIES[$statement->decision_visibility]"></x-infoline>
        @if($statement->decision_visibility == 'DECISION_VISIBILITY_OTHER')
            <x-infoline title="" :content="$statement->decision_visibility_other"></x-infoline>
        @endif
    @endif

    @if(!is_null($statement->decision_monetary))
        <x-infoline :title="Statement::LABEL_STATEMENT_DECISION_MONETARY"
                    :content="Statement::DECISION_MONETARIES[$statement->decision_monetary]"></x-infoline>
        @if($statement->decision_monetary === 'DECISION_MONETARY_OTHER')
            <x-infoline title="" :content="$statement->decision_monetary_other"></x-infoline>
        @endif
    @endif

    @if(!is_null($statement->decision_provision))
        <x-infoline :title="Statement::LABEL_STATEMENT_DECISION_PROVISION"
                    :content="Statement::DECISION_PROVISIONS[$statement->decision_provision]"></x-infoline>
    @endif

    @if(!is_null($statement->decision_account))
    <x-infoline :title="Statement::LABEL_STATEMENT_DECISION_ACCOUNT"
                :content="Statement::DECISION_ACCOUNTS[$statement->decision_account]"></x-infoline>
    @endif

    <x-infoline :title="Statement::LABEL_STATEMENT_DECISION_FACTS" :content="$statement->decision_facts"></x-infoline>

    <x-infoline :title="Statement::LABEL_STATEMENT_DECISION_GROUND"
                :content="Statement::DECISION_GROUNDS[$statement->decision_ground]"></x-infoline>

    @if($statement->decision_ground == 'DECISION_GROUND_ILLEGAL_CONTENT')
        <x-infoline :title="Statement::LABEL_STATEMENT_ILLEGAL_CONTENT_GROUND"
                    :content="$statement->illegal_content_legal_ground"></x-infoline>
        <x-infoline :title="Statement::LABEL_STATEMENT_ILLEGAL_CONTENT_EXPLANATION"
                    :content="$statement->illegal_content_explanation"></x-infoline>
    @endif

    @if($statement->decision_ground == 'DECISION_GROUND_INCOMPATIBLE_CONTENT')
        <x-infoline :title="Statement::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_GROUND"
                    :content="$statement->incompatible_content_ground"></x-infoline>
        <x-infoline :title="Statement::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_EXPLANATION"
                    :content="$statement->incompatible_content_explanation"></x-infoline>
        <x-infoline :title="Statement::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_ILLEGAL"
                    :content="$statement->incompatible_content_illegal?'Yes':'No'"></x-infoline>
    @endif

    <x-infoline :title="Statement::LABEL_STATEMENT_TERRITORIAL_SCOPE"
                content="{{ implode(', ', $statement_territorial_scope_country_names) }}"></x-infoline>

    <x-infoline :title="Statement::LABEL_STATEMENT_CONTENT_TYPE"
                :content="Statement::CONTENT_TYPES[$statement->content_type]"></x-infoline>
    @if($statement->content_type == 'CONTENT_TYPE_OTHER')
        <x-infoline title="" :content="$statement->content_type_other"></x-infoline>
    @endif


    <x-infoline :title="Statement::LABEL_STATEMENT_CATEGORY"
                :content="Statement::STATEMENT_CATEGORIES[$statement->category]"></x-infoline>

    <x-infoline :title="Statement::LABEL_STATEMENT_URL" :content="$statement->url"></x-infoline>

    <x-infoline :title="Statement::LABEL_STATEMENT_SOURCE_TYPE"
                :content="Statement::SOURCE_TYPES[$statement->source_type]"></x-infoline>
    @if($statement->source_type != 'SOURCE_VOLUNTARY')
        <x-infoline :title="Statement::LABEL_STATEMENT_SOURCE" :content="$statement->source"></x-infoline>
    @endif

    <x-infoline :title="Statement::LABEL_STATEMENT_AUTOMATED_DETECTION"
                :content="$statement->automated_detection"></x-infoline>
    <x-infoline :title="Statement::LABEL_STATEMENT_AUTOMATED_DECISION"
                :content="$statement->automated_decision"></x-infoline>

    @if($statement->start_date)
        <x-infoline :title="Statement::LABEL_STATEMENT_START_DATE"
                    :content="$statement->start_date->format('d-m-Y')"></x-infoline>
    @endif

    @if($statement->end_date)
        <x-infoline :title="Statement::LABEL_STATEMENT_END_DATE"
                    :content="$statement->end_date->format('d-m-Y')"></x-infoline>
    @else
        <x-infoline :title="Statement::LABEL_STATEMENT_END_DATE" content="indefinite"></x-infoline>
    @endif

@endsection

