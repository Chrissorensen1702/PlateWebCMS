@php($headerErrors = $errors->getBag('updateSiteHeader'))
@php($headerSettings = $site->headerSettings)
@php($headerBackgroundStyle = old('background_style', \App\Models\SiteHeaderSetting::normalizeBackgroundStyle($headerSettings?->background_style)))
@php($headerTextColorStyle = old('text_color_style', \App\Models\SiteHeaderSetting::normalizeTextColorStyle($headerSettings?->text_color_style)))
@php($headerShadowStyle = old('shadow_style', \App\Models\SiteHeaderSetting::normalizeShadowStyle($headerSettings?->shadow_style)))
@php($headerStickyMode = old('sticky_mode', \App\Models\SiteHeaderSetting::normalizeStickyMode($headerSettings?->sticky_mode)))

<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Header</p>
            <h3 class="site-dashboard-panel__title">Website-header</h3>
            <p class="site-dashboard-panel__copy">Her styrer du logo, brandnavn, tagline og den globale CTA i toppen af websitet.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    <section class="site-global-module-card">
        <form
            method="POST"
            action="{{ route('cms.sites.header.update', $site) }}"
            class="site-global-form"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

            @if ($headerErrors->any())
                <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                    <p class="ui-copy">Der er lige et par header-felter vi skal have rettet:</p>
                    <ul class="ui-list">
                        @foreach ($headerErrors->all() as $error)
                            <li class="ui-list__item">
                                <span class="ui-list__dot"></span>
                                <span>{{ $error }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <fieldset @disabled(! $canUpdateSite)>
                <div class="site-global-module-sections site-global-module-sections--header">
                    <section class="site-global-module-section site-global-module-section--header-brand">
                        <div class="site-global-module-section__header">
                            <p class="site-global-module-section__eyebrow">Brand</p>
                            <h5 class="site-global-module-section__title">Logo og identitet</h5>
                        </div>

                        <div class="site-global-form__grid">
                            <div class="ui-field site-inline-toggle-field">
                                <div class="site-inline-toggle-field__header">
                                    <span class="ui-field__label ui-field__label--with-help">
                                        Brandnavn
                                        <x-help-tooltip text="Det navn der vises i website-headeren, hvis du vil bruge noget andet end sitets interne navn." />
                                    </span>

                                    <div class="site-inline-toggle-field__toggle">
                                        <input type="hidden" name="show_brand_name" value="0">
                                        <label class="site-page-form-card__checkbox site-page-form-card__checkbox--inline">
                                            <input type="checkbox" name="show_brand_name" value="1" {{ old('show_brand_name', $headerSettings?->show_brand_name ?? true) ? 'checked' : '' }}>
                                            <span>Vis brandnavn</span>
                                        </label>
                                    </div>
                                </div>

                                <input type="text" name="brand_name" value="{{ old('brand_name', $headerSettings?->brand_name) }}" class="ui-field__control">
                            </div>

                            <div class="ui-field site-inline-toggle-field">
                                <div class="site-inline-toggle-field__header">
                                    <span class="ui-field__label ui-field__label--with-help">
                                        Tagline
                                        <x-help-tooltip text="Den lille undertitel under eller ved siden af brandnavnet i headeren." />
                                    </span>

                                    <div class="site-inline-toggle-field__toggle">
                                        <input type="hidden" name="show_tagline" value="0">
                                        <label class="site-page-form-card__checkbox site-page-form-card__checkbox--inline">
                                            <input type="checkbox" name="show_tagline" value="1" {{ old('show_tagline', $headerSettings?->show_tagline ?? true) ? 'checked' : '' }}>
                                            <span>Vis tagline</span>
                                        </label>
                                    </div>
                                </div>

                                <input type="text" name="tagline" value="{{ old('tagline', $headerSettings?->tagline) }}" class="ui-field__control">
                            </div>

                            <label class="ui-field site-page-form-card__field--full">
                                <span class="ui-field__label ui-field__label--with-help">
                                    Logo
                                    <x-help-tooltip text="Upload et logo til headeren. Hvis der ikke er et logo, bruger websitet brandnavnet som fallback." />
                                </span>
                                <input type="file" name="logo_upload" accept="image/*,.svg" class="ui-field__control">
                            </label>

                            @if ($headerSettings?->logo_url)
                                <div class="site-global-form__logo-preview site-page-form-card__field--full">
                                            <img src="{{ $headerSettings->logo_url }}" alt="{{ $headerSettings->logo_alt ?: ($headerSettings->brand_name ?: $site->name) }}">
                                    <div class="site-page-form-card__toggles">
                                        <input type="hidden" name="remove_logo" value="0">
                                        <label class="site-page-form-card__checkbox">
                                            <input type="checkbox" name="remove_logo" value="1">
                                            <span>Fjern nuværende logo</span>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <label class="ui-field">
                                <span class="ui-field__label ui-field__label--with-help">
                                    Logo-beskrivelse
                                    <x-help-tooltip text="Bruges som alternativ tekst til logoet for tilgængelighed og SEO." />
                                </span>
                                <input type="text" name="logo_alt" value="{{ old('logo_alt', $headerSettings?->logo_alt) }}" class="ui-field__control">
                            </label>
                        </div>
                    </section>

                    <section class="site-global-module-section site-global-module-section--header-appearance">
                        <div class="site-global-module-section__header">
                            <p class="site-global-module-section__eyebrow">Udtryk</p>
                            <h5 class="site-global-module-section__title">Headerens stil på tværs af themes</h5>
                            <p class="site-global-module-section__copy">Tilpas din header efter egne ønsker</p>
                        </div>

                        <div class="site-global-form__grid site-global-form__grid--header-appearance">
                            <div class="ui-field" x-data="{ backgroundStyle: @js($headerBackgroundStyle) }">
                                <span class="ui-field__label ui-field__label--with-help">
                                    Baggrund
                                    <x-help-tooltip text="Vælg om headeren skal bruge tema-standard, en lys eller mørk baggrund, eller være helt transparent." />
                                </span>
                                <div class="site-header-background-select">
                                    <select name="background_style" class="ui-field__control site-select-control" x-model="backgroundStyle">
                                        @foreach (\App\Models\SiteHeaderSetting::backgroundOptions() as $value => $label)
                                            <option value="{{ $value }}" @selected($headerBackgroundStyle === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <span
                                        class="site-header-background-indicator site-header-background-indicator--{{ $headerBackgroundStyle }}"
                                        aria-hidden="true"
                                        x-bind:class="{
                                            'site-header-background-indicator--auto': backgroundStyle === 'auto',
                                            'site-header-background-indicator--light': backgroundStyle === 'light',
                                            'site-header-background-indicator--dark': backgroundStyle === 'dark',
                                            'site-header-background-indicator--transparent': backgroundStyle === 'transparent',
                                        }"
                                    ></span>
                                </div>
                            </div>

                            <label class="ui-field">
                                <span class="ui-field__label ui-field__label--with-help">
                                    Tekstfarve
                                    <x-help-tooltip text="Vælg om headerens tekst og navigation skal følge temaet, eller tvinges til mørk eller lys tekst." />
                                </span>
                                <select name="text_color_style" class="ui-field__control site-select-control">
                                    @foreach (\App\Models\SiteHeaderSetting::textColorOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected($headerTextColorStyle === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="ui-field">
                                <span class="ui-field__label ui-field__label--with-help">
                                    Skygge
                                    <x-help-tooltip text="Tilføj en blød eller tydelig skygge, eller lad tema-standard bestemme udtrykket." />
                                </span>
                                <select name="shadow_style" class="ui-field__control site-select-control">
                                    @foreach (\App\Models\SiteHeaderSetting::shadowOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected($headerShadowStyle === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="ui-field">
                                <span class="ui-field__label ui-field__label--with-help">
                                    Placering ved scroll
                                    <x-help-tooltip text="Bestem om headeren skal blive fast i toppen, eller om den skal scrolle med siden." />
                                </span>
                                <select name="sticky_mode" class="ui-field__control site-select-control">
                                    @foreach (\App\Models\SiteHeaderSetting::stickyOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected($headerStickyMode === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div class="site-global-module-note">
                            Tilpas headeren samt det generelle udtryk efter hvad du synes passer bedst. Baggrund, tekstfarve, skygge og placering gælder globalt for hele websitet.
                        </div>
                    </section>

                    <section class="site-global-module-section site-global-module-section--header-cta">
                        <div class="site-global-module-section__header">
                            <p class="site-global-module-section__eyebrow">CTA</p>
                            <h5 class="site-global-module-section__title">Header-knap</h5>
                        </div>

                        <div class="site-global-form__grid">
                            <div class="site-page-form-card__toggles">
                                <input type="hidden" name="show_cta" value="0">
                                <label class="site-page-form-card__checkbox">
                                    <input type="checkbox" name="show_cta" value="1" {{ old('show_cta', $headerSettings?->show_cta) ? 'checked' : '' }}>
                                    <span class="ui-field__label--with-help">Vis header-knap <x-help-tooltip text="Gør den globale CTA-knap synlig i website-headeren." /></span>
                                </label>
                            </div>

                            <label class="ui-field">
                                <span class="ui-field__label ui-field__label--with-help">
                                    CTA-tekst
                                    <x-help-tooltip text="Teksten på den primære knap i headeren, hvis du vil have en global handlingsknap." />
                                </span>
                                <input type="text" name="cta_label" value="{{ old('cta_label', $headerSettings?->cta_label) }}" class="ui-field__control">
                            </label>

                            <label class="ui-field">
                                <span class="ui-field__label ui-field__label--with-help">
                                    CTA-link
                                    <x-help-tooltip text="Hvor header-knappen skal sende brugeren hen, fx /contact, www.platebook.dk eller en booking-side." />
                                </span>
                                <input type="text" name="cta_href" value="{{ old('cta_href', $headerSettings?->cta_href) }}" class="ui-field__control" placeholder="www.platebook.dk eller /contact">
                            </label>

                            <div class="site-global-module-note">
                                CTA-knappen er den ekstra handlingsknap, der vises i toppen af websitet i headeren. Brug den fx til booking, kontakt eller et eksternt link som PlateBook.
                            </div>
                        </div>
                    </section>
                </div>
            </fieldset>

            <div class="site-dashboard-panel__actions">
                @if ($canUpdateSite)
                    <button type="submit" class="ui-button ui-button--ink">Gem header</button>
                @else
                    <p class="ui-copy">Denne tenant-rolle giver kun læseadgang til website-headeren.</p>
                @endif
            </div>
        </form>
    </section>
</section>
