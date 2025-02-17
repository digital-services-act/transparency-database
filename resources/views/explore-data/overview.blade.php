@extends('layouts/ecl')

@section('title', 'Explore Data Overview')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Explore Data"/>
    <x-ecl.breadcrumb label="Overview" more="true"/>
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Explore Data Overview</h1>

    <div class="ecl-row ecl-u-mt-l">
        <div class="ecl-col-l-12">

            <p class="ecl-u-type-paragraph" style="max-width:none !important">To access and explore the data contained in the DSA Transparency Database, a number of analytical tools
                are available. Depending on whether you would like to get a quick overview, search and filter for
                individual statements of reasons, look at trends or deep dive into the historical data of platforms that
                interest you, you can select the tool that best matches your needs. </p>

            <h2 class="ecl-u-type-heading-2">When to use which tool?</h2>

            <div class="ecl-table-responsive">
                <table class="ecl-table ecl-table--zebra" id="table-id">

                    <thead class="ecl-table__head">
                    <tr class="ecl-table__row">
                        <th id="table-id-header-1" scope="col" class="ecl-table__header"></th>
                        <th id="table-id-header-2" scope="col" class="ecl-table__header "><a href="/dashboard">Public Dashboard</a></th>
                        <th id="table-id-header-3" scope="col" class="ecl-table__header "><a href="/explore-data/download">Download</a> of individual & aggregated SOR files</th>
                        <th id="table-id-header-4" scope="col" class="ecl-table__header "><a href="/page/research-api">Research API</a></th>
                        <th id="table-id-header-5" scope="col" class="ecl-table__header "><a href="https://code.europa.eu/dsa/transparency-database/dsa-tdb">Dsa-tdb package</a></th>
                    </tr>
                    </thead>
                    <tbody class="ecl-table__body">
                    <tr class="ecl-table__row ">
                        <td data-ecl-table-header="Feature" class="ecl-table__cell">Technical know-how required</td>
                        <td data-ecl-table-header="Public Dashboard" class="ecl-table__cell">Low</td>
                        <td data-ecl-table-header="Download of individual & aggregated SOR files" class="ecl-table__cell">Medium
                            </td>
                        <td data-ecl-table-header="Research API" class="ecl-table__cell">High</td>
                        <td data-ecl-table-header="Dsa-tdb package" class="ecl-table__cell">High</td>
                    </tr>
                    <tr class="ecl-table__row ">
                        <td data-ecl-table-header="Feature" class="ecl-table__cell">Temporal scope of data covered</td>
                        <td data-ecl-table-header="Public Dashboard" class="ecl-table__cell">5 years</td>
                        <td data-ecl-table-header="Download of individual & aggregated SOR files" class="ecl-table__cell">5 years</td>
                        <td data-ecl-table-header="Research API" class="ecl-table__cell">6 months</td>
                        <td data-ecl-table-header="Dsa-tdb package" class="ecl-table__cell">5 years</td>
                    </tr>
                    <tr class="ecl-table__row ">
                        <td data-ecl-table-header="Feature" class="ecl-table__cell">In-depth analysis of individual statements of reasons</td>
                        <td data-ecl-table-header="Public Dashboard" class="ecl-table__cell ">
                            <svg class="ecl-icon ecl-icon--xl ecl-u-type-color-error" focusable="false" aria-hidden="true">
                                <x-ecl.icon icon="close" />
                            </svg></td>
                        <td data-ecl-table-header="Download of individual & aggregated SOR files" class="ecl-table__cell"><svg class="ecl-icon ecl-icon--xl ecl-u-type-color-success" focusable="false" aria-hidden="true">
                                <x-ecl.icon icon="check" />
                            </svg></td>
                        <td data-ecl-table-header="Research API" class="ecl-table__cell"><svg class="ecl-icon ecl-icon--xl ecl-u-type-color-success" focusable="false" aria-hidden="true">
                                <x-ecl.icon icon="check" />
                            </svg></td>
                        <td data-ecl-table-header="Dsa-tdb package" class="ecl-table__cell"><svg class="ecl-icon ecl-icon--xl ecl-u-type-color-success" focusable="false" aria-hidden="true">
                                <x-ecl.icon icon="check" />
                            </svg></td>
                    </tr>
                    <tr class="ecl-table__row">
                        <td data-ecl-table-header="Feature" class="ecl-table__cell ">Example Use Cases</td>
                        <td data-ecl-table-header="Public Dashboard" class="ecl-table__cell">
                            <ul class="ecl-unordered-list ecl-unordered-list--no-marker">
                                <li class="ecl-unordered-list__item">
                                    Quick hypothesis testing
                                </li>
                                <li class="ecl-unordered-list__item">Calculation of aggregated metrics</li>
                                <li class="ecl-unordered-list__item">Aggregate visualisations</li>
                            </ul>
                        </td>
                        <td data-ecl-table-header="Download of individual & aggregated SOR files" class="ecl-table__cell">
                            <ul class="ecl-unordered-list ecl-unordered-list--no-marker">
                                <li class="ecl-unordered-list__item">In-depth analysis of variables of focus</li>
                                <li class="ecl-unordered-list__item">Historical analysis</li>
                            </ul>
                        </td>
                        <td data-ecl-table-header="Research API" class="ecl-table__cell">
                            <ul class="ecl-unordered-list ecl-unordered-list--no-marker">
                                <li class="ecl-unordered-list__item">Cross-platform analysis</li>
                                <li class="ecl-unordered-list__item">Temporal analysis</li>
                                <li class="ecl-unordered-list__item">Statistical insights</li>
                                <li class="ecl-unordered-list__item">Platform-specific analysis</li>
                                <li class="ecl-unordered-list__item">Trend identification</li>
                            </ul>
                        </td>
                        <td data-ecl-table-header="Dsa-tdb package" class="ecl-table__cell">
                            <ul class="ecl-unordered-list ecl-unordered-list--no-marker">
                                <li class="ecl-unordered-list__item">
                                    Custom dashboarding
                                </li>
                                <li class="ecl-unordered-list__item">Individual data analysis pipelines</li>
                                <li class="ecl-unordered-list__item">Platform-specific analysis</li>
                            </ul>
                        </td>
                    </tr><tr class="ecl-table__row ">
                        <td data-ecl-table-header="Feature" class="ecl-table__cell">Storage Requirements</td>
                        <td data-ecl-table-header="Public Dashboard" class="ecl-table__cell">None</td>
                        <td data-ecl-table-header="Download of individual & aggregated SOR files" class="ecl-table__cell">Medium (5MB to 5GB per day depending on data files)</td>
                        <td data-ecl-table-header="Research API" class="ecl-table__cell">Low</td>
                        <td data-ecl-table-header="Dsa-tdb package" class="ecl-table__cell">High (few GB per day)</td>
                    </tr>



                    </tbody>
                </table>
            </div>
            <p class="ecl-u-type-paragraph">
                If you use the data from the DSA Transparency Database for your research work, please cite it using the
                following information:
            </p>
            <p class="ecl-u-type-paragraph">
                European Commission-DG CONNECT, Digital Services Act Transparency Database, Directorate-General for
                Communications Networks, Content and Technology, 2023.
            </p>
            <p class="ecl-u-type-paragraph">
                <x-ecl.external-link href="https://doi.org/10.2906/134353607485211"
                                     label="https://doi.org/10.2906/134353607485211"/>
            </p>
        </div>
    </div>

@endsection
