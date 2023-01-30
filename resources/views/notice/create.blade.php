@extends('layouts/ecl')

@section('content')
    <div class="ecl-row">
        <div class="ecl-col-12">
            <h1>Create a Notice</h1>
            <form method="post" action="{{route('notice.store')}}">
                @csrf
                <x-notice-form :notice=$notice :options=$options />
                <x-ecl.button label="Create Notice" />
            </form>
        </div>
    </div>
@endsection

