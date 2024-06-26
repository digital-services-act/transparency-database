@extends('layouts/ecl')


@if($platform)
    @section('title')
        {{__("dayarchive.Data Download for", ['platform' => $platform->name])}}
    @endsection
@else
    @section('title', 'Data Download')
@endif


@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="{{__('dayarchive.Data Download')}}"/>
@endsection


@section('content')

    @if($platform)
        <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">{{__('dayarchive.Data Download for', ['platform' => $platform->name])}}</h1>
    @else
        <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">{{__('dayarchive.Data Download')}}</h1>
    @endif

    <x-ecl.message type="warning" icon="warning" title="{{__('dayarchive.Work in progress')}}"
                   message="{!! __('dayarchive.The Transparency Database infrastructure is still in development mode. We are constantly adapting the backend and the data processing pipelines to optimize performance and user experience. That is why, during the development phase only, the file format, name pattern and organization of the daily dumps are subject to change without notice.')!!}"
                   :close="true"/>

    <div class="ecl-row ecl-u-mt-l">
        <div class="ecl-col-l-8">
            <p class="ecl-u-type-paragraph">
                {{__('dayarchive.On this page, you can download zipped .csv files containing the daily submissions of statements of reasons, either for all platforms collectively or for each platform individually. The files are provided in full and light versions.')}}<br>
                <br>
                {{__('dayarchive.Full archive files contain all the public data points of each individual statement of reasons submitted on a given day. That is, each file contains the entire attribute schema of the database.')}}<br>
                <br>
                {{__('dayarchive.The daily dumps are currently provided in a nested zip archive containing the csv chunks. Specifically, each .zip file contains several zip files. Each of the latter contains the csv files storing all the statements of reasons received on a given day from the selected platform(s).')}}<br>
                <br>
                {!! __('dayarchive.Please note that a Data Retention Policy applies and the daily dumps will be available during a limited period following their creation date.') !!}<br/><br/>
                <a href="{{ route('page.show', ['faq']) }}">{{__('dayarchive.Read more about the Full and light version of the archive in the FAQ')}}</a><br>
                <br>
                <a href="{{ route('page.show', ['faq']) }}">{{__('dayarchive.Read more about the archive format and the SHA1 in the FAQ')}}</a>
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
    <form method="get" id="platform">
        <div class="ecl-row ecl-u-mt-l" style="border-width: 50px">

            <div class="ecl-col-l-2">
                <x-ecl.datepicker label="{{__('dayarchive.From')}}" id="from_date" justlabel="true"
                                  name="from_date" :value="request()->get('from_date', '')"/>
            </div>
            <div class="ecl-col-l-2">
                <x-ecl.datepicker label="{{__('dayarchive.To')}}" id="to_date" justlabel="true"
                                  name="to_date" :value="request()->get('to_date', '')"/>
            </div>
            <div class="ecl-col-l-4">
                <x-ecl.select label="{{__('dayarchive.Select a Platform')}}" name="uuid" id="uuid"
                              justlabel="true"
                              :options="$options['platforms']" :default="request()->get('uuid', '')"
                />

            </div>
            <div class="ecl-col-l-2">
                <div class="ecl-form-group ecl-u-mb-l" style="margin-top: 36px;">
                    <button class="ecl-button ecl-button--primary" type="submit">
                        <span class="ecl-button__container">
                            <span class="ecl-button__label"
                                  data-ecl-label="true">
                                {{__('dayarchive.Search')}}
                            </span>
                            <svg
                                class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-button__icon ecl-button__icon--after"
                                focusable="false"
                                aria-hidden="true"
                                data-ecl-icon="">
                                <x-ecl.icon icon="corner-arrow"/>
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

    <x-dayarchive.table :dayarchives="$dayarchives" :reindexing="$reindexing"/>

@endsection

