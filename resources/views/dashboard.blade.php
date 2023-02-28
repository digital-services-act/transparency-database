@extends('layouts/ecl')

@section('title', 'Profile Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <h1>Dashboard</h1>

    @can('administrate')
    <h2 class="ecl-u-type-heading-2">Administration</h2>

    <div class="ecl-row ecl-u-mb-l">
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('user.index') }}">Users</a>
        </div>
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('role.index') }}">Roles</a>
        </div>
        <div class="ecl-col-4">
            <a class="ecl-button ecl-button--primary" href="{{ route('permission.index') }}">Permissions</a>
        </div>
    </div>

    <div class="ecl-row">
        @can('view logs')
            <div class="ecl-col-4">
                <a class="ecl-button ecl-button--primary" href="{{ route('logs') }}">Logs</a>
            </div>
        @endcan
        @can('view reports')
            <div class="ecl-col-4">
                <a class="ecl-button ecl-button--primary" href="{{ route('reports') }}">Reports</a>
            </div>
        @endcan
    </div>
    @endcan




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
                    generate a new one, click the button below and new one will be shown here.
                </p>
                <p class="ecl-u-type-paragraph">
                    <form method="POST" action="{{ route('new-token') }}">
                        @csrf
                        <input type="submit" class="ecl-button ecl-button--primary" value="Generate New Token" />
                    </form>
                </p>
            @endif

            <h3>How to use the API</h3>
            <p class="ecl-u-type-paragraph">
                <a href="{{ route('dashboard.page.show', ['api-documentation']) }}" class="ecl-button ecl-button--primary">API Documentation</a>
            </p>
        </div>
    </div>

@endsection
