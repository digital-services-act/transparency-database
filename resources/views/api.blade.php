@extends('layouts/ecl')

@section('title', 'User Profile')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home
                " url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="User Profile
                " url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="API Token Management
                " />
@endsection


@section('content')

    <div class="ecl-row">
        <div class="ecl-col-12">

            <h2 class="ecl-u-type-heading-2">Your API Token
            </h2>
            @if($token_plain_text)
                <p class="ecl-u-type-paragraph">
                    Your token for accessing the API is:
                <pre id="plaintoken">{{ $token_plain_text }}</pre>
                <button class="ecl-button ecl-button--primary" onclick="copyContent()">Copy To Clipboard
                </button>
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
                    Copy this and use it in your api calls, do not leave or refresh the page until you have copied it down. It
                    is only going to be shown once.
                </p>
            @else
                <p class="ecl-u-type-paragraph">
                    You currently have a token for the API. However if you have lost the key or would like to generate a new
                    one, click the button below. This will invalidate any old tokens.
                </p>
                <p class="ecl-u-type-paragraph">
                <form method="POST" action="{{ route('profile.api.new-token') }}">
                    @csrf
                    <input type="submit" class="ecl-button ecl-button--primary" value="Generate New Token" />
                </form>
                </p>
            @endif


            @can('create statements')
                <h2 class="ecl-u-type-heading-2">How to use the statement API</h2>
                <p class="ecl-u-type-paragraph">
                    Would you like to create statements of reasons using the API?
                </p>
                <p class="ecl-u-type-paragraph">
                    <a href="{{ route('page.show', ['api-documentation']) }}" class="ecl-button ecl-button--primary">
                        Statement API Documentation
                    </a>
                </p>
            @endcan

            @can('research API')
                <h2 class="ecl-u-type-heading-2">How to use the research API</h2>
{{--                <p class="ecl-u-type-paragraph">--}}
{{--                    <a href="{{ route('page.show', ['research-api']) }}" class="ecl-button ecl-button--primary">--}}
{{--                        Research API Documentation--}}
{{--                    </a>--}}
{{--                </p>--}}

                <section class="ecl-u-mb-l">


                    <p class="ecl-u-type-paragraph">
                        To help you get started quickly, we've prepared a few resources:
                    </p>

                    <ul class="ecl-list ecl-list--unordered ecl-u-mb-l">
                        <li class="ecl-u-mb-s">
                            📄
                            <a href="https://dsa-files.s3.eu-central-1.amazonaws.com/Transparency+Database+Reasearch+API+-+HOWTO.pdf" class="ecl-link" target="_blank">
                                Step-by-step guide for using the Research API
                            </a>
                        </li>
                        <li class="ecl-u-mb-s">
                            📦
                            <a href="https://dsa-files.s3.eu-central-1.amazonaws.com/DSA+Transparency+Database+starter+pack.zip" class="ecl-link" target="_blank">
                                Starter pack with example files for popular API development tools
                            </a>
                        </li>
                        <li class="ecl-u-mb-s">
                            📚
                            <a href="#" class="ecl-link" target="_blank">
                                Full API documentation for in-depth reference
                            </a>
                        </li>
                    </ul>

                </section>

            @endcan

        </div>
    </div>

@endsection
