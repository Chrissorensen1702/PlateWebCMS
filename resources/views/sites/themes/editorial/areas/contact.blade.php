@php
    $data = $area->data ?? [];
    $layoutStyle = $data['layout_style'] ?? 'split';
    $sectionTone = $data['section_tone'] ?? 'default';
    $showPhone = ($data['show_phone'] ?? '1') !== '0';
    $ctaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['cta_href'] ?? null);
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--compact">
    <div class="ui-shell">
        <article class="editorial-contact editorial-contact--{{ $layoutStyle }}{{ $sectionTone === 'accent' ? ' editorial-contact--accent' : '' }}">
            <div class="editorial-contact__lead">
                @if (! empty($data['eyebrow']))
                    <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="ui-title">{{ $data['title'] ?? 'Kontakt' }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            <div class="editorial-contact__card">
                <p class="editorial-contact__label">Booking & kontakt</p>

                <div class="editorial-contact__actions">
                    @if (! empty($data['email']))
                        <a href="mailto:{{ $data['email'] }}" class="editorial-contact__link">{{ $data['email'] }}</a>
                    @endif

                    @if ($showPhone && ! empty($data['phone']))
                        <a href="tel:{{ preg_replace('/\\s+/', '', $data['phone']) }}" class="editorial-contact__link">{{ $data['phone'] }}</a>
                    @endif

                    @if (! empty($data['cta_label']) && $ctaHref)
                        <a href="{{ $ctaHref }}" class="ui-button ui-button--accent editorial-contact__button">
                            {{ $data['cta_label'] }}
                        </a>
                    @endif
                </div>

                <p class="editorial-contact__note">
                    Brug sektionen til booking, aabningstider eller en rolig invitation til at tage kontakt.
                </p>
            </div>
        </article>
    </div>
</section>
