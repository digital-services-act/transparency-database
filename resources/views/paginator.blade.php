@if ($paginator->hasPages())

    <nav class="ecl-pagination" aria-label="Pagination">

        <ul class="ecl-pagination__list">
            @if ($paginator->onFirstPage())

                <li class="ecl-pagination__item ecl-pagination__item--previous"><span
                        class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-before ecl-pagination__link"
                        aria-label="Go to previous page"><svg
                            class="ecl-icon ecl-icon--xs ecl-icon--rotate-270 ecl-link__icon"
                            focusable="false" aria-hidden="true">
                        <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>
                    </svg><span class="ecl-link__label">Previous</span></span></li>
            @else
                <li class="ecl-pagination__item ecl-pagination__item--previous"><a
                        href="{{ $paginator->previousPageUrl() }}"
                        class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-before ecl-pagination__link"
                        aria-label="Go to previous page">
                        <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-270 ecl-link__icon"
                             focusable="false" aria-hidden="true">
                            <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>
                        </svg>
                        <span class="ecl-link__label">Previous</span></a></li>
            @endif

            @foreach ($elements as $element)

                @if (is_string($element))
                    <li class="ecl-pagination__item" aria-disabled="true">{{ $element }}</li>

                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            {{--                        <span aria-current="page">--}}
                            {{--                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5">{{ $page }}</span>--}}
                            {{--                                    </span>--}}
                            <li class="ecl-pagination__item ecl-pagination__item--current"><span
                                    class="ecl-pagination__text ecl-pagination__text--summary"
                                    aria-current="true">{{ $page }}</span><span
                                    class="ecl-pagination__text ecl-pagination__text--full"
                                    aria-current="true">Page {{ $page }}</span></li>
                        @else
                            {{--                        <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">--}}
                            {{--                            {{ $page }}--}}
                            {{--                        </a>--}}
                            <li class="ecl-pagination__item"><a href="{{ $url }}"
                                                                class="ecl-link ecl-link--standalone ecl-pagination__link"
                                                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="ecl-pagination__item ecl-pagination__item--next"><a href="{{ $paginator->nextPageUrl() }}"
                                                                               class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after ecl-pagination__link"
                                                                               aria-label="Go to next page"><span
                            class="ecl-link__label">Next</span>
                        <svg
                            class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-link__icon" focusable="false"
                            aria-hidden="true">
                            <use xlink:href="{{asset('static/media/icons.1fa1778b.svg#corner-arrow')}}"></use>
                        </svg>
                    </a></li>
            @else
                <span
                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                    {!! __('pagination.next') !!}
                </span>
            @endif

        </ul>

    </nav>


    <p class="blocktext">
        {!! __('Showing') !!}
        @if ($paginator->firstItem())
            <span class="font-medium">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span class="font-medium">{{ $paginator->lastItem() }}</span>
        @else
            {{ $paginator->count() }}
        @endif
        {!! __('of') !!}
        <span class="font-medium">{{ $paginator->total() }}</span>
        {!! __('results') !!}
    </p>


    <style>
        p.blocktext {
            margin-left: auto;
            margin-right: auto;
            width: 16em
        }
    </style>
@endif
