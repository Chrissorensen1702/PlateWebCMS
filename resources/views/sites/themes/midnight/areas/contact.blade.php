@php
    $data = $area->data ?? [];
    $layoutStyle = $data['layout_style'] ?? 'split';
    $sectionTone = $data['section_tone'] ?? 'default';
    $showPhone = ($data['show_phone'] ?? '1') !== '0';
    $ctaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['cta_href'] ?? null);
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--compact">
    <div class="ui-shell">
        <article class="midnight-contact midnight-contact--{{ $layoutStyle }}{{ $sectionTone === 'accent' ? ' midnight-contact--accent' : '' }}">
            <div class="midnight-contact__main">
                @if (! empty($data['eyebrow']))
                    <p class="midnight-kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="midnight-section-title">{{ $data['title'] ?? 'Kontakt' }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            <aside class="midnight-contact__aside">
                <p class="midnight-contact__label">Direkte linje</p>

                <div class="midnight-contact__card">
                    @if (! empty($data['email']))
                        <a href="mailto:{{ $data['email'] }}" class="midnight-contact__link">{{ $data['email'] }}</a>
                    @endif

                    @if ($showPhone && ! empty($data['phone']))
                        <a href="tel:{{ preg_replace('/\\s+/', '', $data['phone']) }}" class="midnight-contact__link">{{ $data['phone'] }}</a>
                    @endif

                    @if (! empty($data['cta_label']) && $ctaHref)
                        <a href="{{ $ctaHref }}" class="ui-button ui-button--accent">
                            {{ $data['cta_label'] }}
                        </a>
                    @endif
                </div>

                <p class="midnight-contact__note">Brug sektionen til direkte kontakt, næste skridt eller en hurtig bookingvej.</p>
            </aside>
        </article>
    </div>
</section>
