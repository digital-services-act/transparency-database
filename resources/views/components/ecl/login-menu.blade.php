<div class="ecl-site-header__login-container">

    @guest
        <a class="ecl-button ecl-button--tertiary ecl-site-header__login-toggle"
           href="{{ route('profile.start') }}">
            <svg class="ecl-icon ecl-icon--s ecl-site-header__icon"
                 focusable="false"
                 aria-hidden="false"
                 role="img"
            >
                <title>Log in</title>
                <x-ecl.icon icon="log-in"/>
            </svg>
            {{__('menu.Log In')}}</a>
    @endguest
    @auth

            <a class="ecl-button ecl-button--tertiary ecl-site-header__login-toggle"
               href="#"
               data-ecl-login-toggle="true"
               aria-controls="login-box-id" aria-expanded="false">
                <svg class="ecl-icon ecl-icon--s ecl-icon--rotate-0 ecl-site-header__icon"
                     focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="logged-in"/>
                </svg>
                {{ __('menu.Logged In') }}
                <span>&nbsp;</span>
                <svg class="ecl-icon ecl-icon--xs ecl-icon--rotate-180"
                     focusable="false" aria-hidden="false">
                    <x-ecl.icon icon="corner-arrow"/>
                </svg>
            </a>

            <div id="login-box-id" class="ecl-site-header__login-box"
                 data-ecl-login-box="true">
                <x-ecl.menu-item icon="log-in" :link="route('profile.start')"
                                 title="{{__('menu.Your Profile')}}"/>
                @can('create statements')
                    <x-ecl.menu-item icon="settings" :link="route('profile.api.index')"
                                     title="{{__('menu.API Token Management')}}"/>

                    <x-ecl.menu-item icon="edit" :link="route('statement.create')"
                                     title="{{__('menu.Submit statements of reasons')}}"/>

                @endcan
                <hr class="ecl-site-header__login-separator">
                <x-ecl.menu-item link="/logout" title="{{__('menu.Logout')}}"/>
            </div>

    @endauth
</div>


