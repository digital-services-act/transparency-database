@extends('layouts/ecl')


@if($platform)
    @section('title', 'Download for ' . $platform->name)
@else
    @section('title', 'Download')
@endif


@section('breadcrumbs')
<x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
<x-ecl.breadcrumb label="Explore Data" />
<x-ecl.breadcrumb label="Download" more="true" />
@endsection


@section('content')

@if($platform)
    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Download for {{ $platform->name }}</h1>
@else
    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Download</h1>
@endif

<x-ecl.message type="info" icon="information" title="New file formats & aggregated data available for download"
    message="You can now download the global version of the full daily files in Parquet format. You can also download daily aggregates of statements of reasons submissions in csv or Parquet format. For more information and to download the data aggregates, visit <a class='ecl-link' href='https://dsa.pages.code.europa.eu/transparency-database/dsa-tdb/index.html' target='_blank'>the dsa-tdb package documentation</a>."
    :close="true" />

<div class="ecl-row ecl-u-mt-l">
    <div class="ecl-col-l-8">
        <p class="ecl-u-type-paragraph">
            On this page, you can download zipped csv files containing the daily submissions of statements of reasons,
            either for
            all platforms collectively or for each platform individually. The files are provided in full and light
            versions.<br />
            <br />

            You can also download these files in Parquet format for the full, global version containing all the
            platforms' data.
            Please refer to the <a class="ecl-link" href="https://digital-strategy.ec.europa.eu/en/faqs/dsa-transparency-database-questions-and-answers#ecl-inpage-technical-faq" target="_blank">technical FAQ</a> or to the dsa-tdb package documentation to learn how to download these
            files programmatically.<br />
            <br />


            Full archive files contain all the public data points of each individual statement of reasons submitted on a
            given day. That is, each file contains the entire attribute schema of the database. Light archive files only contain a
            subset of
            the attribute schema of the database, notably no free text fields with a character limit higher than 2000
            characters
            (i.e. illegal_content_explanation, incompatible_content_explanation or decision_facts). Light archive files
            also do not
            contain the territorial_scope attribute.<br />
            <br />


            To download a compact, aggregated version of the data in csv or <a class="ecl-link" href="https://parquet.apache.org/" target="_blank">Parquet</a> format, reporting the number of
            statements of
            seasons sharing the same categorical features, you can visit the <a class="ecl-link" href="https://dsa.pages.code.europa.eu/transparency-database/dsa-tdb/index.html" target="_blank">dsa-tdb package documentation</a>. Two types of
            aggregations
            are provided: the basic aggregation currently feeding the <a class="ecl-link" href="/dashboard">Dashboard</a> and an advanced one aggregating the data
            on all the
            database's non-free-text fields columns.<br />
            <br />
            Please note that a <a class="ecl-link" href="/page/data-retention-policy">Data Retention Policy</a> applies to all files available for download.
        </p>

    </div>
    <div class="ecl-col-l-4">

        <div class="ecl-media-container">
            <figure class="ecl-media-container__figure">
                <div class="ecl-media-container__caption">
                    <picture class="ecl-picture ecl-media-container__picture"><img
                            class="ecl-media-container__media"
                            src="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/sor-graph.png"
                            alt="Digital Services Act"></picture>
                </div>
            </figure>
        </div>
    </div>

</div>
<form method="get" id="platform">
    <div class="ecl-row ecl-u-mt-l" style="border-width: 50px">

        <div class="ecl-col-l-2">
            <x-ecl.datepicker label="From" id="from_date" justlabel="true" name="from_date"
                :value="request()->get('from_date', '')" />
        </div>
        <div class="ecl-col-l-2">
            <x-ecl.datepicker label="To" id="to_date" justlabel="true" name="to_date" :value="request()->get('to_date', '')" />
        </div>
        <div class="ecl-col-l-4">
            <x-ecl.select label="Select a Platform" name="uuid" id="uuid" justlabel="true"
                :options="$options['platforms']" :default="request()->get('uuid', '')" />

        </div>
        <div class="ecl-col-l-2 ecl-u-align-content-center">
            <div class="ecl-form-group" style="margin-top: 24px;">
                <button class="ecl-button ecl-button--primary" type="submit">
                    <span class="ecl-button__container">
                        <span class="ecl-button__label" data-ecl-label="true">
                            Search
                        </span>
                        <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-button__icon ecl-button__icon--after"
                            focusable="false" aria-hidden="true" data-ecl-icon="">
                            <x-ecl.icon icon="corner-arrow" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>

    </div>
</form>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var form = document.getElementById("platform");

        // Function to submit the form
        function submitForm() {
            form.submit();
        }

        // Attach event listeners to input fields
        var fromInput = document.getElementById("from_date");
        var toInput = document.getElementById("to_date");
        var platformInput = document.getElementById("uuid");

        fromInput.addEventListener("change", submitForm);
        toInput.addEventListener("change", submitForm);
        platformInput.addEventListener("change", submitForm);
    });

</script>

<x-dayarchive.table :dayarchives="$dayarchives" :reindexing="$reindexing" />

@endsection
