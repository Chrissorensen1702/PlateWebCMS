@php
    $productLinks = [
        [
            'label' => 'Websitebuilder',
            'href' => route('templates'),
            'active' => request()->routeIs('templates'),
        ],
        [
            'label' => 'Kunde-CMS',
            'href' => route('sales.customer-cms'),
            'active' => request()->routeIs('sales.customer-cms'),
        ],
        [
            'label' => 'Bookingsystem',
            'href' => route('home').'#produkt-bookingsystem',
            'active' => false,
        ],
    ];

    $productsActive = collect($productLinks)->contains(fn (array $link) => $link['active']);
    $designsActive = request()->routeIs('sales.designs');
    $aboutActive = request()->routeIs('sales.about');
    $mobileAppHref = route('sales.mobile-app');
    $isHomePage = request()->routeIs('home');
    $isRegisterPage = request()->routeIs('register');
    $isGetStartedPage = request()->routeIs('sales.get-started');
    $gettingStartedHref = $isGetStartedPage || $isRegisterPage
        ? route('templates').'#pricing-guide'
        : route('sales.get-started');
    $gettingStartedLabel = $isGetStartedPage
        ? 'Beregn pris'
        : ($isRegisterPage ? 'Se priser' : 'Kom i gang');
@endphp

<div class="marketing-topbar">
    <div class="marketing-topbar__inner">
        <div class="marketing-topbar__copy">
            <p class="marketing-topbar__text">
                <span class="marketing-topbar__lead">HJÆÆÆLP! 😄 🇩🇰</span>
                <span>Vi søger få virksomheder til at forme vores første templates - Få custom design til Studio-pris inkl. 6 måneder gratis.</span>
                <a href="mailto:kontakt@plateweb.dk?subject=Samarbejde%20om%20nyt%20template" class="marketing-topbar__inline-link">Læs mere</a>
            </p>
        </div>
    </div>
</div>

<div class="marketing-header__inner">
    <a href="{{ route('home') }}" class="brand-lockup">
        <img
            src="{{ asset('images/logo/plateweb-sales.svg') }}"
            alt="PlateWeb"
            class="brand-lockup__wordmark"
        />
    </a>

    <nav class="marketing-nav">
        <div class="marketing-nav__dropdown">
            <button type="button" class="marketing-nav__link marketing-nav__dropdown-trigger{{ $productsActive ? ' marketing-nav__link--active' : '' }}">
                <span>Produkter</span>
                <span class="marketing-nav__dropdown-icon" aria-hidden="true"></span>
            </button>

            <div class="marketing-nav__dropdown-menu" aria-label="Produkter">
                @foreach ($productLinks as $link)
                    <a href="{{ $link['href'] }}" class="marketing-nav__dropdown-link{{ $link['active'] ? ' marketing-nav__dropdown-link--active' : '' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <a href="{{ route('sales.designs') }}" class="marketing-nav__link{{ $designsActive ? ' marketing-nav__link--active' : '' }}">Se designs</a>
        <a href="{{ route('templates') }}" class="marketing-nav__link{{ request()->routeIs('templates') ? ' marketing-nav__link--active' : '' }}">Priser</a>
        <a href="{{ $mobileAppHref }}" class="marketing-nav__link{{ request()->routeIs('sales.mobile-app') ? ' marketing-nav__link--active' : '' }}">Mobilapp</a>
        <a href="{{ route('sales.about') }}" class="marketing-nav__link{{ $aboutActive ? ' marketing-nav__link--active' : '' }}">Om os</a>
        <a href="{{ route('contact') }}" class="marketing-nav__link{{ request()->routeIs('contact') ? ' marketing-nav__link--active' : '' }}">Kontakt os</a>
    </nav>

    <div class="marketing-header__actions">
        @auth
            <a href="{{ route('dashboard') }}" class="ui-button ui-button--outline">
                Gå til CMS-modul
            </a>
        @else
            <a href="{{ route('login') }}" class="ui-button ui-button--outline marketing-header__login-button">
                Kundelogin
            </a>

            @unless ($isHomePage)
                <a href="{{ $gettingStartedHref }}" class="ui-button ui-button--ink marketing-header__cta-button">
                    {{ $gettingStartedLabel }}
                </a>
            @endunless
        @endauth
    </div>

    <div class="marketing-header__mobile-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="ui-button ui-button--outline marketing-header__mobile-login">
                CMS-modul
            </a>
        @else
            <a href="{{ route('login') }}" class="ui-button ui-button--outline marketing-header__login-button marketing-header__mobile-login">
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
                <img
                    src="{{ asset('images/logo/plateweb-sales.svg') }}"
                    alt="PlateWeb"
                    class="brand-lockup__wordmark"
                />
            </a>

            <button type="button" class="marketing-mobile-nav__close" data-mobile-nav-close aria-label="Luk menu">
                <span></span>
                <span></span>
            </button>
        </div>

        <nav class="marketing-mobile-nav__links" aria-label="Mobil navigation">
            <details class="marketing-mobile-nav__group">
                <summary class="marketing-mobile-nav__link{{ $productsActive ? ' marketing-mobile-nav__link--active' : '' }}">
                    <span>Produkter</span>
                    <span class="marketing-mobile-nav__group-icon" aria-hidden="true"></span>
                </summary>

                <div class="marketing-mobile-nav__sublinks">
                    @foreach ($productLinks as $link)
                        <a href="{{ $link['href'] }}" class="marketing-mobile-nav__sublink{{ $link['active'] ? ' marketing-mobile-nav__sublink--active' : '' }}" data-mobile-nav-close>
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </details>

            <a href="{{ route('sales.designs') }}" class="marketing-mobile-nav__link{{ $designsActive ? ' marketing-mobile-nav__link--active' : '' }}" data-mobile-nav-close>Se designs</a>
            <a href="{{ route('templates') }}" class="marketing-mobile-nav__link{{ request()->routeIs('templates') ? ' marketing-mobile-nav__link--active' : '' }}" data-mobile-nav-close>Priser</a>
            <a href="{{ $mobileAppHref }}" class="marketing-mobile-nav__link{{ request()->routeIs('sales.mobile-app') ? ' marketing-mobile-nav__link--active' : '' }}" data-mobile-nav-close>Mobilapp</a>
            <a href="{{ route('sales.about') }}" class="marketing-mobile-nav__link{{ $aboutActive ? ' marketing-mobile-nav__link--active' : '' }}" data-mobile-nav-close>Om os</a>
            <a href="{{ route('contact') }}" class="marketing-mobile-nav__link{{ request()->routeIs('contact') ? ' marketing-mobile-nav__link--active' : '' }}" data-mobile-nav-close>Kontakt os</a>
        </nav>

        <div class="marketing-mobile-nav__footer">
            @auth
                <a href="{{ route('dashboard') }}" class="ui-button ui-button--ink marketing-mobile-nav__cta">
                    Gå til CMS-modul
                </a>
            @else
                @unless ($isHomePage)
                    <a href="{{ $gettingStartedHref }}" class="ui-button ui-button--ink marketing-mobile-nav__cta" data-mobile-nav-close>
                        {{ $gettingStartedLabel }}
                    </a>
                @endunless
            @endauth
        </div>
    </aside>
</div>
