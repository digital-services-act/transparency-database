@auth
    {{--                        @can('impersonate')--}}

    <form class="" action="{{route('impersonate')}}" method="POST">
        @csrf
        <div class="ecl-form-group">
            <div class="ecl-select__container ecl-select__container--m">
                <select class="ecl-select" id="select-default" name="username" onchange="this.form.submit()">

                    @foreach($profiles as $profile)
                        <option value="{{$profile->eu_login_username}}"
                                @if(auth()->user()->eu_login_username == $profile->eu_login_username) selected @endif>
                            {{$profile->name}}
                        </option>
                    @endforeach

                </select>
                <div class="ecl-select__icon">
                    <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-180 ecl-select__icon-shape" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="corner-arrow" />
                    </svg>
                </div>
            </div>
        </div>
    </form>


    {{--                        @endcan--}}
@endauth