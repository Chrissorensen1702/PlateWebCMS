@php
    $data = $area->data ?? [];
    $showSecondaryCta = ($data['secondary_cta_mode'] ?? 'show') !== 'hide';
    $primaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['primary_cta_href'] ?? null);
    $secondaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['secondary_cta_href'] ?? null);
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--hero">
    <div class="ui-shell">
        <article class="spotlight-hero">
            <div class="spotlight-hero__content">
                @if (! empty($data['eyebrow']))
                    <p class="spotlight-kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h1 class="spotlight-hero__title">{{ $data['title'] ?? $page->title }}</h1>

                @if (! empty($data['copy']))
                    <p class="spotlight-hero__copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            <div class="spotlight-hero__actions">
                @if (! empty($data['primary_cta_label']) && $primaryCtaHref)
                    <a href="{{ $primaryCtaHref }}" class="ui-button ui-button--accent">
                        {{ $data['primary_cta_label'] }}
                    </a>
                @endif

                @if ($showSecondaryCta && ! empty($data['secondary_cta_label']) && $secondaryCtaHref)
                    <a href="{{ $secondaryCtaHref }}" class="ui-button ui-button--light-outline">
                        {{ $data['secondary_cta_label'] }}
                    </a>
                @endif
            </div>
        </article>
    </div>
</section>
