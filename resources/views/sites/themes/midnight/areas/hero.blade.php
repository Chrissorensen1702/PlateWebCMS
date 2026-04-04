@php
    $data = $area->data ?? [];
    $headingSize = $data['heading_size'] ?? 'large';
    $textAlign = $data['text_align'] ?? 'left';
    $buttonAlign = $data['button_align'] ?? ($textAlign === 'center' ? 'center' : 'left');
    $showSecondaryCta = ($data['secondary_cta_mode'] ?? 'show') !== 'hide';
    $primaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['primary_cta_href'] ?? null);
    $secondaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['secondary_cta_href'] ?? null);
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--hero">
    <div class="ui-shell">
        <article class="midnight-hero midnight-hero--{{ $headingSize }}{{ $textAlign === 'center' ? ' midnight-hero--centered' : '' }}">
            <div class="midnight-hero__grid">
                <div class="midnight-hero__content">
                    @if (! empty($data['eyebrow']))
                        <p class="midnight-kicker">{{ $data['eyebrow'] }}</p>
                    @endif

                    <h1 class="midnight-hero__title">{{ $data['title'] ?? $page->title }}</h1>

                    @if (! empty($data['copy']))
                        <p class="midnight-hero__copy">{{ $data['copy'] }}</p>
                    @endif
                </div>

                <aside class="midnight-hero__rail">
                    <div class="midnight-hero__signal">
                        <span class="midnight-hero__signal-label">Næste træk</span>
                        <p class="midnight-hero__signal-copy">Giv besøgende en tydelig vej videre med et stærkt næste skridt.</p>
                    </div>

                    <div class="midnight-hero__actions midnight-hero__actions--{{ $buttonAlign }}">
                        @if (! empty($data['primary_cta_label']) && $primaryCtaHref)
                            <a href="{{ $primaryCtaHref }}" class="ui-button ui-button--accent">
                                {{ $data['primary_cta_label'] }}
                            </a>
                        @endif

                        @if ($showSecondaryCta && ! empty($data['secondary_cta_label']) && $secondaryCtaHref)
                            <a href="{{ $secondaryCtaHref }}" class="ui-button ui-button--outline">
                                {{ $data['secondary_cta_label'] }}
                            </a>
                        @endif
                    </div>
                </aside>
            </div>
        </article>
    </div>
</section>
