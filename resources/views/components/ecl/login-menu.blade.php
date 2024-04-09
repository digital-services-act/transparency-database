<div class="ecl-site-header__login-container">


    @guest
        <a class="ecl-button ecl-button--tertiary ecl-site-header__login-toggle"
           href="{{ route('profile.start') }}" data-ecl-login-toggle>
            <svg class="ecl-icon ecl-icon--s ecl-site-header__icon"
                 focusable="false"
                 aria-hidden="false"
                 role="img"
            >
                <title>{{__('menu.Log In')}}</title>
                <x-ecl.icon icon="log-in"/>
            </svg>
            {{__('menu.Log In')}}</a>
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
                {{__('menu.Logged In')}}</a>

            <div id="login-box-id" class="ecl-site-header__login-box"
                 data-ecl-login-box="true">
                <x-ecl.menu-item icon="log-in" :link="route('profile.start')"
                                 title="{{__('menu.Your Profile')}}"/>
            <div id="login-box-id" class="ecl-site-header__login-box" data-ecl-login-box="">
                <a href="{{ route('profile.start') }}">{{__('menu.Your Profile')}}</a><br />
                @can('create statements')
                    <x-ecl.menu-item icon="settings" :link="route('profile.api.index')"
                                     title="{{__('menu.API Token Management')}}"/>
                   <a href="{{ route('profile.api.index') }}">{{__('menu.API Token Management')}}</a><br />
                @endcan
                <hr class="ecl-site-header__login-separator">

                <a href="/logout">{{__('menu.Logout')}}</a>
            </div>


    @endauth
</div>


