@extends('layouts/ecl')

@section('title', 'Statement Details - ' . $statement->title)

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Statements" url="{{ route('statement.index') }}"/>
    <x-ecl.breadcrumb label="Statement details: {{$statement->title}}"/>
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statement Details: {{$statement->uuid}}</h1>

    @if($statement->user && $statement->user->platform)
        <x-info-line title="Sent by" :content="$statement->user->platform->name"></x-info-line>
    @endif

    @if($statement->user && $statement->user->platform)
        <x-info-line title="Platform Type" :content="\App\Models\Platform::PLATFORM_TYPES[$statement->user->platform->type]"></x-info-line>
    @endif

    <x-info-line title="Received" :content="$statement->created_at->format('d-m-Y g:i A')"></x-info-line>

    @if($statement->decision_visibility)
    <x-info-line title="Decision Taken (content visibility)" :content="\App\Models\Statement::DECISIONS_VISIBILITY[$statement->decision_visibility]"></x-info-line>
    @endif
    @if($statement->decision_monetary)
    <x-info-line title="Decision Taken (monetary)" :content="\App\Models\Statement::DECISIONS_MONETARY[$statement->decision_monetary]"></x-info-line>
    @endif
    @if($statement->decision_provision)
    <x-info-line title="Decision Taken (provision of service)" :content="\App\Models\Statement::DECISIONS_PROVISION[$statement->decision_provision]"></x-info-line>
    @endif
    @if($statement->decision_account)
    <x-info-line title="Decision Taken (account status)" :content="\App\Models\Statement::DECISIONS_ACCOUNT[$statement->decision_account]"></x-info-line>
    @endif

    <x-info-line title="Facts and circumstances relied on in taking the decision" :content="$statement->decision_facts"></x-info-line>

    <x-info-line title="Ground for Decision" :content="\App\Models\Statement::DECISION_GROUNDS[$statement->decision_ground]"></x-info-line>

    @if($statement->decision_ground == 'ILLEGAL_CONTENT')
        <x-info-line title="Legal ground relied on" :content="$statement->illegal_content_legal_ground"></x-info-line>
        <x-info-line title="Explanation of why the content is considered to be illegal on that ground" :content="$statement->illegal_content_explanation"></x-info-line>
    @endif

    @if($statement->decision_ground == 'INCOMPATIBLE_CONTENT')
        <x-info-line title="Reference to contractual ground" :content="$statement->incompatible_content_ground"></x-info-line>
        <x-info-line title="Explanation of why the content is considered as incompatible on that ground" :content="$statement->incompatible_content_explanation"></x-info-line>
        <x-info-line title="Is the content also illegal ?" :content="$statement->incompatible_content_illegal?'Yes':'No'"></x-info-line>
    @endif



    <x-info-line title="Content Type" :content="\App\Models\Statement::CONTENT_TYPES[$statement->content_type]"></x-info-line>

    <x-info-line title="Category" :content="\App\Models\Statement::SOR_CATEGORIES[$statement->category]"></x-info-line>

    <x-info-line title="URL" :content="$statement->url"></x-info-line>

    <x-info-line title="Decision source" :content="\App\Models\Statement::SOURCES[$statement->source]"></x-info-line>

{{--    @if($statement->source == 'SOURCE_ARTICLE_16' && $statement->source_explanation)--}}
{{--        <x-info-line title="Article 16 Explanation" :content="$statement->source_explanation"></x-info-line>--}}
{{--    @endif--}}

{{--    @if($statement->source == 'SOURCE_VOLUNTARY' && $statement->source_explanation)--}}
{{--        <x-info-line title="Own Voluntary Source Explanation" :content="$statement->source_explanation"></x-info-line>--}}
{{--    @endif--}}

    <x-info-line title="Automated Detection" :content="$statement->automated_detection"></x-info-line>
    <x-info-line title="Automated Decision" :content="$statement->automated_decision"></x-info-line>
{{--    <x-info-line title="Automated Take-down" :content="$statement->automated_takedown"></x-info-line>--}}

    @if($statement->start_date)
        <x-info-line title="Abolished since" :content="$statement->start_date->format('d-m-Y')"></x-info-line>
    @endif

    @if($statement->end_date)
        <x-info-line title="Abolished until" :content="$statement->end_date->format('d-m-Y')"></x-info-line>
    @else
        <x-info-line title="Abolished until" content="indefinite"></x-info-line>
    @endif

@endsection

