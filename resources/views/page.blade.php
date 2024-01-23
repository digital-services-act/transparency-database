@extends('layouts/ecl')

@section('title', $page_title)

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    @if ($profile)
        <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}"/>
    @endif
    <x-ecl.breadcrumb label="{{ $breadcrumb }}"/>
@endsection


@section('content')

    {{--    <h1 class="ecl-page-header__title ecl-u-type-heading-1">{{ $page_title }}</h1>--}}

    <style>

        code {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: .1em;
        }

    </style>




    <h1 class="ecl-u-type-heading-1">{{$page_title}}</h1>

    @if($show_feedback_link)
        <p class="ecl-u-type-paragraph">
            If you can't find an answer to your question, feel free to use our <a href="{{route('feedback.index')}}">contact
                form</a>
        </p>
    @endif


    <div class="ecl-row">
        <div class="ecl-col-l-3 ecl-u-d-none" id="toc-wrapper">
            <nav class="ecl-inpage-navigation"
                 data-ecl-auto-init="InpageNavigation"
                 data-ecl-inpage-navigation="true"
                 aria-labelledby="ecl-inpage-navigation-default">
                <div class="ecl-inpage-navigation__title">Page contents</div>

                <div class="ecl-inpage-navigation__body">
                    <div id="toc-area">
                        <ul id="toc-list"
                            class="ecl-inpage-navigation__list"
                            data-ecl-inpage-navigation-list="true"
                        >

                        </ul>
                    </div>
                </div>

            </nav>
        </div>
        <div class="ecl-col-l-12" id="content-wrapper">
            <div id="content-area" v-html="content">
                {!! $page_content !!}
            </div>
        </div>
    </div>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/monokai-sublime.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.3.min.js"
            integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>

    <script>
        const slugify = str => str.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');

        jQuery(document).ready(function ($) {
            $('#content-area > h1').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                dis.addClass('ecl-u-type-heading-1')

                $('#toc-list').append('<li class="ecl-inpage-navigation__item"><a href="#' + id +
                    '" class="ecl-link ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + t + '</a></li>');
            })
        })

        jQuery(document).ready(function ($) {
            $('#content-area > h2').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                dis.addClass('ecl-u-type-heading-2')

                $('#toc-list').append('<li class="ecl-inpage-navigation__item"><a href="#' + id +
                    '" class="ecl-link ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + t + '</a></li>');
                $('#toc-wrapper').removeClass('ecl-u-d-none');
                $('#content-wrapper').removeClass('ecl-col-l-12');
                $('#content-wrapper').addClass('ecl-col-l-9');
            })
        })


        // Slugify the id so it can be linked but not necessarily in the TOC
        jQuery(document).ready(function ($) {
            $('#content-area > h3').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                dis.addClass('ecl-u-type-heading-3')
            })
        })

        // Slugify the id so it can be linked but not necessarily in the TOC
        jQuery(document).ready(function ($) {
            $('#content-area > h4').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                dis.addClass('ecl-u-type-heading-4')
            })
        })

        // Slugify the id so it can be linked but not necessarily in the TOC
        jQuery(document).ready(function ($) {
            $('#content-area > h5').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                dis.addClass('ecl-u-type-heading-5')
            })
        })

        jQuery(document).ready(function ($) {
            $('#content-area > p').each(function (e) {
                var dis = $(this)
                dis.addClass('ecl-u-type-paragraph')
            })
        })

        jQuery(document).ready(function ($) {
            $('#content-area > ul').each(function (e) {
                var dis = $(this)
                dis.addClass('ecl-unordered-list')
            })
        })

        jQuery(document).ready(function ($) {
            $('#content-area > ul > li').each(function (e) {
                var dis = $(this)
                dis.addClass('ecl-unordered-list__item')
            })
        })

        jQuery(document).ready(function ($) {
            $('#content-area > p > a').each(function (e) {
                var dis = $(this)
                dis.addClass('ecl-link')
            })
        })

        hljs.highlightAll()
    </script>

@endsection
