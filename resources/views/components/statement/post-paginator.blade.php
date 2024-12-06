@if ($paginator->hasPages())
    <nav class="ecl-pagination" aria-label="Pagination">
        <ul class="ecl-pagination__list">
            @if (!$paginator->onFirstPage())
                <li class="ecl-pagination__item ecl-pagination__item--previous">
                    <form method="POST" action="{{ route('statement.index.post') }}" style="display: inline;">
                        @csrf
                        @foreach(request()->except(['page', '_token']) as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <input type="hidden" name="page" value="{{ $paginator->currentPage() - 1 }}">
                        <button type="submit" class="ecl-link ecl-link--standalone ecl-link--icon ecl-pagination__link ecl-link--icon-only" aria-label="Go to previous page">
                            <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-270 ecl-link__icon" focusable="false" aria-hidden="true">
                                <x-ecl.icon icon="corner-arrow" />
                            </svg>
                            <span class="ecl-link__label">Previous</span>
                        </button>
                    </form>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="ecl-pagination__item" aria-disabled="true">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="ecl-pagination__item ecl-pagination__item--current">
                                <span class="ecl-pagination__text ecl-pagination__text--summary" aria-current="true">{{ $page }}</span>
                                <span class="ecl-pagination__text ecl-pagination__text--full" aria-current="true">Page {{ $page }}</span>
                            </li>
                        @else
                            <li class="ecl-pagination__item">
                                <form method="POST" action="{{ route('statement.index.post') }}" style="display: inline;">
                                    @csrf
                                    @foreach(request()->except(['page', '_token']) as $key => $value)
                                        @if(is_array($value))
                                            @foreach($value as $item)
                                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                            @endforeach
                                        @else
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endif
                                    @endforeach
                                    <input type="hidden" name="page" value="{{ $page }}">
                                    <button type="submit" class="ecl-link ecl-link--standalone ecl-pagination__link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </button>
                                </form>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="ecl-pagination__item ecl-pagination__item--next">
                    <form method="POST" action="{{ route('statement.index.post') }}" style="display: inline;">
                        @csrf
                        @foreach(request()->except(['page', '_token']) as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <input type="hidden" name="page" value="{{ $paginator->currentPage() + 1 }}">
                        <button type="submit" class="ecl-link ecl-link--standalone ecl-link--icon ecl-pagination__link ecl-link--icon-only" aria-label="Go to next page">
                            <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-90 ecl-link__icon" focusable="false" aria-hidden="true">
                                <x-ecl.icon icon="corner-arrow" />
                            </svg>
                            <span class="ecl-link__label">Next</span>
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </nav>
@endif
