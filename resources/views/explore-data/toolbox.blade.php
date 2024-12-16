@extends('layouts/ecl')

@section('title', 'Toolbox')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Explore Data"/>
    <x-ecl.breadcrumb label="Toolbox" more="true"/>
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Toolbox</h1>

    <div class="ecl-row ecl-u-mt-l">
        <div class="ecl-col-l-8">
            <p class="ecl-u-type-paragraph">
                To streamline the analysis of data downloaded from the DSA Transparency Database, you can use the
                open-source dsa-tdb python package. The package allows to efficiently carry out a number of data pre-
                and post-processing tasks at scale thanks to its high-performance data processing backend. Specifically,
                the package allows you to:
            </p>
            <p class="ecl-u-type-paragraph">
            <ul class="ecl-unordered-list ecl-unordered-list--no-bullet ecl-u-mb-l">


                <li class="ecl-unordered-list__item">Easily download the daily download files, perform their checksum
                    verification and convert them into
                    data processing-ready csv or parquet files.
                </li>

                <li class="ecl-unordered-list__item">Filter and/or aggregate the statements of reasons across
                    user-selected variables from the database
                    schema to create bespoke datasets for advanced visualisations or to answer advanced research
                    questions.
                </li>

                <li class="ecl-unordered-list__item">Develop ad-hoc dashboards and visualisations based on the
                    aggregated data using the Apache Superset
                    framework.
                </li>
            </ul>
            </p>

            <p class="ecl-u-type-paragraph">

                Depending on your technical level and preferences, you can access these functionalities
            <ul class="ecl-unordered-list ecl-u-mb-l">
                <li class="ecl-unordered-list__item">via the high-level command line interface;</li>
                <li class="ecl-unordered-list__item">through a jupyter notebook, directly using the module’s python
                    bindings;
                </li>
                <li class="ecl-unordered-list__item">through fully functional APIs, either programmatically or using an
                    interactive web-based
                    interface.
                </li>
            </ul>
            </p>
            <p class="ecl-u-type-paragraph">
                To access the package as well as its full technical documentation, you can visit the <a
                    href="https://code.europa.eu/dsa/transparency-database/dsa-tdb">dsa-tdb page on
                    code.europa.eu</a>.
            </p>
            <p class="ecl-u-type-paragraph">
                If you use the data from the DSA Transparency Database for your research work, please cite it using the
                following information:
            </p>
            <p class="ecl-u-type-m" style="font-size: 0.85em;">

                European Commission-DG CONNECT, Digital Services Act Transparency Database, Directorate-General for
                Communications Networks, Content and Technology, 2023<br/>

                <a href="https://doi.org/10.2906/134353607485211">https://doi.org/10.2906/134353607485211</a>


            </p>


        </div>
        <div class="ecl-col-l-4">

            <div class="ecl-media-container">
                <figure class="ecl-media-container__figure">
                    <div class="ecl-media-container__caption">
                        <picture class="ecl-picture ecl-media-container__picture"><img
                                class="ecl-media-container__media"
                                src="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-image-2.jpeg"
                                alt="Digital Services Act Logo"></picture>
                    </div>
                </figure>
            </div>
        </div>

    </div>

@endsection
