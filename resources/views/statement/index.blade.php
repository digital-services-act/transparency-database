@extends('layouts/ecl')

@section('title', 'Statements')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Statements"/>
@endsection

@section('content')

    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
        <form method="get">
            <x-ecl.textfield name="s" label="Search <a class='ecl-link' href='{{ route('statement.index') }}'>clear</a>" placeholder="search by name and uuid" justlabel="true" value="{{ request()->get('s', '') }}" />
            <div class="ecl-expandable" data-ecl-expandable="true" data-ecl-auto-init="Expandable">
                <button class="ecl-button ecl-button--secondary ecl-expandable__toggle"
                        type="button"
                        aria-controls="expandable-search-content"
                        data-ecl-expandable-toggle=""
                        data-ecl-label-expanded="Simple"
                        data-ecl-label-collapsed="Advanced"
                        aria-expanded="false">
                    <span class="ecl-button__container">
                        <span class="ecl-button__label"
                              data-ecl-label="true">Advanced</span>
                        <svg class="ecl-icon ecl-icon--fluid ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after"
                             focusable="false"
                             aria-hidden="true"
                             data-ecl-icon="">
                            <x-ecl.icon icon="corner-arrow" />
                        </svg>
                    </span>
                </button>
                <div id="expandable-search-content" class="ecl-expandable__content" hidden="">

                    <p class="ecl-u-type-paragraph">
                        <x-ecl.checkboxes
                                label="Ground for Decision"
                                justlabel="true"
                                name="decision_ground"
                                id="decision_ground"
                                :default="request()->get('decision_ground', [])"
                                :options="$options['decision_grounds']"
                        />

                        <x-ecl.select-multiple label="Territorial scope of the decision " name="countries_list" id="countries_list"
                                               justlabel="true"
                                               :options="$options['countries']" :default="request()->get('countries_list', [])"
                                               select_all="European Union" select_item="Select a member state"
                                               enter_keyword="Enter a country name" />

                        <x-ecl.checkboxes
                            label="Automated Detection"
                            justlabel="true"
                            name="automated_detection"
                            id="automated_detection"
                            :options="$options['automated_detections']"
                            :default="request()->get('automated_detection', [])"
                        />

                        <x-ecl.datepicker label="Created Start" id="created_at_start" justlabel="true"
                                          name="created_at_start" :value="request()->get('created_at_start', '')" />

                        <x-ecl.datepicker label="Created End" id="created_at_end" justlabel="true"
                                          name="created_at_end" :value="request()->get('created_at_end', '')" />

                        <x-ecl.button label="search" />
                    </p>

                </div>
            </div>
        </form>
    </div>


    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statements</h1>

    @can('create statements')
        <p class="ecl-u-type-paragraph"></p>
        <x-ecl.cta-button label="Create a Statement" url="{{ route('statement.create') }}"/>
        </p>
    @endcan

    <p class="ecl-u-type-paragraph">Statements Found: {{ $total }}</p>
    <x-statements-table :statements=$statements />

@endsection

