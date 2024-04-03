<div class="ecl-site-header__search-container" role="search">
    <a class="ecl-button ecl-button--tertiary ecl-site-header__search-toggle"
       href="{{ route('home') }}"
       data-ecl-search-toggle="true" aria-controls="search-form-id" aria-expanded="false">
        <svg class="ecl-icon ecl-icon--s ecl-site-header__icon" focusable="false"
             aria-hidden="false" role="img">
            <title>Search</title>
            <x-ecl.icon icon="search"/>
        </svg>
        Search
    </a>
    <form class="ecl-search-form ecl-site-header__search" role="search" data-ecl-search-form
          id="search-form-id">
        <div class="ecl-form-group">
            <label for="search-input-id" id="search-input-id-label"
                   class="ecl-form-label ecl-search-form__label">Search</label>
            <input id="search-input-id"
                   class="ecl-text-input ecl-text-input--m ecl-search-form__text-input"
                   type="search"
                   placeholder="Placeholder text"/>
        </div>
        <button class="ecl-button ecl-button--ghost ecl-search-form__button" type="submit"
                aria-label="Search">
            <span class="ecl-button__container">
                <svg class="ecl-icon ecl-icon--xs ecl-button__icon" focusable="false"
                     aria-hidden="true" data-ecl-icon>
                    <x-ecl.icon icon="search"/>
                </svg>
                <span class="ecl-button__label" data-ecl-label="true">Search</span>
            </span>
        </button>
    </form>
</div>