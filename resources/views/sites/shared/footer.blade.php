@php
    $footer = $site->footerSettings;
    $tenant = $site->tenant;
    $showContactEmail = $footer ? (bool) ($footer->show_contact_email ?? true) : true;
    $showContactPhone = $footer ? (bool) ($footer->show_contact_phone ?? true) : true;
    $showContactAddress = $footer ? (bool) ($footer->show_contact_address ?? true) : true;
    $showContactCvr = $footer ? (bool) ($footer->show_contact_cvr ?? true) : true;
    $contactEmail = $showContactEmail ? ($footer?->contact_email ?: $tenant?->display_email) : null;
    $contactPhone = $showContactPhone ? ($footer?->contact_phone ?: $tenant?->phone) : null;
    $contactAddress = $showContactAddress ? $footer?->contact_address : null;
    $contactCvr = $showContactCvr ? ($footer?->contact_cvr ?: $tenant?->cvr_number) : null;
    $navigationLinks = collect($footer?->navigation_links ?: [])
        ->map(function ($link) {
            $href = \App\Support\Http\PublicSiteUrl::sanitize($link['href'] ?? null);

            return [
                'label' => trim((string) ($link['label'] ?? '')),
                'href' => $href,
            ];
        })
        ->filter(fn ($link) => filled($link['label'] ?? null) && filled($link['href'] ?? null));
    $informationLinks = collect($footer?->information_links ?: [])
        ->map(function ($link) {
            $href = \App\Support\Http\PublicSiteUrl::sanitize($link['href'] ?? null);

            return [
                'label' => trim((string) ($link['label'] ?? '')),
                'href' => $href,
            ];
        })
        ->filter(fn ($link) => filled($link['label'] ?? null) && filled($link['href'] ?? null));
    $socialLinks = collect(\App\Support\Sites\SiteFooterSocialPlatforms::normalize($footer?->social_links ?: []))
        ->map(function ($link) {
            $link['href'] = \App\Support\Http\PublicSiteUrl::sanitize($link['href'] ?? null);

            return $link;
        })
        ->filter(fn ($link) => (bool) ($link['enabled'] ?? false) && filled($link['href'] ?? null));

    if ($navigationLinks->isEmpty()) {
        $navigationLinks = $navigation->take(8)->map(function ($navPage) use ($site) {
            return [
                'label' => $navPage->name,
                'href' => $navPage->preview_url
                    ?? ($navPage->is_home
                        ? ($site->preview_home_url ?? route('sites.show', $site))
                        : route('sites.page', [$site, $navPage->slug])),
            ];
        });
    }
@endphp

<footer class="site-common-footer">
    <div class="site-common-footer__surface">
        <div class="ui-shell site-common-footer__inner">
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
                    <h2 class="site-common-footer__section-title">Information</h2>
                </div>

                <div class="site-common-footer__nav">
                    @foreach ($informationLinks as $link)
                        <a href="{{ $link['href'] }}" class="site-common-footer__link">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </section>

            <section class="site-common-footer__section">
                <div class="site-common-footer__section-header">
                    <h2 class="site-common-footer__section-title">Kontakt</h2>
                </div>

                <div class="site-common-footer__contact-list">
                    @if ($contactEmail)
                        <div class="site-common-footer__contact-item">
                            <span class="site-common-footer__contact-label">
                                <span class="site-common-footer__contact-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 6h16v12H4z" />
                                        <path d="m4 7 8 6 8-6" />
                                    </svg>
                                </span>
                                <span>E-mail</span>
                            </span>
                            <a href="mailto:{{ $contactEmail }}" class="site-common-footer__link">{{ $contactEmail }}</a>
                        </div>
                    @endif

                    @if ($contactPhone)
                        <div class="site-common-footer__contact-item">
                            <span class="site-common-footer__contact-label">
                                <span class="site-common-footer__contact-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.34 2.71a2 2 0 0 1-.57 1.71l-1.2 1.2a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 1.71-.57l2.71.34A2 2 0 0 1 22 16.92z" />
                                    </svg>
                                </span>
                                <span>Telefon</span>
                            </span>
                            <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="site-common-footer__link">{{ $contactPhone }}</a>
                        </div>
                    @endif

                    @if ($contactAddress)
                        <div class="site-common-footer__contact-item">
                            <span class="site-common-footer__contact-label">
                                <span class="site-common-footer__contact-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 21s-6-5.33-6-11a6 6 0 1 1 12 0c0 5.67-6 11-6 11Z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>
                                </span>
                                <span>Adresse</span>
                            </span>
                            <p class="site-common-footer__meta">{!! nl2br(e($contactAddress)) !!}</p>
                        </div>
                    @endif

                    @if ($contactCvr)
                        <div class="site-common-footer__contact-item">
                            <span class="site-common-footer__contact-label">
                                <span class="site-common-footer__contact-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="4" y="4" width="16" height="16" rx="2" />
                                        <path d="M9 9h6M9 12h6M9 15h4" />
                                    </svg>
                                </span>
                                <span>CVR</span>
                            </span>
                            <p class="site-common-footer__meta">{{ $contactCvr }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="site-common-footer__section">
                <div class="site-common-footer__section-header">
                    <h2 class="site-common-footer__section-title">Følg os</h2>
                </div>

                <div class="site-common-footer__socials">
                    @foreach ($socialLinks as $platform => $link)
                        @php($platformDefinition = \App\Support\Sites\SiteFooterSocialPlatforms::definition($platform))

                        <a
                            href="{{ $link['href'] }}"
                            class="site-common-footer__social-link"
                            aria-label="{{ $platformDefinition['label'] }}"
                            title="{{ $platformDefinition['label'] }}"
                            target="_blank"
                            rel="noreferrer"
                        >
                            @include('sites.shared.partials.social-icon', ['platform' => $platform])
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    <div class="site-common-footer__subbar">
        <div class="ui-shell site-common-footer__subbar-inner">
            <span>&copy; Alle rettigheder reserveret</span>
            <a href="https://plateweb.dk" class="site-common-footer__subbar-link" target="_blank" rel="noreferrer">PlateWeb.dk</a>
            <span>CVR: 88888888</span>
        </div>
    </div>
</footer>
