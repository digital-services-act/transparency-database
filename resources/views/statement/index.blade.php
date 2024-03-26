@extends('layouts/ecl')

@section('title', 'Statements of Reasons')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Search for Statements of Reasons"/>
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Search “Statements of reasons”</h1>

    <div class="ecl-row ecl-u-mt-l ecl-u-mb-xl">
        <div class="ecl-col-l-8">
            <div class="ecl-u-type-paragraph">
                On this page, you can search for statements of reasons submitted by providers of online platforms. You
                can use the free text search box to look for specific words within the free text fields of each
                statement of reason submitted by providers of online platforms.
                <br/>
                <br/>
                Through the advanced search button, you can easily find the statements of reasons submitted by each
                platform, and filter by several data fields, e.g. the type of restriction(s) imposed, categories and
                keywords, or the type or language of the content. Please note that only the first 10 000 results are
                paginated, and only the first 1000 statements of reasons can be exported in .csv
                format at a given time. The data is updated once every day. If you want to access a larger subset of
                statements of reasons, please visit the
                “<a class="ecl-link" href="{{route("dayarchive.index")}}">Data download</a>” page.
                To submit feedback on the content of this page and to propose additional features, please visit the
                <a class="ecl-link" href="{{route("feedback.index")}}">feedback form</a>.
            </div>
        </div>
        <div class="ecl-col-l-4">
            <div class="ecl-media-container">
                <figure class="ecl-media-container__figure">
                    <div class="ecl-media-container__caption">
                        <picture class="ecl-picture ecl-media-container__picture"><img
                                class="ecl-media-container__media"
                                src="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-image-1.jpeg"
                                alt="Digital Services Act Logo"></picture>
                    </div>
                </figure>
            </div>
        </div>
    </div>

    <div class="ecl-row">
        <div class="ecl-col-l-6">
            <x-statement.search-form-simple :similarity_results="$similarity_results"/>
        </div>
        <div class="ecl-col-l-4">
            <a href="{{ route('statement.search', request()->query()) }}" class="ecl-button ecl-button--secondary">
                Advanced Search
            </a>
        </div>
    </div>



    <div class="ecl-u-pt-l ecl-u-d-inline-flex ecl-u-align-items-center ecl-u-f-r">

        <div class="ecl-u-type-paragraph ecl-u-mr-s">
            Statements of Reasons @if(!$reindexing)Found: {{ $total }} @endif
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


    <x-statement.table :statements="$statements"/>

@endsection

