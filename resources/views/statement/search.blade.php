@extends('layouts/ecl')

@section('title', 'Statements')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Search for Statements of Reasons" url="{{ route('statement.index') }}" />
    <x-ecl.breadcrumb label="Advanced Search" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statements of Reasons: Advanced Search</h1>

    <p class="ecl-u-type-paragraph">
        On this page, you can search for statements of reasons submitted by providers
        of online platforms. You can use the free text search box to look for specific
        words <strong>within the free text fields of each statement of reasons</strong> submitted by
        providers of online platforms-to be specific, searching for the platform name
        in that box does not guarantee that you will get that platform's data, please
        use the “Platform” dropdown selector to select one or more platforms.
        The data is updated once every day and a Data Retention Policy applies.
    </p>
    <p class="ecl-u-type-paragraph">
        You can easily filter the statements of reasons submitted by each platform,
        and filter by several data fields, e.g. the type of restriction(s) imposed,
        categories and keywords, or the type or language of the content.
    </p>

    <p class="ecl-u-type-paragraph">
        Please note that only the first 10 000 results are paginated, and only the first 
        1000 statements of reasons amongst those can be exported in .csv format at a given time.
    </p>
    <p class="ecl-u-type-paragraph">
        If you want to access a larger subset of statements of reasons or an aggregate view of them, 
        please visit the <a class="ecl-link" href="{{ route('dayarchive.index') }}">Data download</a> page or 
        have a look at the <a class="ecl-link" href="{{ route('page.show', ['page' => 'data-analysis-software'])}}">dsa-tdb</a> software package to
        streamline the data download and processing.
    </p>


    <x-statement.search-form :options="$options" />

@endsection