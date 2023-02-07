<div class="ecl-site-header__search-container"><a
        class="ecl-button ecl-button--ghost ecl-site-header__search-toggle" href="/"
        data-ecl-search-toggle="true" aria-controls="search-form-id" aria-expanded="false">
        <svg
            class="ecl-icon ecl-icon--s ecl-site-header__icon" focusable="false"
            aria-hidden="true">
            <use xlink:href="{{asset('static/media/icons.148a2e16.svg#search')}}"></use>
        </svg>
        Search</a>
    <form class="ecl-search-form ecl-site-header__search" role="search" data-ecl-search-form=""
          id="search-form-id" action="{{route('search')}}" method="get">
        @csrf
        <div class="ecl-form-group"><label for="search-input-id"
                                           class="ecl-form-label ecl-search-form__label">Search</label><input
                type="search" id="search-input-id" name="query"
                class="ecl-text-input ecl-text-input--m ecl-search-form__text-input"
                placeholder="Search for a statement or entity"/>
        </div>
        <button class="ecl-button ecl-button--search ecl-search-form__button" type="submit"
                aria-label="Search"><span class="ecl-button__container"><span
                    class="ecl-button__label"
                    data-ecl-label="true">Search</span><svg
                    class="ecl-icon ecl-icon--xs ecl-button__icon ecl-button__icon--after"
                    focusable="false"
                    aria-hidden="true" data-ecl-icon="">
                    <use xlink:href="{{asset('static/media/icons.148a2e16.svg#search')}}"></use>
                  </svg></span></button>
    </form>
</div>
