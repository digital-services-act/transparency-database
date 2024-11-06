@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard"/>
@endsection


@section('content')

    <style>

        .responsive-iframe-container {
            position: relative;
            width: 110%;
            padding-bottom: 70.5%;
            height: 0;
            margin-left: -40px;

        }

        .responsive-iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

    </style>

    <h1 class="ecl-u-type-heading-1">Dashboard</h1 class="ecl-u-type-heading-1">


    <p class="ecl-u-type-paragraph" style="max-width:none !important">
        The dashboard below provides a user-friendly and interactive interface to explore summarized data, offering a comprehensive overview. Start exploring the data by clicking on different elements. You can navigate across the following pages, from left to right at the bottom of the dashboard:<br/><br/>
        1. Overview<br/>
        2. Timelines<br/>
        3. Violations<br/>
        4. Restrictions<br/>
        5. Platforms<br/>
        6. Other analysis<br/><br/>
        For additional guidance on making the best use of the tool, please refer to instructions below the dashboard.<br/>
        <strong>By default</strong>, the dashboard displays data from the <strong>last 30 days</strong>; you can apply filters to view data for other time periods.<br/><br/>
        Please note that a <a href='/page/data-retention-policy'>Data Retention Policy</a> applies and that the Dashboard is showing data aggregated from a longer period of time in the past with respect to the data available on the search page. This might result in different numbers of Statements of Reasons being reported by the Dashboard and the search pages even when the same filtering settings are applied.

    </p>

    <div class="responsive-iframe-container">
        <iframe title="Transparency Database Dashboard - {{config('app.env_real')}}"
                class="responsive-iframe"
                src="{{config("dsa.POWERBI")}}"
                frameborder="0"
                allowfullscreen="true">
        </iframe>
    </div>



    <h2 class="ecl-u-type-heading-2">Instructions</h2>
    <div style="width:110%; !important;
            margin-left: -20px;
            max-height:100%">

        <div class="ecl-u-d-flex">
            <div
                class="ecl-u-border-all ecl-u-border-color-white ecl-u-type-color-black ecl-u-type-s ecl-u-pl-l ecl-u-pr-l">
                <div class="ecl-u-type-bold ecl-u-mb-s">Introduction</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">The purpose of this dashboard is to help users without programming skills explore aggregated data in the DSA Transparency Database. These instructions are to assist you in making the best use of all the available features.
                </div>


                <div class="ecl-u-type-bold ecl-u-mb-s">Overview</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">This dashboard comprises 5 pages, in addition to these instructions. Each page contains 2-3 visualizations, typically grouped by theme, such as timelines, violations, or restrictions. In addition, the “5-Other analyses” page shows a breakdown of statements of reasons by content type. It also shows how the grounds for the restrictions imposed and the information source used relate to one another as well as the relationship between automatic content moderation decisions and automatic detection of content for moderation.
                </div>

                <div class="ecl-u-type-bold ecl-u-mb-s">Data update</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">The data is updated every day at 06:00 (CET).
                </div>

                <div class="ecl-u-type-bold ecl-u-mb-s">Definitions</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">For definitions of terms used, please refer to the
                    <a href="https://digital-strategy.ec.europa.eu/en/faqs/dsa-transparency-database-questions-and-answers">FAQ</a>
                    section and the
                    <a href="{{ route('page.show', ['api-documentation']) }}">API documentation</a> of the DSA Transparency Database.
                     Kindly note that “violations” refer to the variable “category” of the API documentation.</div>

            </div>
            <div
                class="ecl-u-border-all ecl-u-border-color-white ecl-u-type-color-black ecl-u-type-s ecl-u-pl-l ecl-u-pr-l">
                <div class="ecl-u-type-bold ecl-u-mb-s">Interactivity</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">Each visualization is interactive: 1) additional information is provided when the mouse hovers over a visualization, and 2) you can click on any of the elements (e.g. bars, charts, labels) to highlight specific data. The rest of the visualizations in the page will accordingly highlight the relevant data.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">Note that highlights apply only to the page in which they are triggered. For filters that apply to all the pages, use the ‘filters’ menu on the top left – see below.
                </div>

                <div class="ecl-u-type-bold ecl-u-mb-s">Filters</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">The three lines on the top left of the dashboard open the filters menu.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">Select the element you want to focus on (e.g., specific platform(s), types of violations or restrictions), and the visualizations on all the pages will update accordingly.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">To close the filters menu, click on the ‘back’ button on the right of the menu.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">To remove all applied filters, press the ‘Remove all filters’ button at the bottom of the menu.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">Please refer to the <a href="https://digital-strategy.ec.europa.eu/en/faqs/dsa-transparency-database-questions-and-answers">FAQ section</a> about the filters that are available.
                </div>

                <div class="ecl-u-type-bold ecl-u-mb-s">Selecting multiple elements</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">Keep the ‘Ctrl’ button pressed to select multiple items on any interactive element of the dashboard or filters.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">This way, you can, for example, select all the social media platforms or all online marketplaces, or different combinations of violations or restrictions.
                </div>


            </div>
            <div
                class="ecl-u-border-all ecl-u-border-color-white ecl-u-type-color-black ecl-u-type-s ecl-u-pl-l ecl-u-pr-l">
                <div class="ecl-u-type-bold ecl-u-mb-s">Zooming on the Y-axis</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">In some visualizations, the orders of magnitude of data vary significantly, from thousands to billions. In these cases, ‘zoom in’ on the y-axis by scrolling the bar on the left of the graph, where it is available.
                </div>

                <div class="ecl-u-type-bold ecl-u-mb-s">Focus mode</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">You can maximize any visualization by pressing the 'focus mode' button, on the top right of the visualization.
                </div>

                <div class="ecl-u-type-bold ecl-u-mb-s">Viewing detailed figures</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">You can view the data as a table, by right-clicking on any visualization and selecting 'Show as a table'.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">It is not possible to download individual statements of reason from the dashboard. For this, kindly use the <a href="{{route('dayarchive.index')}}">Data Download</a> functionality.
                </div>


                <div class="ecl-u-type-bold ecl-u-mb-s">Adjusting the date format</div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s"> By default, the dashboard considers your browser’s settings for the format of displaying dates. Therefore, you may see dates as MM/DD/YYYY, instead of DD/MM/YYYY. To see dates in the latter format, change your browser’s language preference.
                </div>

                <div class="ecl-u-type-paragraph ecl-u-type-s ecl-u-mb-s">For example, for Google Chrome, type on the URL box: chrome://settings/languages and choose “English (United Kingdom, Oxford English Dictionary spelling)” as the preferred language. For Firefox, go to 'Settings', select 'Languages' and choose 'English (GB)'.
                </div>
            </div>
        </div>

    </div>



@endsection

