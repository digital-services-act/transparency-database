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
                     aria-controls="login-box-id"
                     role="img">
                    <title>Logged in</title>
                    <x-ecl.icon icon="logged-in"/>
                </svg>
                Logged In</a>

            <div id="login-box-id" class="ecl-site-header__login-box" data-ecl-login-box="">
                <a href="{{ route('profile.start') }}">Your Profile</a><br />
                @can('create statements')
                   <a href="{{ route('profile.api.index') }}">API Token Management</a><br />
                @endcan
                <hr class="ecl-site-header__login-separator">
                <a href="/logout">Logout</a>
            </div>


    @endauth

</div>
