@php
    $data = $area->data ?? [];
    $showPhone = ($data['show_phone'] ?? '1') !== '0';
    $localCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['cta_href'] ?? null);
    $localCtaLabel = trim((string) ($data['cta_label'] ?? ''));
    $usesLocalCta = $localCtaLabel !== '' && $localCtaHref !== null;
    $bookingFallbackEnabled = $site->usesBookingInContactSections();
    $ctaHref = $usesLocalCta
        ? $localCtaHref
        : ($bookingFallbackEnabled ? $site->resolvedBookingUrl() : null);
    $ctaLabel = $usesLocalCta
        ? $localCtaLabel
        : ($bookingFallbackEnabled ? $site->resolvedBookingCtaLabel('Book tid') : null);
    $ctaTarget = $bookingFallbackEnabled && ! $usesLocalCta && $site->bookingShouldOpenInNewTab()
        ? '_blank'
        : null;
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--compact">
    <div class="ui-shell">
        <article class="minimal-contact">
            <div class="minimal-contact__lead">
                @if (! empty($data['eyebrow']))
                    <p class="minimal-kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="minimal-section-title">{{ $data['title'] ?? 'Kontakt' }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            <div class="minimal-contact__card">
                @if (! empty($data['email']))
                    <a href="mailto:{{ $data['email'] }}" class="minimal-contact__link">{{ $data['email'] }}</a>
                @endif

                @if ($showPhone && ! empty($data['phone']))
                    <a href="tel:{{ preg_replace('/\\s+/', '', $data['phone']) }}" class="minimal-contact__link">{{ $data['phone'] }}</a>
                @endif

                @if ($ctaLabel && $ctaHref)
                    <a href="{{ $ctaHref }}" class="ui-button ui-button--ink" @if($ctaTarget) target="{{ $ctaTarget }}" rel="noreferrer" @endif>
                        {{ $ctaLabel }}
                    </a>
                @endif
            </div>
        </article>
    </div>
</section>
