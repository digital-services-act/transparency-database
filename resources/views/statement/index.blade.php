@extends('layouts/ecl')

@section('title', 'Statements of Reasons')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Statements of Reasons"/>
@endsection

@section('content')

    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
        <x-statement.search-form-simple :similarity_results="$similarity_results"/>
    </div>

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statements of Reasons</h1>

    @can('create statements')
        <x-ecl.cta-button label="Create a Statement of Reason" url="{{ route('statement.create') }}" />
        <br />
    @endcan




    <div class="ecl-u-pt-l ecl-u-d-inline-flex ecl-u-align-items-center">

        <div class="ecl-u-type-paragraph ecl-u-mr-s">
            Statements of Reasons Found: {{ $total }} out of {{ $global_total }}
        </div>

        <div class="ecl-u-type-paragraph ecl-u-mr-l">

            <a href="{{ route('statement.export', request()->query()) }}" class="ecl-link ecl-link--default ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">.csv</span>
                    <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="download" />
                    </svg></a>
        </div>
    </div>


    <x-statement.table :statements="$statements" />

@endsection

