@php
    $newsletterErrors = $errors->getBag('newsletterSignup');

    $navigationLinks = [
        ['label' => 'Forside', 'href' => route('home')],
        ['label' => 'Om os', 'href' => route('sales.about')],
        ['label' => 'Se designs', 'href' => route('sales.designs')],
        ['label' => 'Vores priser', 'href' => route('templates')],
        ['label' => 'Custom build', 'href' => route('custom-build')],
        ['label' => 'Kunde-CMS', 'href' => route('sales.customer-cms')],
        ['label' => 'Kontakt', 'href' => route('contact')],
    ];

    $solutionLinks = [
        ['label' => 'Hjemmeside', 'href' => route('templates')],
        ['label' => 'Bookingsystem', 'href' => route('contact')],
        ['label' => 'Statistik', 'href' => route('contact')],
        ['label' => 'Betaling', 'href' => route('contact')],
    ];

    $socialPlatforms = [
        ['label' => 'Instagram', 'platform' => 'instagram'],
        ['label' => 'Facebook', 'platform' => 'facebook'],
        ['label' => 'LinkedIn', 'platform' => 'linkedin'],
    ];

    $paymentMethods = [
        ['label' => 'Visa', 'asset' => 'images/payments-official/visa.svg', 'class' => 'marketing-footer__payment-logo--visa'],
        ['label' => 'Mastercard', 'asset' => 'images/payments-official/mastercard.png', 'class' => 'marketing-footer__payment-logo--mastercard'],
    ];
@endphp

<footer class="site-common-footer marketing-footer">
    <div class="site-common-footer__surface">
        <div class="ui-shell site-common-footer__inner">
            <section class="site-common-footer__section site-common-footer__section--brand">
                <div class="site-common-footer__section-header">
                    <h2 class="site-common-footer__section-title">Kontakt os</h2>
                </div>

                <div class="marketing-footer__brand-stack">
                    <div class="marketing-footer__contact-panel">
                        <div class="site-common-footer__brand-block">
                            <div class="site-common-footer__brand-copy">
                                <p class="site-common-footer__lead marketing-footer__contact-copy">
                                    PlateDigital<br>
                                    CVR: 42456187<br>
                                    +45 20 63 12 99<br>
                                    hallo@plateweb.dk
                                </p>
                            </div>
                        </div>

                        <div class="site-common-footer__action">
                            <a href="{{ route('contact') }}" class="site-common-footer__cta">
                                Se kontaktformular
                            </a>
                        </div>
                    </div>

                    <div class="marketing-footer__powered-brand">
                        <p class="marketing-footer__powered-by">En del af</p>
                        <img
                            src="{{ asset('images/logo/PlateDigital-logo-saas.svg') }}"
                            alt="PlateDigital"
                            class="site-common-footer__logo marketing-footer__brand-logo"
                        >
                    </div>
                </div>
            </section>

            <section class="site-common-footer__section">
                <div class="site-common-footer__section-header">
                    <h2 class="site-common-footer__section-title">Navigation</h2>
                </div>

                <nav class="site-common-footer__nav" aria-label="Footer navigation">
                    @foreach ($navigationLinks as $link)
                        <a href="{{ $link['href'] }}" class="site-common-footer__link">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </section>

            <section class="site-common-footer__section">
                <div class="site-common-footer__section-header">
                    <h2 class="site-common-footer__section-title">Løsningen</h2>
                </div>

                <div class="site-common-footer__nav">
                    @foreach ($solutionLinks as $link)
                        <a href="{{ $link['href'] }}" class="site-common-footer__link">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </section>

            <section class="site-common-footer__section">
                <div class="site-common-footer__section-header">
                    <h2 class="site-common-footer__section-title">Kan du ikke få nok?</h2>
                </div>

                <div class="marketing-footer__engagement">
                    <div class="marketing-footer__engagement-block marketing-footer__engagement-block--social">
                        <p class="marketing-footer__mini-heading">Følg os</p>

                        <div class="marketing-footer__social-grid">
                            @foreach ($socialPlatforms as $socialPlatform)
                                <div class="marketing-footer__social-item">
                                    <span class="marketing-footer__social-icon" aria-hidden="true">
                                        @include('sites.shared.partials.social-icon', [
                                            'platform' => $socialPlatform['platform'],
                                            'class' => 'marketing-footer__social-svg',
                                        ])
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="marketing-footer__engagement-block marketing-footer__engagement-block--newsletter" id="footer-newsletter">
                        <p class="marketing-footer__mini-heading">Tilmeld dig vores nyhedsbrev</p>
                        <p class="marketing-footer__newsletter-copy">
                            Få nye designs, tips og opdateringer direkte i indbakken.
                        </p>

                        @if (session('newsletter_status'))
                            <p class="ui-status marketing-footer__newsletter-status">{{ session('newsletter_status') }}</p>
                        @endif

                        <form method="POST" action="{{ route('newsletter.store') }}" class="marketing-footer__newsletter-form">
                            @csrf

                            <input
                                id="footer-newsletter-email"
                                name="newsletter_email"
                                type="email"
                                value="{{ old('newsletter_email') }}"
                                class="ui-field__control marketing-footer__newsletter-input"
                                placeholder="din@email.dk"
                                aria-label="Email til nyhedsbrev"
                                autocomplete="email"
                                required
                            >

                            <button type="submit" class="ui-button ui-button--ink marketing-footer__newsletter-button">
                                Tilmeld
                            </button>
                        </form>

                        @if ($newsletterErrors->has('newsletter_email'))
                            <p class="ui-field__error marketing-footer__newsletter-error">
                                {{ $newsletterErrors->first('newsletter_email') }}
                            </p>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="site-common-footer__subbar">
        <div class="ui-shell site-common-footer__subbar-inner">
            <div class="marketing-footer__subbar-meta">
                <span>&copy; {{ now()->year }} PlateDigital</span>
                <a href="{{ route('home') }}" class="site-common-footer__subbar-link">plateweb.dk</a>
                <span>CVR 42456187</span>
                <div class="marketing-footer__payment-list marketing-footer__payment-list--subbar" aria-label="Betalingsmetoder">
                    @foreach ($paymentMethods as $paymentMethod)
                        <span class="marketing-footer__payment-badge marketing-footer__payment-badge--subbar">
                            <img
                                src="{{ asset($paymentMethod['asset']) }}"
                                alt="{{ $paymentMethod['label'] }}"
                                class="marketing-footer__payment-logo {{ $paymentMethod['class'] }}"
                                loading="lazy"
                                decoding="async"
                            >
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>
