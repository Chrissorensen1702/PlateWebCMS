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
        ? null
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

<section class="site-section site-section--hero">
    <article class="ui-card ui-card--dark site-hero site-hero--{{ $headingSize }}{{ $textAlign === 'center' ? ' site-hero--centered' : '' }}{{ $imageUrl ? ' site-hero--with-media' : '' }}">
        <div class="ui-shell site-hero__inner">
            <div class="site-hero__content">
                @if (! empty($data['eyebrow']))
                    <p class="ui-kicker ui-kicker--light">{{ $data['eyebrow'] }}</p>
                @endif

                <h1 class="ui-title{{ $headingSize === 'large' ? ' ui-title--display' : '' }}">{{ $data['title'] ?? $page->title }}</h1>

                @if (! empty($data['copy']))
                    <p class="site-hero__copy">{{ $data['copy'] }}</p>
                @endif

                <div class="site-hero__actions site-hero__actions--{{ $buttonAlign }}">
                    @if (! empty($data['primary_cta_label']) && $primaryCtaHref)
                        <a href="{{ $primaryCtaHref }}" class="ui-button ui-button--light">
                            {{ $data['primary_cta_label'] }}
                        </a>
                    @endif

                    @if ($showSecondaryCta && ! empty($data['secondary_cta_label']) && $secondaryCtaHref)
                        <a href="{{ $secondaryCtaHref }}" class="ui-button ui-button--light-outline">
                            {{ $data['secondary_cta_label'] }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($imageUrl)
                <figure class="site-hero__media">
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $imageAlt !== '' ? $imageAlt : ($data['title'] ?? $page->title) }}"
                        class="site-hero__image"
                        style="object-position: {{ $imageFocus }};"
                    >
                </figure>
            @endif
        </div>
    </article>
</section>
