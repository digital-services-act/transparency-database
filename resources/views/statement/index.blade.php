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
            <div class="ecl-expandable" data-ecl-expandable="true" data-ecl-auto-init="Expandable"><button
                        class="ecl-button ecl-button--secondary ecl-expandable__toggle" type="button"
                        aria-controls="expandable-example-content" data-ecl-expandable-toggle="" data-ecl-label-expanded="Expanded"
                        data-ecl-label-collapsed="Collapsed" aria-expanded="false"><span class="ecl-button__container"><span
                                class="ecl-button__label" data-ecl-label="true">Collapsed</span><svg
                                class="ecl-icon ecl-icon--fluid ecl-icon--rotate-180 ecl-button__icon ecl-button__icon--after" focusable="false"
                                aria-hidden="true" data-ecl-icon="">
        <use xlink:href="/component-library/dist/media/icons.eae40d34.svg#corner-arrow"></use>
      </svg></span></button>
                <div id="expandable-example-content" class="ecl-expandable__content" hidden="">
                    <p class="ecl-u-type-paragraph-m">The EU is building an energy union that make sures Europe’s energy supply is safe,
                        viable and accessible to all. In doing so, it can boost the economy and attract investments that can create new
                        jobs opportunities.</p>
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

    <x-statements-table :statements=$statements />

@endsection

