@php($colorErrors = $errors->getBag('updateSiteColors'))
@php($selectedPaletteKey = old('palette_key', $site->colorSettings?->palette_key ?? \App\Support\Sites\SiteColorPalettes::defaultKey()))
@php($selectedPalette = ($availableColorPalettes ?? [])[$selectedPaletteKey] ?? \App\Support\Sites\SiteColorPalettes::definition($selectedPaletteKey))
@php($selectedColors = $selectedPalette['colors'])
@php($selectedTheme = ($availableThemes ?? [])[$site->theme] ?? null)
@php($readability = \App\Support\Sites\SiteColorPalettes::readabilitySummary($selectedPaletteKey))
@php($usageAreas = \App\Support\Sites\SiteColorPalettes::usageAreas())
@php($defaultPaletteKey = \App\Support\Sites\SiteColorPalettes::defaultKey())

<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <h3 class="site-dashboard-panel__title">Fælles visuelle valg</h3>
            <p class="site-dashboard-panel__copy">Vælg en fast farvepalette for hele websitet. Themes bruger derefter farverne automatisk i knapper, containere og fremhævede sektioner.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    @if ($colorErrors->any())
        <div class="site-page-form-card__errors site-page-form-card__errors--inline">
            <p class="ui-copy">Der er lige et par farvefelter vi skal have rettet:</p>
            <ul class="ui-list">
                @foreach ($colorErrors->all() as $error)
                    <li class="ui-list__item">
                        <span class="ui-list__dot"></span>
                        <span>{{ $error }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="site-color-workspace">
        <div
            class="site-color-overview"
            style="
                --site-color-preview-primary: {{ $selectedColors['primary'] }};
                --site-color-preview-accent: {{ $selectedColors['accent'] }};
                --site-color-preview-header: {{ $selectedColors['header_ink'] }};
                --site-color-preview-surface: {{ $selectedColors['surface_alt'] }};
                --site-color-preview-ink: {{ $selectedColors['ink'] }};
                --site-color-preview-border: {{ $selectedColors['border'] }};
            "
        >
            <article class="site-color-preview-card">
                <div class="site-color-preview-card__frame">
                    <div class="site-color-preview-card__header">
                        <span class="site-color-preview-card__brand-dot"></span>
                        <span class="site-color-preview-card__brand-line"></span>
                        <span class="site-color-preview-card__button"></span>
                    </div>

                    <div class="site-color-preview-card__hero">
                        <span class="site-color-preview-card__eyebrow"></span>
                        <span class="site-color-preview-card__title"></span>
                        <span class="site-color-preview-card__copy"></span>
                        <span class="site-color-preview-card__copy site-color-preview-card__copy--short"></span>
                    </div>

                    <div class="site-color-preview-card__surface-row">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </article>

            <div class="site-color-overview__content">
                <div class="site-color-overview__intro">
                    <span class="site-dashboard-panel__detail-label">Aktivt farvevalg</span>
                    <h4 class="site-color-overview__title">{{ $selectedPalette['label'] }}</h4>
                    <p class="site-color-overview__copy">{{ $selectedPalette['description'] }}</p>
                </div>

                <div class="site-color-overview__meta">
                    <div class="site-dashboard-panel__detail">
                        <span class="site-dashboard-panel__detail-label">Aktivt theme</span>
                        <strong>{{ $selectedTheme['label'] ?? $site->theme }}</strong>
                        <p class="site-color-overview__detail-copy">{{ $selectedTheme['vibe'] ?? 'Farverne tilpasser sig det valgte theme automatisk.' }}</p>
                    </div>

                    <div class="site-dashboard-panel__detail">
                        <span class="site-dashboard-panel__detail-label">Kontrast og læsbarhed</span>
                        <strong>{{ $readability['label'] }} · {{ $readability['ratio'] }}</strong>
                        <p class="site-color-overview__detail-copy">{{ $readability['copy'] }}</p>
                    </div>

                    <div class="site-dashboard-panel__detail">
                        <span class="site-dashboard-panel__detail-label">Anbefalet til</span>
                        <strong>{{ implode(' · ', $selectedPalette['recommended_for']) }}</strong>
                        <p class="site-color-overview__detail-copy">{{ $selectedPalette['vibe'] }}</p>
                    </div>
                </div>

                <div class="site-color-overview__usage">
                    <span class="site-dashboard-panel__detail-label">Hvor farverne bruges</span>

                    <div class="site-color-overview__chips">
                        @foreach ($usageAreas as $usageArea)
                            <span class="site-color-chip">{{ $usageArea }}</span>
                        @endforeach
                    </div>

                    <p class="site-color-overview__usage-copy">
                        Themeet bruger palettevalget automatisk, så du slipper for at ændre containere og knapper én for én.
                    </p>
                </div>
            </div>
        </div>

        <aside class="site-color-sidebar">
            <div class="site-color-sidebar__header">
                <span class="site-dashboard-panel__detail-label">Paletter</span>
                <h4 class="site-color-sidebar__title">Vælg nyt udtryk</h4>
            </div>

            <form method="POST" action="{{ route('cms.sites.colors.update', $site) }}" class="site-global-form site-color-sidebar__form" id="site-colors-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                <fieldset @disabled(! $canUpdateSite)>
                    <div class="site-color-choice-grid">
                        @foreach (($availableColorPalettes ?? []) as $paletteKey => $definition)
                            @php($colors = $definition['colors'])
                            <label
                                class="site-color-choice{{ $selectedPaletteKey === $paletteKey ? ' site-color-choice--active' : '' }}"
                                style="
                                    --site-color-preview-primary: {{ $colors['primary'] }};
                                    --site-color-preview-accent: {{ $colors['accent'] }};
                                    --site-color-preview-header: {{ $colors['header_ink'] }};
                                    --site-color-preview-surface: {{ $colors['surface_alt'] }};
                                    --site-color-preview-ink: {{ $colors['ink'] }};
                                    --site-color-preview-border: {{ $colors['border'] }};
                                "
                            >
                                <input type="radio" name="palette_key" value="{{ $paletteKey }}" {{ $selectedPaletteKey === $paletteKey ? 'checked' : '' }}>

                                <span class="site-color-choice__body">
                                    <strong>{{ $definition['label'] }}</strong>
                                    <small>{{ $definition['description'] }}</small>

                                    <span class="site-color-choice__footer">
                                        <span class="site-color-choice__meta">{{ $definition['vibe'] }}</span>

                                        <span class="site-color-choice__swatches">
                                            <span style="background: {{ $colors['primary'] }};"></span>
                                            <span style="background: {{ $colors['accent'] }};"></span>
                                            <span style="background: {{ $colors['header_ink'] }};"></span>
                                            <span style="background: {{ $colors['surface_alt'] }};"></span>
                                        </span>
                                    </span>

                                    <span class="site-color-choice__recommendation">God til: {{ implode(' · ', $definition['recommended_for']) }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </form>

            <div class="site-dashboard-panel__actions site-dashboard-panel__actions--between site-dashboard-panel__actions--compact">
                @if ($canUpdateSite && $selectedPaletteKey !== $defaultPaletteKey)
                    <form method="POST" action="{{ route('cms.sites.colors.update', $site) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <input type="hidden" name="palette_key" value="{{ $defaultPaletteKey }}">
                        <button type="submit" class="ui-button ui-button--outline">Nulstil til standard</button>
                    </form>
                @else
                    <div></div>
                @endif

                @if ($canUpdateSite)
                    <button type="submit" form="site-colors-form" class="ui-button ui-button--ink">Gem farvevalg</button>
                @else
                    <p class="ui-copy">Denne tenant-rolle giver kun læseadgang til farvevalget.</p>
                @endif
            </div>
        </aside>
    </div>
</section>
