<div x-show="open" class="ecl-language-list ecl-language-list--overlay" data-ecl-language-list-overlay=""
     aria-labelledby="ecl-language-list__title" role="dialog" id="language-list-overlay"
     aria-modal="true">
    <div class="ecl-language-list__container ecl-container">
        <div class="ecl-row">
            <div class="ecl-language-list__close ecl-col-12 ecl-col-l-8 ecl-offset-l-2">
                <button class="ecl-button ecl-button--ghost ecl-language-list__close-button"
                        type="submit" data-ecl-language-list-close="" x-on:click="open = ! open"><span
                        class="ecl-button__container"><span class="ecl-button__label"
                                                            data-ecl-label="true">Close</span><svg
                            class="ecl-icon ecl-icon--s ecl-button__icon ecl-button__icon--after"
                            focusable="false" aria-hidden="true" data-ecl-icon=""><use
                                xlink:href="{{asset('static/media/icons.148a2e16.svg#close')}}"></use></svg></span>
                </button>
            </div>
            <div class="ecl-language-list__title ecl-col-12 ecl-col-l-8 ecl-offset-l-2"
                 id="ecl-language-list__title">
                <svg class="ecl-icon ecl-icon--m ecl-language-list__title-icon"
                     focusable="false" aria-hidden="true">
                    <use xlink:href="{{asset('static/media/icons.148a2e16.svg#generic-lang')}}"></use>
                </svg>
                Select your language
            </div>
        </div>
        <div class="ecl-row ecl-language-list__eu">
            <div class="ecl-language-list__category ecl-col-12 ecl-col-l-8 ecl-offset-l-2">EU
                official languages
            </div>
            <div class="ecl-language-list__column ecl-col-12 ecl-col-l-4 ecl-offset-l-2">
                <ul class="ecl-language-list__list">


                    {{--                            <li class="ecl-language-list__item ecl-language-list__item--is-active"><a--}}
                    {{--                                    href="/component-library/example"--}}
                    {{--                                    class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after ecl-language-list__link"--}}
                    {{--                                    lang="en" hreflang="en" rel="alternate"><span--}}
                    {{--                                        class="ecl-link__label">English</span>--}}
                    {{--                                    <svg class="ecl-icon ecl-icon--xs ecl-link__icon" focusable="false"--}}
                    {{--                                         aria-hidden="true">--}}
                    {{--                                        <use xlink:href="{{asset('static/media/icons.148a2e16.svg#check')}}"></use>--}}
                    {{--                                    </svg>--}}
                    {{--                                </a></li>--}}

                    <x-ecl.language-item lang="en"></x-ecl.language-item>



                </ul>
            </div>
            <div class="ecl-language-list__column ecl-col-12 ecl-col-l-4">
                <ul class="ecl-language-list__list">
                    <x-language-item lang="fr"></x-language-item>
{{--                    <li class="ecl-language-list__item">--}}
{{--                        <a class="ecl-link ecl-link--standalone ecl-language-list__link"--}}
{{--                           href="/setlocale/?locale=fr">français</a>--}}
{{--                    </li>--}}

                </ul>
            </div>
        </div>

    </div>
</div>
