<div class="ecl-site-header__login-container">


    @guest
        <a class="ecl-button ecl-button--tertiary ecl-site-header__login-toggle"
           href="{{ route('profile.start') }}" data-ecl-login-toggle>
            <svg class="ecl-icon ecl-icon--s ecl-site-header__icon"
                 focusable="false"
                 aria-hidden="false"
                 role="img"
            >
                <title>Log in</title>
                <x-ecl.icon icon="log-in"/>
            </svg>
            Log In</a>
    @endguest
    @auth

        <a class="ecl-button ecl-button--tertiary ecl-site-header__login-toggle"
           href="{{ route('profile.start') }}" data-ecl-login-toggle>
            <svg class="ecl-icon ecl-icon--s ecl-site-header__icon"
                 focusable="false"
                 aria-hidden="true"
                 role="img">
                <title>Logged in</title>
                <x-ecl.icon icon="logged-in"/>
            </svg>
            Logged In</a>
        
    @endauth

</div>
