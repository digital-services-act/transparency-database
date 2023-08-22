@extends('layouts/ecl')

@section('title', 'Onboarding')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Onboarding" url="{{ route('onboarding.index') }}"/>
    <x-ecl.breadcrumb label="Create Platform"/>
@endsection


@section('content')
    <div style="padding:2rem 0;position:relative;background-color:#f4f7fc">

        <div class="ecl-container">

            <form action="{{ route('onboarding.create') }}" method="GET">

                @csrf
                <div class="ecl-row">

                    <div class="ecl-col-10 ecl-offset-1">
                        <div
                            class="ecl-u-pa-xs ecl-u-bg-blue-25 ecl-u-border-all ecl-u-border-color-blue ecl-u-type-color-blue ecl-u-type-l ecl-u-type-bold">


                            <div>Platform Name:</div>
                            <div>Platform URL:</div>
                            <div>Some Documents:</div>

                            <div class="ecl-u-d-flex ecl-u-justify-content-center">
                                <input type="submit"
                                       class="ecl-button ecl-button--secondary"
                                       value="Create the Platform" style="margin:12px"/>
                            </div>


                        </div>
                    </div>

                </div>


            </form>
        </div>
    </div>


    {{--    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">--}}
    {{--        <form method="get">--}}
    {{--            <x-ecl.textfield name="s" label="Search <a class='ecl-link' href='{{ route('platform.index') }}'>reset</a>" placeholder="search by name" justlabel="true" value="{{ request()->get('s', '') }}" />--}}
    {{--        </form>--}}
    {{--    </div>--}}

    {{--    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Platforms</h1>--}}

    {{--    <p class="ecl-u-type-paragraph">--}}
    {{--        Manage the platforms of the application below.--}}
    {{--    </p>--}}

    {{--    <p class="ecl-u-type-paragraph">--}}
    {{--        <x-ecl.cta-button label="Create a Platform" url="{{ route('platform.create') }}"/>--}}
    {{--    </p>--}}

@endsection
