<footer class="ecl-site-footer">
    <div class="ecl-container ecl-site-footer__container">
        <div class="ecl-site-footer__row">
            <div class="ecl-site-footer__column">
                <div class="ecl-site-footer__section"><a href="https://ec.europa.eu"
                                                         class="ecl-link ecl-link--standalone ecl-site-footer__logo-link" aria-label="European Commission"><img
                            alt="European Commission logo" title="European Commission" class="ecl-site-footer__logo-image-desktop"
                            src="{{asset('static/media/logo-ec--en.4369895b.svg')}}" /></a>
                    <div class="ecl-site-footer__description">This site is managed by the Directorate-General for &quot;Communications Networks, Content and Technology&quot;

                    </div>
                </div>

            </div>
            <div class="ecl-site-footer__column">
                <div class="ecl-site-footer__section">
                    <h2 class="ecl-site-footer__title ecl-site-footer__title--separator">
                        Contact us </h2>
                    <ul class="ecl-site-footer__list">
                        <li class="ecl-site-footer__list-item"><a href="https://commission.europa.eu/about-european-commission/contact_en"
                                                                  class="ecl-link ecl-link--standalone ecl-site-footer__link"
                                                                  aria-label="Link to Contact information of the DG">Contact information</a></li>
                    </ul>
                </div>
{{--                <div class="ecl-site-footer__section">--}}
{{--                    <h2 class="ecl-site-footer__title ecl-site-footer__title--separator">--}}
{{--                        Our data </h2>--}}
{{--                    <ul class="ecl-site-footer__list">--}}
{{--                        <li class="ecl-site-footer__list-item"><a href="{{route('datasets.index')}}"--}}
{{--                                                                  class="ecl-link ecl-link--standalone ecl-site-footer__link"--}}
{{--                                                                  aria-label="Link to Contact information of the DG">Datasets</a></li>--}}
{{--                    </ul>--}}
{{--                </div>--}}
            </div>
            <div class="ecl-site-footer__column">
                <div class="ecl-site-footer__section">
                    <h2 class="ecl-site-footer__title ecl-site-footer__title--separator">
                        About us </h2>
                    <ul class="ecl-site-footer__list">
                        <li class="ecl-site-footer__list-item"><a href="https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX%3A32022R2065&qid=1671203215141"
                                                                  class="ecl-link ecl-link--standalone ecl-site-footer__link"
                                                                  aria-label="Link to Information about the DG">Digital Services Act Regulation</a></li>
                    </ul>
                </div>
                <div class="ecl-site-footer__section">
                    <h2 class="ecl-site-footer__title ecl-site-footer__title--separator">
                        Policies </h2>
                    <ul class="ecl-site-footer__list">
                        <li class="ecl-site-footer__list-item"><a href="{{ route('page.show', ['page' => 'cookie-policy']) }}"
                                                                  class="ecl-link ecl-link--standalone ecl-site-footer__link"
                                                                  aria-label="Cookie Policy">Cookie Policy</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</footer>
