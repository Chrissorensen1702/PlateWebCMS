@php
    $data = $area->data ?? [];
    $sectionTone = $data['section_tone'] ?? 'default';
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
    $mapEmbedRaw = trim((string) ($data['map_embed_url'] ?? ''));

    if ($mapEmbedRaw !== '' && preg_match('/src=[\"\']([^\"\']+)[\"\']/i', $mapEmbedRaw, $matches) === 1) {
        $mapEmbedRaw = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }

    $mapEmbedUrl = \Illuminate\Support\Str::startsWith($mapEmbedRaw, [
        'https://www.google.com/maps',
        'https://maps.google.com/maps',
        'https://www.google.dk/maps',
    ]) ? $mapEmbedRaw : null;

    $contactTitle = $data['title'] ?? 'Her finder du os';
@endphp

<section class="site-section site-section--compact">
    <div class="ui-shell site-section__shell site-section__shell--wide">
        <article class="site-contact site-contact--split{{ $sectionTone === 'accent' ? ' site-contact--accent' : '' }}">
            <div class="site-contact__content">
                @if (! empty($data['eyebrow']))
                    <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="ui-title">{{ $contactTitle }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif

                @if ($ctaLabel && $ctaHref)
                    <a href="{{ $ctaHref }}" class="ui-button ui-button--light" @if($ctaTarget) target="{{ $ctaTarget }}" rel="noreferrer" @endif>
                        {{ $ctaLabel }}
                    </a>
                @endif
            </div>

            <div class="site-contact__map">
                @if ($mapEmbedUrl)
                    <iframe
                        src="{{ $mapEmbedUrl }}"
                        class="site-contact__map-frame"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                @else
                    <div class="site-contact__map-placeholder">
                        Tilføj et Google Maps embed-link i designeren for at vise kortet her.
                    </div>
                @endif
            </div>
        </article>
    </div>
</section>
