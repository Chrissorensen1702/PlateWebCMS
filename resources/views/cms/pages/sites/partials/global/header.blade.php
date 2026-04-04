@php($headerErrors = $errors->getBag('updateSiteHeader'))
@php($footerErrors = $errors->getBag('updateSiteFooter'))
@php($headerSettings = $site->headerSettings)
@php($footerSettings = $site->footerSettings)
@php($footerNavigationDefaults = $site->pages()->published()->ordered()->get()->map(fn ($page) => ['label' => $page->name, 'href' => $page->is_home ? route('sites.show', $site) : route('sites.page', [$site, $page->slug])])->values()->all())
@php($footerNavigationLinks = collect(old('navigation_links', $footerSettings?->navigation_links ?: $footerNavigationDefaults))->values()->all())
@php($footerInformationLinks = collect(old('information_links', $footerSettings?->information_links ?: []))->values()->all())
@php($footerSocialPlatforms = \App\Support\Sites\SiteFooterSocialPlatforms::definitions())
@php($footerSocialLinks = \App\Support\Sites\SiteFooterSocialPlatforms::normalize(old('social_links', $footerSettings?->social_links ?: [])))

<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Header og footer</p>
            <h3 class="site-dashboard-panel__title">Website-header og footer</h3>
            <p class="site-dashboard-panel__copy">Her samler vi det globale top- og bundindhold på websitet. Footeren er nu én fælles struktur på tværs af hele sitet, så du kun redigerer ét sted.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    <div class="site-global-module-split">
        <section class="site-global-module-card">
            <div class="site-global-module-card__header">
                <div>
                    <p class="site-global-module-card__eyebrow">Header</p>
                    <h4 class="site-global-module-card__title">Logo, brand og CTA</h4>
                    <p class="site-global-module-card__copy">Styr hvordan websitet præsenterer sig helt oppe i toppen.</p>
                </div>
            </div>

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
                    <div class="site-global-form__grid">
                        <div class="site-page-form-card__toggles">
                            <input type="hidden" name="show_brand_name" value="0">
                            <label class="site-page-form-card__checkbox">
                                <input type="checkbox" name="show_brand_name" value="1" {{ old('show_brand_name', $headerSettings?->show_brand_name ?? true) ? 'checked' : '' }}>
                                <span class="ui-field__label--with-help">Vis brandnavn <x-help-tooltip text="Skjul eller vis brandnavnet i headeren. Hvis det er skjult og du har et logo, vises kun logoet." /></span>
                            </label>
                        </div>

                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Brandnavn
                                <x-help-tooltip text="Det navn der vises i website-headeren, hvis du vil bruge noget andet end sitets interne navn." />
                            </span>
                            <input type="text" name="brand_name" value="{{ old('brand_name', $headerSettings?->brand_name) }}" class="ui-field__control">
                        </label>

                        <div class="site-page-form-card__toggles">
                            <input type="hidden" name="show_tagline" value="0">
                            <label class="site-page-form-card__checkbox">
                                <input type="checkbox" name="show_tagline" value="1" {{ old('show_tagline', $headerSettings?->show_tagline ?? true) ? 'checked' : '' }}>
                                <span class="ui-field__label--with-help">Vis tagline <x-help-tooltip text="Skjul eller vis undertitlen i headeren. Hvis feltet er tomt, vises ingen tagline." /></span>
                            </label>
                        </div>

                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Tagline
                                <x-help-tooltip text="Den lille undertitel under eller ved siden af brandnavnet i headeren." />
                            </span>
                            <input type="text" name="tagline" value="{{ old('tagline', $headerSettings?->tagline) }}" class="ui-field__control">
                        </label>

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

                        <div class="site-page-form-card__toggles site-page-form-card__field--full">
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

                        <label class="ui-field site-page-form-card__field--full">
                            <span class="ui-field__label ui-field__label--with-help">
                                CTA-link
                                <x-help-tooltip text="Hvor header-knappen skal sende brugeren hen, fx /kontakt eller en booking-side." />
                            </span>
                            <input type="text" name="cta_href" value="{{ old('cta_href', $headerSettings?->cta_href) }}" class="ui-field__control">
                        </label>
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

        <section class="site-global-module-card">
            <div class="site-global-module-card__header">
                <div>
                    <p class="site-global-module-card__eyebrow">Footer</p>
                    <h4 class="site-global-module-card__title">Fælles footer</h4>
                    <p class="site-global-module-card__copy">Footeren bruger samme struktur på tværs af hele sitet: Navigation, Information, Kontakt og Følg os.</p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('cms.sites.footer.update', $site) }}"
                class="site-global-form"
                x-data="{
                    footerLinkLimit: 8,
                    navigationLinks: @js($footerNavigationLinks),
                    informationLinks: @js($footerInformationLinks),
                    activeFooterSection: 'navigation',
                    canAddNavigationLink() { return this.navigationLinks.length < this.footerLinkLimit; },
                    canAddInformationLink() { return this.informationLinks.length < this.footerLinkLimit; },
                    addNavigationLink() {
                        if (! this.canAddNavigationLink()) return;
                        this.navigationLinks.push({ label: '', href: '' });
                    },
                    removeNavigationLink(index) { this.navigationLinks.splice(index, 1); },
                    addInformationLink() {
                        if (! this.canAddInformationLink()) return;
                        this.informationLinks.push({ label: '', href: '' });
                    },
                    removeInformationLink(index) { this.informationLinks.splice(index, 1); },
                }"
            >
                @csrf
                @method('PATCH')
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                @if ($footerErrors->any())
                    <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                        <p class="ui-copy">Der er lige et par footer-felter vi skal have rettet:</p>
                        <ul class="ui-list">
                            @foreach ($footerErrors->all() as $error)
                                <li class="ui-list__item">
                                    <span class="ui-list__dot"></span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <fieldset @disabled(! $canUpdateSite)>
                    <div class="site-footer-builder">
                        <div class="site-footer-builder__overview">
                            <button
                                type="button"
                                class="site-footer-builder__overview-item"
                                x-bind:class="{ 'site-footer-builder__overview-item--active': activeFooterSection === 'navigation' }"
                                x-on:click="activeFooterSection = 'navigation'"
                            >
                                <span>1</span>
                                <strong>Navigation</strong>
                            </button>
                            <button
                                type="button"
                                class="site-footer-builder__overview-item"
                                x-bind:class="{ 'site-footer-builder__overview-item--active': activeFooterSection === 'information' }"
                                x-on:click="activeFooterSection = 'information'"
                            >
                                <span>2</span>
                                <strong>Information</strong>
                            </button>
                            <button
                                type="button"
                                class="site-footer-builder__overview-item"
                                x-bind:class="{ 'site-footer-builder__overview-item--active': activeFooterSection === 'contact' }"
                                x-on:click="activeFooterSection = 'contact'"
                            >
                                <span>3</span>
                                <strong>Kontakt</strong>
                            </button>
                            <button
                                type="button"
                                class="site-footer-builder__overview-item"
                                x-bind:class="{ 'site-footer-builder__overview-item--active': activeFooterSection === 'social' }"
                                x-on:click="activeFooterSection = 'social'"
                            >
                                <span>4</span>
                                <strong>Følg os</strong>
                            </button>
                        </div>

                        <div class="site-footer-builder__panel">
                                <section class="site-global-module-section" x-show="activeFooterSection === 'navigation'">
                                    <div class="site-global-module-section__header">
                                        <p class="site-global-module-section__eyebrow">Navigation</p>
                                        <h5 class="site-global-module-section__title">Primære footer-links</h5>
                                        <p class="site-global-module-section__copy">Det her er de links, der skal være lettest at finde nederst på siden.</p>
                                    </div>

                                    <div class="site-global-repeater">
                                        <p class="ui-copy site-global-repeater__meta" x-text="'Maks 8 links. Du har ' + navigationLinks.length + ' af ' + footerLinkLimit + '.'"></p>

                                        <template x-for="(item, index) in navigationLinks" :key="'nav-' + index">
                                            <div class="site-global-repeater__row">
                                                <div class="site-global-repeater__fields">
                                                    <label class="ui-field">
                                                        <span class="ui-field__label">Linktekst</span>
                                                        <input x-model="item.label" x-bind:name="'navigation_links[' + index + '][label]'" type="text" class="ui-field__control">
                                                    </label>

                                                    <label class="ui-field">
                                                        <span class="ui-field__label">URL</span>
                                                        <input x-model="item.href" x-bind:name="'navigation_links[' + index + '][href]'" type="text" class="ui-field__control">
                                                    </label>
                                                </div>

                                                <button type="button" class="ui-button ui-button--outline site-global-repeater__remove" x-on:click="removeNavigationLink(index)">
                                                    Fjern
                                                </button>
                                            </div>
                                        </template>

                                        <button
                                            type="button"
                                            class="ui-button ui-button--outline site-global-repeater__add"
                                            x-on:click="addNavigationLink()"
                                            x-bind:disabled="! canAddNavigationLink()"
                                        >
                                            Tilføj link
                                        </button>
                                    </div>
                                </section>

                                <section class="site-global-module-section" x-show="activeFooterSection === 'information'" style="display: none;">
                                    <div class="site-global-module-section__header">
                                        <p class="site-global-module-section__eyebrow">Information</p>
                                        <h5 class="site-global-module-section__title">Sekundære links</h5>
                                        <p class="site-global-module-section__copy">God til politikker, betingelser, FAQ eller andet ekstra indhold.</p>
                                    </div>

                                    <div class="site-global-repeater">
                                        <p class="ui-copy site-global-repeater__meta" x-text="'Maks 8 links. Du har ' + informationLinks.length + ' af ' + footerLinkLimit + '.'"></p>

                                        <template x-for="(item, index) in informationLinks" :key="'info-' + index">
                                            <div class="site-global-repeater__row">
                                                <div class="site-global-repeater__fields">
                                                    <label class="ui-field">
                                                        <span class="ui-field__label">Linktekst</span>
                                                        <input x-model="item.label" x-bind:name="'information_links[' + index + '][label]'" type="text" class="ui-field__control">
                                                    </label>

                                                    <label class="ui-field">
                                                        <span class="ui-field__label">URL</span>
                                                        <input x-model="item.href" x-bind:name="'information_links[' + index + '][href]'" type="text" class="ui-field__control">
                                                    </label>
                                                </div>

                                                <button type="button" class="ui-button ui-button--outline site-global-repeater__remove" x-on:click="removeInformationLink(index)">
                                                    Fjern
                                                </button>
                                            </div>
                                        </template>

                                        <button
                                            type="button"
                                            class="ui-button ui-button--outline site-global-repeater__add"
                                            x-on:click="addInformationLink()"
                                            x-bind:disabled="! canAddInformationLink()"
                                        >
                                            Tilføj link
                                        </button>
                                    </div>
                                </section>

                                <section class="site-global-module-section" x-show="activeFooterSection === 'contact'" style="display: none;">
                                    <div class="site-global-module-section__header">
                                        <p class="site-global-module-section__eyebrow">Kontakt</p>
                                        <h5 class="site-global-module-section__title">Kontaktoplysninger</h5>
                                        <p class="site-global-module-section__copy">Felter kan slås til og fra, så footeren kun viser det, der giver mening.</p>
                                    </div>

                                    <div class="site-global-form__grid">
                                        <div class="site-page-form-card__toggles">
                                            <input type="hidden" name="show_contact_email" value="0">
                                            <label class="site-page-form-card__checkbox">
                                                <input type="checkbox" name="show_contact_email" value="1" {{ old('show_contact_email', $footerSettings?->show_contact_email ?? true) ? 'checked' : '' }}>
                                                <span class="ui-field__label--with-help">Vis e-mail <x-help-tooltip text="Slå feltet helt fra hvis e-mailen slet ikke skal vises i footeren. Hvis det er slået til og feltet er tomt, bruger footeren fallback." /></span>
                                            </label>
                                        </div>

                                        <label class="ui-field">
                                            <span class="ui-field__label ui-field__label--with-help">
                                                E-mail
                                                <x-help-tooltip text="Footerens e-mail. Hvis feltet er tomt, bruger vi virksomhedens e-mail som fallback." />
                                            </span>
                                            <input type="email" name="contact_email" value="{{ old('contact_email', $footerSettings?->contact_email) }}" class="ui-field__control">
                                        </label>

                                        <div class="site-page-form-card__toggles">
                                            <input type="hidden" name="show_contact_phone" value="0">
                                            <label class="site-page-form-card__checkbox">
                                                <input type="checkbox" name="show_contact_phone" value="1" {{ old('show_contact_phone', $footerSettings?->show_contact_phone ?? true) ? 'checked' : '' }}>
                                                <span class="ui-field__label--with-help">Vis telefon <x-help-tooltip text="Slå feltet helt fra hvis telefonnummeret ikke skal vises i footeren. Hvis det er slået til og feltet er tomt, bruger footeren fallback." /></span>
                                            </label>
                                        </div>

                                        <label class="ui-field">
                                            <span class="ui-field__label ui-field__label--with-help">
                                                Telefon
                                                <x-help-tooltip text="Footerens telefonnummer. Hvis feltet er tomt, bruger vi virksomhedens telefonnummer som fallback." />
                                            </span>
                                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $footerSettings?->contact_phone) }}" class="ui-field__control">
                                        </label>

                                        <div class="site-page-form-card__toggles site-page-form-card__field--full">
                                            <input type="hidden" name="show_contact_address" value="0">
                                            <label class="site-page-form-card__checkbox">
                                                <input type="checkbox" name="show_contact_address" value="1" {{ old('show_contact_address', $footerSettings?->show_contact_address ?? true) ? 'checked' : '' }}>
                                                <span class="ui-field__label--with-help">Vis adresse <x-help-tooltip text="Slå feltet helt fra hvis adresseblokken ikke skal vises i footeren." /></span>
                                            </label>
                                        </div>

                                        <label class="ui-field site-page-form-card__field--full">
                                            <span class="ui-field__label ui-field__label--with-help">
                                                Adresse eller ekstra linjer
                                                <x-help-tooltip text="Brug flere linjer hvis du vil vise adresse, by eller andre kontaktlinjer i footeren." />
                                            </span>
                                            <textarea name="contact_address" class="ui-field__control ui-field__control--textarea">{{ old('contact_address', $footerSettings?->contact_address) }}</textarea>
                                        </label>

                                        <div class="site-page-form-card__toggles">
                                            <input type="hidden" name="show_contact_cvr" value="0">
                                            <label class="site-page-form-card__checkbox">
                                                <input type="checkbox" name="show_contact_cvr" value="1" {{ old('show_contact_cvr', $footerSettings?->show_contact_cvr ?? true) ? 'checked' : '' }}>
                                                <span class="ui-field__label--with-help">Vis CVR <x-help-tooltip text="Slå feltet helt fra hvis CVR ikke skal vises i footeren. Hvis det er slået til og feltet er tomt, bruger footeren fallback." /></span>
                                            </label>
                                        </div>

                                        <label class="ui-field">
                                            <span class="ui-field__label ui-field__label--with-help">
                                                CVR
                                                <x-help-tooltip text="Vises som en del af footerens kontaktsektion eller bundnote." />
                                            </span>
                                            <input type="text" name="contact_cvr" value="{{ old('contact_cvr', $footerSettings?->contact_cvr) }}" class="ui-field__control">
                                        </label>
                                    </div>
                                </section>

                                <section class="site-global-module-section" x-show="activeFooterSection === 'social'" style="display: none;">
                                    <div class="site-global-module-section__header">
                                        <p class="site-global-module-section__eyebrow">Følg os</p>
                                        <h5 class="site-global-module-section__title">Sociale links</h5>
                                        <p class="site-global-module-section__copy">Vælg de store platforme der skal vises i footeren, og indsæt kun URL'er der faktisk skal være live.</p>
                                    </div>

                                    <div class="site-footer-social-grid">
                                        @foreach ($footerSocialPlatforms as $platformKey => $platform)
                                            <div class="site-footer-social-card">
                                                <div class="site-footer-social-card__header">
                                                    <div class="site-footer-social-card__identity">
                                                        <span class="site-footer-social-card__icon" aria-hidden="true">
                                                            @include('sites.shared.partials.social-icon', ['platform' => $platformKey])
                                                        </span>
                                                        <strong>{{ $platform['label'] }}</strong>
                                                    </div>

                                                    <div class="site-page-form-card__toggles">
                                                        <input type="hidden" name="social_links[{{ $platformKey }}][enabled]" value="0">
                                                        <label class="site-page-form-card__checkbox">
                                                            <input
                                                                type="checkbox"
                                                                name="social_links[{{ $platformKey }}][enabled]"
                                                                value="1"
                                                                {{ ($footerSocialLinks[$platformKey]['enabled'] ?? false) ? 'checked' : '' }}
                                                            >
                                                            <span>Vis i footer</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <label class="ui-field">
                                                    <span class="ui-field__label">URL</span>
                                                    <input
                                                        type="url"
                                                        name="social_links[{{ $platformKey }}][href]"
                                                        value="{{ $footerSocialLinks[$platformKey]['href'] ?? '' }}"
                                                        class="ui-field__control"
                                                        placeholder="{{ $platform['placeholder'] }}"
                                                    >
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                        </div>
                    </div>
                </fieldset>

                <div class="site-global-module-note">
                    Navigation starter med dine publicerede sider som default. Information er en fri linkliste, Kontakt styres med felter og vis/skjul-knapper, og Følg os bruger faste platforme med toggles.
                </div>

                <div class="site-dashboard-panel__actions">
                    @if ($canUpdateSite)
                        <button type="submit" class="ui-button ui-button--ink">Gem footer-indhold</button>
                    @else
                        <p class="ui-copy">Denne tenant-rolle giver kun læseadgang til footer-indholdet.</p>
                    @endif
                </div>
            </form>
        </section>
    </div>
</section>
