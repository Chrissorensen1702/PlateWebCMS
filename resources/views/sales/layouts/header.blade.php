<div class="marketing-header__inner">
    <a href="{{ route('home') }}" class="brand-lockup">
        <x-application-header-logo class="brand-lockup__wordmark" />
    </a>

    <nav class="marketing-nav">
        <a href="{{ route('templates') }}" class="marketing-nav__link{{ request()->routeIs('templates') ? ' marketing-nav__link--active' : '' }}">Vores priser</a>
        <a href="{{ route('sales.customer-cms') }}" class="marketing-nav__link{{ request()->routeIs('sales.customer-cms') ? ' marketing-nav__link--active' : '' }}">Kunde-CMS</a>
        <a href="{{ route('custom-build') }}" class="marketing-nav__link{{ request()->routeIs('custom-build') ? ' marketing-nav__link--active' : '' }}">Custom build</a>
        <a href="{{ route('contact') }}" class="marketing-nav__link{{ request()->routeIs('contact') ? ' marketing-nav__link--active' : '' }}">Kontakt</a>
    </nav>

    <div class="marketing-header__actions">
        @auth
            <a href="{{ route('dashboard') }}" class="ui-button ui-button--outline">
                Gå til CMS-modul
            </a>
        @else
            <a href="{{ route('contact') }}" class="ui-button ui-button--ink">
                Prøv 30 dage gratis
            </a>
            <a href="{{ route('login') }}" class="ui-button ui-button--outline">
                Kundelogin
            </a>
        @endauth
    </div>

    <div class="marketing-header__mobile-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="ui-button ui-button--outline marketing-header__mobile-login">
                CMS-modul
            </a>
        @else
            <a href="{{ route('login') }}" class="ui-button ui-button--outline marketing-header__mobile-login">
                Kundelogin
            </a>
        @endauth

        <button
            type="button"
            class="marketing-mobile-nav-toggle"
            data-mobile-nav-toggle
            aria-expanded="false"
            aria-controls="marketing-mobile-drawer"
            aria-label="Åbn menu"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</div>

<div class="marketing-mobile-nav" data-mobile-nav aria-hidden="true">
    <button type="button" class="marketing-mobile-nav__backdrop" data-mobile-nav-close aria-label="Luk menu"></button>

    <aside class="marketing-mobile-nav__drawer" id="marketing-mobile-drawer" aria-label="Mobilmenu">
        <div class="marketing-mobile-nav__header">
            <a href="{{ route('home') }}" class="brand-lockup brand-lockup--mobile" data-mobile-nav-close>
                <x-application-header-logo class="brand-lockup__wordmark" />
            </a>

            <button type="button" class="marketing-mobile-nav__close" data-mobile-nav-close aria-label="Luk menu">
                <span></span>
                <span></span>
            </button>
        </div>

        <nav class="marketing-mobile-nav__links" aria-label="Mobil navigation">
            <a href="{{ route('templates') }}" class="marketing-mobile-nav__link{{ request()->routeIs('templates') ? ' marketing-mobile-nav__link--active' : '' }}">Vores priser</a>
            <a href="{{ route('sales.customer-cms') }}" class="marketing-mobile-nav__link{{ request()->routeIs('sales.customer-cms') ? ' marketing-mobile-nav__link--active' : '' }}">Kunde-CMS</a>
            <a href="{{ route('custom-build') }}" class="marketing-mobile-nav__link{{ request()->routeIs('custom-build') ? ' marketing-mobile-nav__link--active' : '' }}">Custom build</a>
            <a href="{{ route('contact') }}" class="marketing-mobile-nav__link{{ request()->routeIs('contact') ? ' marketing-mobile-nav__link--active' : '' }}">Kontakt</a>
        </nav>

        <div class="marketing-mobile-nav__footer">
            @auth
                <a href="{{ route('dashboard') }}" class="ui-button ui-button--ink marketing-mobile-nav__cta">
                    Gå til CMS-modul
                </a>
            @else
                <a href="{{ route('contact') }}" class="ui-button ui-button--ink marketing-mobile-nav__cta">
                    Prøv 30 dage gratis
                </a>
            @endauth
        </div>
    </aside>
</div>
