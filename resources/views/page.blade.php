@extends('layouts/ecl')

@section('title', $page_title)

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    @if ($profile)
        <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}"/>
    @endif
    <x-ecl.breadcrumb label="{{ $breadcrumb }}"/>
@endsection

@section('extra-head')
    <style>

        code {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: .1em;
        }

        hr {
            border: none;
            border-top: 2px solid;
            color: rgb(205, 213, 239);
        }

    </style>
@endsection

@section('content')

    <h1 class="ecl-u-type-heading-1">{{$page_title}}</h1>

    <div class="ecl-row data-ecl-inpage-navigation-container">
        <div class="ecl-col-l-3 ecl-u-d-none" id="toc-wrapper">
            @if($table_of_contents)
                <nav class="ecl-inpage-navigation"
                    data-ecl-auto-init="InpageNavigation"
                    data-ecl-inpage-navigation="true"
                    aria-labelledby="ecl-inpage-navigation-default">
                    <div class="ecl-inpage-navigation__title" id="ecl-inpage-navigation-default">Page contents</div>
                    <div class="ecl-inpage-navigation__body">
                        <div id="toc-area">
                            <div class="ecl-inpage-navigation__trigger-wrapper">
                                <button type="button" class="ecl-inpage-navigation__trigger"
                                        id="ecl-inpage-navigation-default-trigger"
                                        data-ecl-inpage-navigation-trigger="true"
                                        aria-controls="ecl-inpage-navigation-list" aria-expanded="false"
                                        aria-label="inpage-navigation trigger">
                                    <span class="ecl-inpage-navigation__trigger-current"
                                        data-ecl-inpage-navigation-trigger-current="true">
                                    </span>
                                    <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-180 ecl-inpage-navigation__trigger-icon"
                                        focusable="false" aria-hidden="true">
                                        <x-ecl.icon icon="corner-arrow"/>
                                    </svg>
                                </button>
                            </div>
                            <ul class="ecl-inpage-navigation__list"
                                data-ecl-inpage-navigation-list="true"
                                id="ecl-inpage-navigation-default-list"
                            >
                            </ul>
                        </div>
                    </div>
                </nav>
            @endif
        </div>
        @if($table_of_contents || !$right_side_image)
            <div class="ecl-col-l-12" id="content-wrapper">
                <div id="content-area">
                    {!! $page_content !!}
                </div>
            </div>
        @else
            <div class="ecl-col-l-8" id="content-wrapper">
                <div id="content-area">
                    {!! $page_content !!}
                </div>
            </div>
            <div class="ecl-col-l-4">
                <div class="ecl-media-container">
                    <figure class="ecl-media-container__figure">
                        <div class="ecl-media-container__caption">
                            <picture class="ecl-picture ecl-media-container__picture"><img
                                    class="ecl-media-container__media"
                                    src="{{ $right_side_image }}"
                                    alt="{{ $page_title }}"></picture>
                        </div>
                    </figure>
                </div>
            </div>
        @endif
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
                if (!dis.hasClass('ecl-u-type-heading-1')) {
                  dis.addClass('ecl-u-type-heading-1')
                }

                @if($table_of_contents)
                    // Only do these things if we have a table of contents
                    $('#ecl-inpage-navigation-default-list').append('<li class="ecl-inpage-navigation__item"><a href="#' + id +
                        '" class="ecl-link ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + t + '</a></li>');
                @endif
            })
        })

        jQuery(document).ready(function ($) {
            $('#content-area > h2').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                if (!dis.hasClass('ecl-u-type-heading-2')) {
                  dis.addClass('ecl-u-type-heading-2');
                }

                @if($table_of_contents)
                    // Only do these things if we have a table of contents
                    $('#ecl-inpage-navigation-default-list').append('<li class="ecl-inpage-navigation__item"><a href="#' + id +
                    '" class="ecl-link ecl-inpage-navigation__link" data-ecl-inpage-navigation-link="">' + t + '</a></li>');
                    $('#toc-wrapper').removeClass('ecl-u-d-none');
                    $('#content-wrapper').removeClass('ecl-col-l-12');
                    $('#content-wrapper').addClass('ecl-col-l-9');
                @endif
            })
        })


        // Slugify the id so it can be linked but not necessarily in the TOC
        jQuery(document).ready(function ($) {
            $('#content-area > h3').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                if (!dis.hasClass('ecl-u-type-heading-3')) {
                  dis.addClass('ecl-u-type-heading-3')
                }
            })
        })

        // Slugify the id so it can be linked but not necessarily in the TOC
        jQuery(document).ready(function ($) {
            $('#content-area > h4').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                if (!dis.hasClass('ecl-u-type-heading-4')) {
                    dis.addClass('ecl-u-type-heading-4')
                }
            })
        })

        // Slugify the id so it can be linked but not necessarily in the TOC
        jQuery(document).ready(function ($) {
            $('#content-area > h5').each(function (e) {
                var dis = $(this)
                var t = dis.text()
                var id = slugify(t)
                dis.attr('id', id)
                if (!dis.hasClass('ecl-u-type-heading-5')) {
                    dis.addClass('ecl-u-type-heading-5')
                }
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

        hljs.highlightAll();
    </script>

@endsection
