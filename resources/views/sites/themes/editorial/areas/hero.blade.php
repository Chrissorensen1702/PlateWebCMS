@php
    $data = $area->data ?? [];
    $headingSize = $data['heading_size'] ?? 'large';
    $textAlign = $data['text_align'] ?? 'left';
    $buttonAlign = $data['button_align'] ?? ($textAlign === 'center' ? 'center' : 'left');
    $showSecondaryCta = ($data['secondary_cta_mode'] ?? 'show') !== 'hide';
    $primaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['primary_cta_href'] ?? null);
    $secondaryCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($data['secondary_cta_href'] ?? null);
    $imagePath = trim((string) ($data['image_url'] ?? ''));
    $imageUrl = $imagePath === ''
        ? asset('images/demo/maison-glow-hero.svg')
        : (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://', '//', '/'])
            ? $imagePath
            : \Illuminate\Support\Facades\Storage::disk(config('filesystems.site_media_disk', 'public'))->url($imagePath));
    $imageAlt = trim((string) ($data['image_alt'] ?? ''));
    $imageFocus = match ($data['image_focus'] ?? 'center') {
        'left' => 'left center',
        'right' => 'right center',
        'top' => 'center top',
        'bottom' => 'center bottom',
        default => 'center center',
    };
@endphp

<section id="{{ $area->area_key }}" class="site-section site-section--hero">
    <div class="ui-shell">
        <article class="ui-card editorial-hero editorial-hero--{{ $headingSize }}{{ $textAlign === 'center' ? ' editorial-hero--centered' : '' }}">
            <div class="editorial-hero__grid">
                <div class="editorial-hero__content">
                    @if (! empty($data['eyebrow']))
                        <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
                    @endif

                    <h1 class="ui-title{{ $headingSize === 'large' ? ' ui-title--display' : '' }}">{{ $data['title'] ?? $page->title }}</h1>

                    @if (! empty($data['copy']))
                        <p class="editorial-hero__copy">{{ $data['copy'] }}</p>
                    @endif

                    <div class="editorial-hero__actions editorial-hero__actions--{{ $buttonAlign }}">
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
                </div>

                <figure class="editorial-hero__media">
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $imageAlt !== '' ? $imageAlt : ($data['title'] ?? $page->title) }}"
                        class="editorial-hero__image"
                        style="object-position: {{ $imageFocus }};"
                    >
                </figure>
            </div>
        </article>
    </div>
</section>
