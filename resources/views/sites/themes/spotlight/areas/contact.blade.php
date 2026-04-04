@php
    $data = $area->data ?? [];
    $showPhone = ($data['show_phone'] ?? '1') !== '0';
    $ctaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['cta_href'] ?? null);
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--compact">
    <div class="ui-shell">
        <article class="spotlight-contact">
            <div class="spotlight-contact__lead">
                @if (! empty($data['eyebrow']))
                    <p class="spotlight-kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="spotlight-section-title">{{ $data['title'] ?? 'Kontakt' }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            <div class="spotlight-contact__actions">
                @if (! empty($data['email']))
                    <a href="mailto:{{ $data['email'] }}" class="spotlight-contact__link">{{ $data['email'] }}</a>
                @endif

                @if ($showPhone && ! empty($data['phone']))
                    <a href="tel:{{ preg_replace('/\\s+/', '', $data['phone']) }}" class="spotlight-contact__link">{{ $data['phone'] }}</a>
                @endif

                @if (! empty($data['cta_label']) && $ctaHref)
                    <a href="{{ $ctaHref }}" class="ui-button ui-button--accent">
                        {{ $data['cta_label'] }}
                    </a>
                @endif
            </div>
        </article>
    </div>
</section>
