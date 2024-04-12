<footer class="ecl-site-footer">
    <div class="ecl-container ecl-site-footer__container">
        <div class="ecl-site-footer__row">
            <div class="ecl-site-footer__column">
                <div class="ecl-site-footer__section">
                    <a href="https://ec.europa.eu"
                       class="ecl-link ecl-link--standalone ecl-site-footer__logo-link"
                       aria-label="European Commission">
                        <img alt="European Commission logo" title="European Commission"
                             class="ecl-site-footer__logo-image-desktop"
                             src="{{asset('static/ecl/images/logo/negative/logo-ec--en.svg')}}">
                    </a>
                    <div class="ecl-site-footer__description">
                        {{__('footer.This site is managed by the Directorate-General for "Communications Networks, Content and Technology"')}}
                    </div>
                </div>

            </div>
            <div class="ecl-site-footer__column">
                <div class="ecl-site-footer__section">
                    <h2 class="ecl-site-footer__title ecl-site-footer__title--separator">
                        {{__('footer.Contact us')}} </h2>
                    <ul class="ecl-site-footer__list">
                        <li class="ecl-site-footer__list-item">
                            <a href="https://digital-strategy.ec.europa.eu/en/write-us"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Link to Contact information of the DG">{{__('footer.Contact information')}}</a></li>
                        <li class="ecl-site-footer__list-item">
                            <a href="{{route('feedback.index')}}"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Link to Contact information of the DG">{{__('footer.Feedback')}}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="ecl-site-footer__column">
                <div class="ecl-site-footer__section">
                    <h2 class="ecl-site-footer__title ecl-site-footer__title--separator">
                        {{__('footer.About us')}} </h2>
                    <ul class="ecl-site-footer__list">
                        <li class="ecl-site-footer__list-item">
                            <a href="https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX%3A32022R2065&qid=1671203215141"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Link to Information about the DG">{{__('footer.Digital Services Act Regulation')}}</a></li>

                        <li class="ecl-site-footer__list-item">
                            <a href="https://digital-strategy.ec.europa.eu/en/policies/safer-online"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Learn more about the DSA">{{__('footer.Learn more about the DSA')}}</a></li>

                        <li class="ecl-site-footer__list-item">
                            <a href="https://digital-strategy.ec.europa.eu/en/policies/safer-online"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Learn more about the DSA">{{__('footer.Announcements')}}</a></li>


                    </ul>
                </div>
                <div class="ecl-site-footer__section">
                    <h2 class="ecl-site-footer__title ecl-site-footer__title--separator">
                        {{__('footer.Policies')}} </h2>
                    <ul class="ecl-site-footer__list">
                        <li class="ecl-site-footer__list-item">
                            <a href="{{ route('page.show', ['page' => 'privacy-policy']) }}"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Cookie Policy">{{__('footer.Privacy Policy')}}</a></li>
                        <li class="ecl-site-footer__list-item">
                            <a href="{{ route('page.show', ['page' => 'legal-information']) }}"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Cookie Policy">{{__('footer.Legal Notice')}}</a></li>

                        <li class="ecl-site-footer__list-item">
                            <a href="{{ route('page.show', ['page' => 'data-retention-policy']) }}"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                               aria-label="Cookie Policy">{{__('footer.Data Retention Policy')}}</a></li>

                    </ul>
                </div>

            </div>
        </div>
    </div>
</footer>
