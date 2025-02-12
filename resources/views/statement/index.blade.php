@extends('layouts/ecl')

@section('title', 'Statements of Reasons')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Search “Statements of reasons”" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Search “Statements of reasons”</h1>

    <div class="ecl-row ecl-u-mt-l ecl-u-mb-xl">
        <div class="ecl-col-l-12">

            <div class="ecl-u-type-paragraph" style="max-width:none !important">
                On this page, you can search for individual statements of reasons submitted by providers of online
                platforms.<br />
                <br />
                To learn more about the available variables in each statement of reasons, you can read the full
                <a class="ecl-link" href="/page/api-documentation#statement-attributes">technical documentation of the
                    database schema</a>
                as well as <a class="ecl-link" href="/page/additional-explanation-for-statement-attributes">additional
                    explanations</a> about each variable, detailing why they are
                included.<br />
                <br />
                Please note that the search results returned cover the last 6 months of data submitted, in line with the
                <a class="ecl-link" href="/page/data-retention-policy">Data Retention Policy</a>.
                Data is updated once every day and only the first 10 000 results are paginated. The first 1000
                statements of
                reasons among those can be manually exported in .csv format.<br />
                <br />
                If you are interested in a programmatic search instead, you can our Search API.
            </div>

            <x-ecl.accordion label="How Search works">
                You can use the free-text search to look for specific words within the free text fields of each statement of
                reasons.
                Searching for the platform name in the free-text search only returns statements of reasons which contain the
                platform
                name entered in one of their free-text fields. To search for statements of reasons submitted by a specific
                online
                platform, please use the “Platform” dropdown selector to select one or more platforms of interest
                instead.<br />
                <br />
                To filter statements of reasons by (a combination of) other variables, in addition to the platform they were
                submitted
                by, the type of restriction(s) imposed, their information source and their category, please click on
                “Advanced Filter”
                to expand the additional search filters.
            </x-ecl.accordion>

        </div>
    </div>

    <hr class="ecl-separator">

    <x-statement.search-form :similarity_results="$similarity_results" :options="$options" />



    <div class="ecl-u-mt-l ecl-u-d-flex ecl-u-justify-content-between">

        <div class="item ecl-u-type-paragraph">
            @if (!$reindexing)
                Statements of Reasons: {{ $total }}
            @else
                Statements of Reasons Found: {{ $total }}
            @endif
        </div>


        <div class="item ecl-u-type-paragraph">
            <a href="{{ route('statement.export', request()->query()) }}"
                class="ecl-link ecl-link--default ecl-link--icon ecl-link--icon-after">
                <span class="ecl-link__label">Download a sample of 1000 results in CSV</span>
                <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="download" />
                </svg>
            </a>
        </div>
    </div>


    <x-statement.table :statements="$statements" />

@endsection
