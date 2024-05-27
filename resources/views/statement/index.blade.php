@extends('layouts/ecl')

@section('title', 'Statements of Reasons')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="{{__('statement-search.title')}}"/>
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">{{__('statement-search.title')}}</h1>

    <div class="ecl-row ecl-u-mt-l ecl-u-mb-xl">
        <div class="ecl-col-l-8">
            <div class="ecl-u-type-paragraph">
  {!!  __('statement-search.body') !!}
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
                {{__('statement-search.Advanced Search')}}
            </a>
        </div>
    </div>



    <div class="ecl-u-pt-l ecl-u-d-inline-flex ecl-u-align-items-center ecl-u-f-r">

        <div class="ecl-u-type-paragraph ecl-u-mr-s">
            @if(!$reindexing)
                {{__('statement-search.Statements of Reasons')}}: {{ $total }}
            @else
                {{__('statement-search.Statements of Reasons Found: ')}}: {{ $total }}
            @endif
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

