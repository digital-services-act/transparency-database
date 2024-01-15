@extends('layouts/ecl')


@if($platform)
    @section('title', 'Data Download for ' . $platform->name)
@else
    @section('title', 'Data Download')
@endif

@section('breadcrumbs')
    @if($platform)
        <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
        <x-ecl.breadcrumb label="Data Download" url="{{ route('dayarchive.index') }}"/>
        <x-ecl.breadcrumb :label="$platform->name"/>
    @else
        <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
        <x-ecl.breadcrumb label="Data Download"/>
    @endif
@endsection


@section('content')

    @if($platform)
        <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Daily Archives for {{ $platform->name }}</h1>
    @else
        <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Data Download</h1>
    @endif

    <div class="ecl-row ecl-u-mt-l">
        <div class="ecl-col-l-8">
            <p class="ecl-u-type-paragraph">
                On this page, you can download zipped .csv files containing the daily submissions of statements of
                reasons,
                either for all platforms collectively or for each platform individually. The files are provided in
                full and light versions.<br/>
                <br/>
                Full archive files contain all the public data points of each individual statement of reasons
                submitted on a given day. That is, each file contains the entire attribute schema of the database.<br/>
                <br/>


                <a href="{{ route('page.show', ['faq']) }}">Read more about the Full and light version of the archive in
                    the FAQ</a><br/><br/>


                <a href="{{ route('page.show', ['faq']) }}">Read more about the archive format and the SHA1 in the
                    FAQ</a>

            </p>

        </div>
        <div class="ecl-col-l-4">

            <div class="ecl-media-container">
                <figure class="ecl-media-container__figure">
                    <div class="ecl-media-container__caption">

                        <picture class="ecl-picture ecl-media-container__picture"><img
                                class="ecl-media-container__media"
                                src="https://dsa-images-disk.s3.eu-central-1.amazonaws.com/dsa-image-2.jpeg"
                                alt="Digital Services Act Logo"/></picture>


                    </div>


                </figure>


            </div>
        </div>

    </div>
    <form method="get" id="platform">
        <div class="ecl-row ecl-u-mt-l" style="border-width: 50px">

            <div class="ecl-col-l-2">

                <x-ecl.datepicker label="From" id="created_at_start" justlabel="true"
                                  name="created_at_start" :value="request()->get('created_at_start', '')"/>
            </div>
            <div class="ecl-col-l-2">
                <x-ecl.datepicker label="To" id="created_at_end" justlabel="true"
                                  name="created_at_end" :value="request()->get('created_at_end', '')"/>
            </div>
            <div class="ecl-col-l-4">
                <x-ecl.select label="Select a Platform" name="uuid" id="uuid"
                              justlabel="true"
                              :options="$options['platforms']" :default="request()->route('uuid')"
                />

            </div>
            <div class="ecl-col-l-2 ecl-u-flex-l-row">
                <button class="ecl-button ecl-button--primary" style="margin-top:36px" type="submit"><span
                        class="ecl-button__container"><span
                            class="ecl-button__label" data-ecl-label="true">Search</span><svg
                            class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-button__icon ecl-button__icon--after"
                            focusable="false" aria-hidden="true" data-ecl-icon="">
                            <x-ecl.icon icon="corner-arrow"/>
                            </svg></span>
                </button>

            </div>

        </div>
    </form>
    {{--    <script>--}}
    {{--        var uuid = document.getElementById('uuid')--}}
    {{--        uuid.onchange = (event) => {--}}
    {{--            document.location.href = '{{ route('dayarchive.index') }}/' + event.target.value--}}
    {{--        }--}}
    {{--    </script>--}}
    {{--    </div>--}}

    <x-dayarchive.table :dayarchives="$dayarchives"/>

@endsection
