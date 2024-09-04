@if ($paginator->hasPages())

    <nav class="ecl-pagination" aria-label="Pagination">

        <ul class="ecl-pagination__list">
            @if (!$paginator->onFirstPage())
                <li class="ecl-pagination__item ecl-pagination__item--previous">
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="ecl-link ecl-link--standalone ecl-link--icon ecl-pagination__link ecl-link--icon-only"
                       aria-label="Go to previous page">
                        <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-270 ecl-link__icon"
                             focusable="false" aria-hidden="true">
                            <x-ecl.icon icon="corner-arrow" />
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

                            <li class="ecl-pagination__item ecl-pagination__item--current"><span
                                    class="ecl-pagination__text ecl-pagination__text--summary"
                                    aria-current="true">{{ $page }}</span><span
                                    class="ecl-pagination__text ecl-pagination__text--full"
                                    aria-current="true">Page {{ $page }}</span></li>
                        @else

                            <li class="ecl-pagination__item"><a href="{{ $url }}"
                                                                class="ecl-link ecl-link--standalone ecl-pagination__link"
                                                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="ecl-pagination__item ecl-pagination__item--next">
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="ecl-link ecl-link--standalone ecl-link--icon ecl-pagination__link ecl-link--icon-only"
                       aria-label="Go to next page"><span
                            class="ecl-link__label">Next</span>
                        <svg
                            class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-link__icon" focusable="false"
                            aria-hidden="true">
                            <x-ecl.icon icon="corner-arrow" />
                        </svg>
                    </a></li>
            @endif

        </ul>

    </nav>


    <p class="blocktext ecl-u-type-paragraph">
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
