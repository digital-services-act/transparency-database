@extends('layouts/ecl')

@section('title', 'Onboarding')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Onboarding"/>
@endsection


@section('content')
    <div style="padding:2rem 0;position:relative;background-color:#f4f7fc">

        <div class="ecl-container">
            <form action="{{ route('onboarding.join') }}" id="join-platform" method="POST">

                @csrf
                <div class="ecl-row">

                    <div class="ecl-col-10 ecl-offset-1">
                        <div
                            class="ecl-u-pa-xs ecl-u-bg-blue-25 ecl-u-border-all ecl-u-border-color-blue ecl-u-type-color-blue ecl-u-type-l ecl-u-type-bold" style="border-radius: 6px; padding: 4px">

                            <div class="ecl-row">
                                <div class="ecl-col-10">
                                    <x-ecl.label label="Choose a platform" justlabel="true"/>

                                    <div class="ecl-select__container ecl-select__container--xl">
                                        <select name="platform" id="platform_select" class="ecl-select">

                                            <option enable value="">-- Choose an existing platform --</option>

                                            @foreach($platforms as $platform)
                                                <option
                                                    value="{{$platform->id}}">{{ ucfirst($platform->name) }}</option>
                                            @endforeach
                                        </select>
                                        <div class="ecl-select__icon">
                                            <svg
                                                class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape"
                                                focusable="false" aria-hidden="true">
                                                <x-ecl.icon icon="corner-arrow"/>
                                            </svg>
                                        </div>

                                    </div>
                                </div>
                                <div class="ecl-col-2  ecl-u-mv-auto ">


                                    <input type="submit"
                                           class="ecl-u-d-inline ecl-u-f-l ecl-button ecl-button--secondary"
                                           id="platform_join_btn"
                                           value="Apply" style="margin-top:24px"/>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


            </form>


            <div class="ecl-u-d-flex ecl-u-justify-content-center">
                <h1>OR</h1>
            </div>

            <form action="{{ route('onboarding.create') }}" method="GET">

                @csrf
                <div class="ecl-row">

                    <div class="ecl-col-10 ecl-offset-1">
                        <div
                            class="ecl-u-pa-xs ecl-u-bg-blue-25 ecl-u-border-all ecl-u-border-color-blue ecl-u-type-color-blue ecl-u-type-l ecl-u-type-bold" style="border-radius: 6px; padding: 4px">



                                <div class="ecl-u-d-flex ecl-u-justify-content-center">
                                    <input type="submit"
                                           class="ecl-button ecl-button--secondary"
                                           value="Create a new Platform" style="margin:12px"/>
                                </div>



                        </div>
                    </div>

                </div>


            </form>
        </div>
    </div>

    <script type="text/javascript">

        let form = ge("join-platform");



        function initFields() {

            console.log(ge('platform_select').value);
            ge('platform_join_btn').disabled = false;
            if (ge('platform_select').value !== "") {
                console.log('enable button');
                ge('platform_join_btn').disabled = false;
            } else {
                console.log('disable button');
                ge('platform_join_btn').disabled = true;
            }

        }

        function ge(id) {
            return document.getElementById(id);
        }


        initFields();

        ge('platform_select').addEventListener('change', initFields);







    </script>

@endsection
