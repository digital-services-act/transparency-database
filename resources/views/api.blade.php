@extends('layouts/ecl')

@section('title', 'Profile Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}"/>
    <x-ecl.breadcrumb label="API" />
@endsection


@section('content')

<div class="ecl-row">
    <div class="ecl-col-12">

        <h2 class="ecl-u-type-heading-2">Your API Token</h2>
        @if($token_plain_text)
            <p class="ecl-u-type-paragraph">
                Your token for accessing the API is: <pre id="plaintoken">{{ $token_plain_text }}</pre>
            <button class="btn" onclick="copyContent()">Copy To Clipboard</button>
            <script>
              let text = document.getElementById('plaintoken').innerHTML;
              const copyContent = async () => {
                try {
                  await navigator.clipboard.writeText(text);
                } catch (err) {
                }
              }
            </script>
            </p>
            <p class="ecl-u-type-paragraph">
                Copy this and use it in your api calls, do not leave or refresh the page until you have copied it
                down. It is only going to be shown once.
            </p>
        @else
            <p class="ecl-u-type-paragraph">
                You currently have a token for the API. However if you have lost the key or would like to
                generate a new one, click the button below. This will invalidate any old tokens.
            </p>
            <p class="ecl-u-type-paragraph">
            <form method="POST" action="{{ route('new-token') }}">
                @csrf
                <input type="submit" class="ecl-button ecl-button--primary" value="Generate New Token" />
            </form>
            </p>
        @endif


        <h2 class="ecl-u-type-heading-2">How to use the API</h2>
        <p class="ecl-u-type-paragraph">
            Would you like to create statements of reasons using the API?
        </p>
        <p class="ecl-u-type-paragraph">
            <a href="{{ route('dashboard.page.show', ['api-documentation']) }}" class="ecl-button ecl-button--primary">API Documentation</a>
        </p>

    </div>
</div>

@endsection
