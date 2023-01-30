@extends('layouts/ecl')

@section('content')

    <div class="ecl-row">
        <div class="ecl-col-12">
            <h1>Create a Notice</h1>

            @if ($errors->any())

            @endif

            <form method="post" action="{{route('notice.store')}}">
                @csrf

                <x-ecl.textfield label="Title" name="title" id="title" required=true help="The title of the notice"/>
                <x-ecl.textarea label="Body" name="body" id="body" required=true help="The body of the notice"/>
                <x-ecl.select label="Language" name="language" id="language" :options=$languages required=true help="The language of the notice"/>

                <x-ecl.button label="Create Notice" />
            </form>


        </div>
    </div>

@endsection

