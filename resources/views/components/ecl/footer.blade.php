<footer class="ecl-site-footer">
    <div class="ecl-site-footer__main">
        <div class="ecl-container ecl-site-footer__container">
            <div class="ecl-site-footer__row ecl-site-footer__row--specific">
                <div class="ecl-site-footer__section ecl-site-footer__section--site-info">
                    <div class="ecl-site-footer__title">
                        <a href="{{ route('home') }}"
                           class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__title-link">
                            DSA Transparency Database
                        </a>
                    </div>
                    <div class="ecl-site-footer__description">
                        This site is managed by the Directorate-General for "Communications Networks, Content and Technology"
                    </div>
                </div>

                <div class="ecl-site-footer__section ecl-site-footer__section--links">
                    <div class="ecl-site-footer__row">
                        <div class="ecl-site-footer__section ecl-site-footer__section--contact">
                            <div class="ecl-site-footer__title ecl-site-footer__title--separator">
                                Contact us
                            </div>
                            <ul class="ecl-site-footer__list">
                                <li class="ecl-site-footer__list-item">
                                    <a href="https://digital-strategy.ec.europa.eu/en/write-us"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Contact information">Contact information</a>
                                </li>
                                <li class="ecl-site-footer__list-item">
                                    <a href="{{ route('feedback.index') }}"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Feedback">Feedback</a>
                                </li>
                            </ul>
                        </div>

                        <div class="ecl-site-footer__section ecl-site-footer__section--about">
                            <div class="ecl-site-footer__title ecl-site-footer__title--separator">
                                About us
                            </div>
                            <ul class="ecl-site-footer__list">
                                <li class="ecl-site-footer__list-item">
                                    <a href="https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX%3A32022R2065&qid=1671203215141"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Digital Services Act Regulation">Digital Services Act Regulation</a>
                                </li>
                                <li class="ecl-site-footer__list-item">
                                    <a href="https://digital-strategy.ec.europa.eu/en/policies/safer-online"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Learn more about the DSA">Learn more about the DSA</a>
                                </li>
                                <li class="ecl-site-footer__list-item">
                                    <a href="https://digital-strategy.ec.europa.eu/en/faqs/dsa-transparency-database-questions-and-answers"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="FAQ">FAQ</a>
                                </li>
                                <li class="ecl-site-footer__list-item">
                                    <a href="{{ route('page.show', ['page' => 'announcements']) }}"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Announcements">Announcements</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="ecl-site-footer__row">
                        <div class="ecl-site-footer__section ecl-site-footer__section--policies">
                            <div class="ecl-site-footer__title ecl-site-footer__title--separator">
                                Policies
                            </div>
                            <ul class="ecl-site-footer__list">
                                <li class="ecl-site-footer__list-item">
                                    <a href="{{ route('page.show', ['page' => 'privacy-policy']) }}"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label=" Privacy Policy">Privacy Policy</a>
                                </li>
                                <li class="ecl-site-footer__list-item">
                                    <a href="{{ route('page.show', ['page' => 'legal-information']) }}"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Legal Notice">Legal Notice</a>
                                </li>
                                <li class="ecl-site-footer__list-item">
                                    <a href="{{ route('page.show', ['page' => 'data-retention-policy']) }}"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Data Retention Policy">Data Retention Policy</a>
                                </li>
                                <li class="ecl-site-footer__list-item">
                                    <a href="{{ route('page.show', ['page' => 'data-license']) }}"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link"
                                       aria-label="Data Retention Policy">Data License</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ecl-site-footer__row ecl-site-footer__row--common">
                <div class="ecl-site-footer__section ecl-site-footer__section--common">
                    <a href="https://commission.europa.eu/index_en"
                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__logo-link"
                       aria-label="European Commission">
                        <picture class="ecl-picture ecl-site-footer__picture">
                            <img alt="European Commission logo"
                                 title="European Commission"
                                 class="ecl-site-footer__logo-image"
                                 src="{{ asset('static/ecl/images/logo/negative/logo-ec--en.svg') }}">
                        </picture>
                    </a>
                    <div class="ecl-site-footer__extra-links-container">
                        <div class="ecl-social-media-follow ecl-social-media-follow--left ecl-social-media-follow--description_inline ecl-site-footer__social-media">
                            <p class="ecl-social-media-follow__description">Follow us</p>
                            <ul class="ecl-social-media-follow__list">
                                <li class="ecl-social-media-follow__item">
                                    <a href="https://ec.social-network.europa.eu/@EUCommission"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-link--icon ecl-social-media-follow__link ecl-link--icon-only"
                                       data-section-label-untranslated="Follow us"
                                       data-footer-link-label="Mastodon">
                                        <x-ecl.social-icon network="mastodon" />
                                        <span class="ecl-link__label">Mastodon</span>
                                    </a>
                                </li>
                                <li class="ecl-social-media-follow__item">
                                    <a href="https://www.linkedin.com/company/european-commission"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-link--icon ecl-social-media-follow__link ecl-link--icon-only"
                                       data-section-label-untranslated="Follow us"
                                       data-footer-link-label="LinkedIn">
                                        <x-ecl.social-icon network="linkedin" />
                                        <span class="ecl-link__label">LinkedIn</span>
                                    </a>
                                </li>
                                <li class="ecl-social-media-follow__item">
                                    <a href="https://wsocial.eu/profile/ec.europa.eu/"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-link--icon ecl-social-media-follow__link ecl-link--icon-only"
                                       data-section-label-untranslated="Follow us"
                                       data-footer-link-label="WSocial">
                                        <x-ecl.social-icon network="wsocial" />
                                        <span class="ecl-link__label">WSocial</span>
                                    </a>
                                </li>
                                <li class="ecl-social-media-follow__item">
                                    <a href="https://www.facebook.com/EuropeanCommission"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-link--icon ecl-social-media-follow__link ecl-link--icon-only"
                                       data-section-label-untranslated="Follow us"
                                       data-footer-link-label="Facebook">
                                        <x-ecl.social-icon network="facebook" />
                                        <span class="ecl-link__label">Facebook</span>
                                    </a>
                                </li>
                                <li class="ecl-social-media-follow__item">
                                    <a href="https://www.youtube.com/user/eutube"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-link--icon ecl-social-media-follow__link ecl-link--icon-only"
                                       data-section-label-untranslated="Follow us"
                                       data-footer-link-label="Youtube">
                                        <x-ecl.social-icon network="youtube" />
                                        <span class="ecl-link__label">Youtube</span>
                                    </a>
                                </li>
                                <li class="ecl-social-media-follow__item">
                                    <a href="https://european-union.europa.eu/contact-eu/social-media-channels_en"
                                       class="ecl-link ecl-link--standalone ecl-link--inverted ecl-link--icon ecl-social-media-follow__link ecl-link--icon-only"
                                       data-section-label-untranslated="Follow us"
                                       data-footer-link-label="Other networks">
                                        <x-ecl.social-icon network="other" />
                                        <span class="ecl-link__label">Other networks</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <ul class="ecl-site-footer__list ecl-site-footer__list--inline">
                        <li class="ecl-site-footer__list-item">
                            <a href="https://commission.europa.eu/legal-notice/vulnerability-disclosure-policy_en"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link">Report an IT vulnerability</a>
                        </li>
                        <li class="ecl-site-footer__list-item">
                            <a href="https://commission.europa.eu/languages-our-websites_en"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link">Languages on our websites</a>
                        </li>
                        <li class="ecl-site-footer__list-item">
                            <a href="https://commission.europa.eu/cookies-policy_en"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link">Cookies</a>
                        </li>
                        <li class="ecl-site-footer__list-item">
                            <a href="https://commission.europa.eu/privacy-policy-websites-managed-european-commission_en"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link">Privacy policy</a>
                        </li>
                        <li class="ecl-site-footer__list-item">
                            <a href="https://commission.europa.eu/legal-notice_en"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link">Legal notice</a>
                        </li>
                        <li class="ecl-site-footer__list-item">
                            <a href="https://commission.europa.eu/accessibility-statement_en"
                               class="ecl-link ecl-link--standalone ecl-link--inverted ecl-site-footer__link">Accessibility</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
