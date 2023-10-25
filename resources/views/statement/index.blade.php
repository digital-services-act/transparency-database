@extends('layouts/ecl')

@section('title', 'Statements of Reasons')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Search for Statements of Reasons"/>
@endsection

@section('content')

    <div class="ecl-fact-figures ecl-fact-figures--col-1">
        <div class="ecl-fact-figures__description">
            On this page you can search for statements of reasons submitted by providers of online platforms.

            Through the advanced search button, you can easily find the statements of reasons submitted by each
            platform, and filter by several data fields, e.g. the type of restriction(s) imposed, categories and
            keywords, or the type or language of the content.

            Please note that only the first 10 000 results are paginated, and only the first 1000 statements of reasons
            can be exported in .csv format at a given time.

            To submit feedback on the content of this page and to propose additional features, please visit the
            <a href="{{ route('feedback.index') }}" class="ecl-link ecl-link--standalone">feedback page</a>.
        </div>
    </div>


    <div class="ecl-row">
        <div class="ecl-col-l-6">

            <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statements of Reasons</h1>

            @can('create statements')
                <x-ecl.cta-button label="Create a Statement of Reason" url="{{ route('statement.create') }}" />
            @endcan

            <div class="ecl-u-pt-l ecl-u-d-inline-flex ecl-u-align-items-center">
                <div class="ecl-u-type-paragraph ecl-u-mr-s">
                    Statements of Reasons Found: {{ $total }}
                </div>

                <div class="ecl-u-type-paragraph ecl-u-mr-l">

                    <a href="{{ route('statement.export', request()->query()) }}"
                       class="ecl-link ecl-link--default ecl-link--icon ecl-link--icon-after">
                        <span class="ecl-link__label">.csv</span>
                        <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
                            <x-ecl.icon icon="download"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>
        <div class="ecl-col-l-6 ecl-u-mt-l ecl-u-mb-l">

            <x-statement.search-form-simple :similarity_results="$similarity_results"/>

            @if($similarity_results)
                <div class="ecl-u-mb-l ecl-u-mt-s">
                    <span class="ecl-u-type-paragraph">
                        <strong>Similar Searches</strong>
                    </span>
                    <br />
                    @foreach($similarity_results as $result)
                        <span class="ecl-u-type-paragraph-xs">
                            <a href="?s={{ $result }}" class="ecl-link">{{ $result }}</a>
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="ecl-u-f-r ecl-u-mt-s">
                <a href="{{ route('statement.search', request()->query()) }}" class="ecl-button ecl-button--secondary">Advanced Search</a>
            </div>

        </div>
    </div>

    <x-statement.table :statements="$statements"/>

@endsection

