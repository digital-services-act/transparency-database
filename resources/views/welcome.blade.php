@extends('layouts/ecl')

@section('content')

    <div class="ecl-row">
        <div class="ecl-col-12">
            <h1>Welcome to the Laravel Skeleton with ECL</h1>
            <p>
                In order to use the application you will first need to login with your EU-Login.
            </p>
            <a class="ecl-button ecl-button--primary" href="{{route('dashboard')}}">EU-Login</a>
        </div>
    </div>

@endsection
