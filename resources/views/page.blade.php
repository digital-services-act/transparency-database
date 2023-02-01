
@extends('layouts/ecl')

@section('title', $page_title)

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Profile Dashboard" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1">{{ $page_title }}</h1>

    <style>
        pre {
            background-color: black;
            color: white;
            padding: 5px;
            word-wrap: normal;
            overflow-x: auto;
        }

        code {
            background-color: black;
            color: white;
        }
    </style>

    <div class="ecl-row ecl-u-mt-l">
        <div class="ecl-col-l-3">
            <nav data-ecl-auto-init="InpageNavigation" class="ecl-inpage-navigation" data-ecl-inpage-navigation="true">
                <div class="ecl-inpage-navigation__title">Page contents</div>

                <div class="ecl-inpage-navigation__body">
                    <div id="toc-area">
                        <ul id="toc-list">
                            <li><a href="#root">TOP</a></li>
                        </ul>
                    </div>
                </div>

            </nav>
        </div>
        <div class="ecl-col-l-9">
            <div id="content-area" v-html="content">
                {!! $page_content !!}
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>

    <script>
        const slugify = str =>
        str
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');

        jQuery(document).ready(function($){
          $('#content-area > h1').each(function(e){
            var dis = $(this)
            var t = dis.text();
            var id = slugify(t);
            dis.attr('id', id);

            $('#toc-list').append('<li><a href="#'+id+'">' + t + '</a></li>');
          });
        });

        jQuery(document).ready(function($){
          $('#content-area > h2').each(function(e){
            var dis = $(this)
            var t = dis.text();
            var id = slugify(t);
            dis.attr('id', id);

            $('#toc-list').append('<li><a href="#'+id+'">' + t + '</a></li>');
          });
        });
    </script>

@endsection