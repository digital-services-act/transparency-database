<footer class="ecl-site-footer">
    <div class="ecl-container ecl-site-footer__container">
        <div class="ecl-footer-core__row">

            <div class="ecl-footer-core__column">
                <div class="ecl-footer-core__section">
                    <a href="{{ route('home') }}" class="ecl-link ecl-link--standalone ecl-footer-core__logo-link"
                       aria-label="European&#x20;Commission">
                        <img alt="European Commission logo" title="European Commission"
                             class="ecl-footer-core__logo-image-desktop"
                             src="{{asset('static/media/logo-ec--en.65cfd447.svg')}}"/>
                    </a>

                    <h2 class="ecl-footer-core__title">
                        <a href="/" class="ecl-link ecl-link--standalone ecl-footer-core__title-link">European Commission website</a>
                    </h2>

                    <div class="ecl-footer-core__description">This site is managed by the Directorate-General for
                        Communication
                    </div>
                </div>
            </div>

            <div class="ecl-footer-core__column">

                <div class="ecl-footer-core__section ecl-footer-core__section--separator">
                    <ul class="ecl-footer-core__list ecl-footer-core__list--columns">
                        <li class="ecl-footer-core__list-item">
                            <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                               aria-label="Link&#x20;to&#x20;Strategy">Link 1</a>
                        </li>
                    </ul>
                </div>

                <div class="ecl-footer-core__section">
                    <ul class="ecl-footer-core__list">
                        <li class="ecl-footer-core__list-item">
                            <a href="{{route('home')}}"
                               class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after ecl-footer-core__link"
                               aria-label="Link&#x20;to&#x20;Follow&#x20;the&#x20;European&#x20;Commission&#x20;on&#x20;social&#x20;media">
                                <span class="ecl-link__label">Link 2</span>
                                <svg class="ecl-icon ecl-icon--2xs ecl-link__icon" focusable="false"
                                     aria-hidden="true">
                                    <x-ecl.icon icon="external"/>
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="ecl-footer-core__section">
                    <ul class="ecl-footer-core__list">
                        <li class="ecl-footer-core__list-item">
                            <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                               aria-label="Link&#x20;to&#x20;Cookies">Cookies</a>
                        </li>
                        <li class="ecl-footer-core__list-item">
                            <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                               aria-label="Link&#x20;to&#x20;Privacy&#x20;policy">Privacy policy</a>
                        </li>
                        <li class="ecl-footer-core__list-item">
                            <a href="{{route('home')}}" class="ecl-link ecl-link--standalone ecl-footer-core__link"
                               aria-label="Link&#x20;to&#x20;Legal&#x20;notice">Legal notice</a>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</footer>