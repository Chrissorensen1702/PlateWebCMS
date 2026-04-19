@php
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

    $contactLinks = [
        ['label' => 'Kontaktformular', 'href' => route('contact')],
        ['label' => 'Prøv 30 dage gratis', 'href' => route('contact')],
        ['label' => 'Kundelogin', 'href' => route('login')],
    ];
@endphp

<footer class="site-common-footer marketing-footer">
    <div class="site-common-footer__surface">
        <div class="ui-shell site-common-footer__inner">
            <section class="site-common-footer__section site-common-footer__section--brand">
                <div class="site-common-footer__section-header">
                    <h2 class="site-common-footer__section-title">PlateWeb</h2>
                </div>

                <div class="site-common-footer__brand-block">
                    <img
                        src="{{ asset('images/logo/plateweb-sales.svg') }}"
                        alt="PlateWeb"
                        class="site-common-footer__logo"
                    >

                    <div class="site-common-footer__brand-copy">
                        <p class="site-common-footer__contact-heading">Kontaktoplysninger:</p>
                        <p class="site-common-footer__lead">
                            PlateWeb ApS<br>
                            CVR 88888888<br>
                            +45 20 63 12 99<br>
                            kontakt@plateweb.dk
                        </p>
                    </div>
                </div>

                <div class="site-common-footer__action">
                    <a href="{{ route('contact') }}" class="site-common-footer__cta">
                        Se kontaktformular
                    </a>
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
                    <h2 class="site-common-footer__section-title">Kom videre</h2>
                </div>

                <div class="site-common-footer__contact-list">
                    @foreach ($contactLinks as $link)
                        <div class="site-common-footer__contact-item">
                            <span class="site-common-footer__contact-label">{{ $loop->iteration < 10 ? '0'.$loop->iteration : $loop->iteration }}</span>
                            <a href="{{ $link['href'] }}" class="site-common-footer__link">{{ $link['label'] }}</a>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    <div class="site-common-footer__subbar">
        <div class="ui-shell site-common-footer__subbar-inner">
            <span>&copy; {{ now()->year }} PlateWeb</span>
            <a href="{{ route('home') }}" class="site-common-footer__subbar-link">plateweb.dk</a>
            <span>CVR 88888888</span>
        </div>
    </div>
</footer>
