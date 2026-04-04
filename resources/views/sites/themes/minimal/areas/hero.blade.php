@php
    $data = $area->data ?? [];
    $headingSize = $data['heading_size'] ?? 'large';
    $showSecondaryCta = ($data['secondary_cta_mode'] ?? 'show') !== 'hide';
    $primaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['primary_cta_href'] ?? null);
    $secondaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['secondary_cta_href'] ?? null);
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--hero">
    <div class="ui-shell">
        <article class="minimal-hero minimal-hero--{{ $headingSize }}">
            @if (! empty($data['eyebrow']))
                <p class="minimal-kicker">{{ $data['eyebrow'] }}</p>
            @endif

            <h1 class="minimal-hero__title">{{ $data['title'] ?? $page->title }}</h1>

            @if (! empty($data['copy']))
                <p class="minimal-hero__copy">{{ $data['copy'] }}</p>
            @endif

            <div class="minimal-hero__actions">
                @if (! empty($data['primary_cta_label']) && $primaryCtaHref)
                    <a href="{{ $primaryCtaHref }}" class="ui-button ui-button--ink">
                        {{ $data['primary_cta_label'] }}
                    </a>
                @endif

                @if ($showSecondaryCta && ! empty($data['secondary_cta_label']) && $secondaryCtaHref)
                    <a href="{{ $secondaryCtaHref }}" class="ui-button ui-button--outline">
                        {{ $data['secondary_cta_label'] }}
                    </a>
                @endif
            </div>
        </article>
    </div>
</section>
