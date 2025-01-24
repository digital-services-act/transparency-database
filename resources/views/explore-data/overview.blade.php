@extends('layouts/ecl')

@section('title', 'Explore Data Overview')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Explore Data" />
    <x-ecl.breadcrumb label="Overview" more="true" />
@endsection

@section('content')

<h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Explore Data Overview</h1>

<div class="ecl-row ecl-u-mt-l">
    <div class="ecl-col-l-8">
        <p class="ecl-u-type-paragraph">
            To access the full data contained in the DSA Transparency Database you can visit the
            <a class="ecl-link" href="{{ route('dayarchive.index') }}">Data Download page</a>.
        </p>
        <p class="ecl-u-type-paragraph">
            The daily files contained in the <a class="ecl-link" href="{{ route('dayarchive.index') }}">data download page</a>
            contain the information at the level of the single Statements of Reasons and are provided in the full
            (providing the complete information for each SoR) and light version - where information on the territorial
            scope and long free text fields is dropped. These files can be downloaded in CSV format.
        </p>
        <p class="ecl-u-type-paragraph">
            <x-ecl.external-link href="https://digital-strategy.ec.europa.eu/en/faqs/dsa-transparency-database-questions-and-answers" label="Read more about the downloadable files versions in the technical FAQ" />
        </p>
        <p class="ecl-u-type-paragraph">
            If you want to automatise the downloading and management of such daily data, you can use the 
            <a class="ecl-link" href="{{ route('explore-data.toolbox') }}">dsa-tdb package</a>. The dsa-tdb package and its related applications streamline the
            download, filtering and analysis of the Transparency Database data in a user-friendly way: read more about
            it in the <x-ecl.external-link href="https://code.europa.eu/dsa/transparency-database/dsa-tdb" label="package source code repository" /> and in
            its <x-ecl.external-link label="online documentation" href="https://dsa.pages.code.europa.eu/transparency-database/dsa-tdb/" />.
        </p>
        <p class="ecl-u-type-paragraph">
            If you use the data from the DSA Transparency Database for your research work, please cite it using the
            following information:
        </p>
        <p class="ecl-u-type-paragraph">
            European Commission-DG CONNECT, Digital Services Act Transparency Database, Directorate-General for
            Communications Networks, Content and Technology, 2023.
        </p>
        <p class="ecl-u-type-paragraph">
            <x-ecl.external-link href="https://doi.org/10.2906/134353607485211" label="https://doi.org/10.2906/134353607485211" />
        </p>
    </div>
    <div class="ecl-col-l-4">

        <div class="ecl-media-container">
            <figure class="ecl-media-container__figure">
                <div class="ecl-media-container__caption">
                    <picture class="ecl-picture ecl-media-container__picture"><img class="ecl-media-container__media"
                            src="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-image-2.jpeg"
                            alt="Digital Services Act Logo"></picture>
                </div>
            </figure>
        </div>
    </div>

</div>


@endsection