@php($newsletterErrors = $errors->getBag('updateSiteNewsletter'))
@php($newsletterSettings = $site->newsletterSettings)

<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Nyhedsbrev</p>
            <h3 class="site-dashboard-panel__title">Nyhedsbrev og tilmeldinger</h3>
            <p class="site-dashboard-panel__copy">Aktivér nyhedsbrev på websitet, styr teksterne og vælg hvordan tilmeldinger skal samles, uden at vi bygger et helt mailsystem endnu.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('cms.sites.newsletter.update', $site) }}"
        class="site-global-form"
    >
        @csrf
        @method('PATCH')
        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

        @if ($newsletterErrors->any())
            <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                <p class="ui-copy">Der er lige et par nyhedsbrevsfelter vi skal have rettet:</p>
                <ul class="ui-list">
                    @foreach ($newsletterErrors->all() as $error)
                        <li class="ui-list__item">
                            <span class="ui-list__dot"></span>
                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <fieldset @disabled(! $canUpdateSite)>
            <div class="site-global-module-split">
                <section class="site-global-module-card">
                    <div class="site-global-module-card__header">
                        <div>
                            <p class="site-global-module-card__eyebrow">Opsætning</p>
                            <h4 class="site-global-module-card__title">Formular og placering</h4>
                            <p class="site-global-module-card__copy">Den første version handler om at samle tilmeldinger og gøre modulet let for kunden at forstå.</p>
                        </div>
                    </div>

                    <div class="site-global-form__grid">
                        <div class="site-page-form-card__toggles">
                            <input type="hidden" name="is_enabled" value="0">
                            <label class="site-page-form-card__checkbox">
                                <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $newsletterSettings?->is_enabled) ? 'checked' : '' }}>
                                <span class="ui-field__label--with-help">Aktivér nyhedsbrev <x-help-tooltip text="Viser nyhedsbrev-modulet på websitet, når formularen senere kobles på i theme eller footer." /></span>
                            </label>
                        </div>

                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Overskrift
                                <x-help-tooltip text="Vises over nyhedsbrev-formularen, fx Tilmeld dig vores nyhedsbrev." />
                            </span>
                            <input type="text" name="headline" value="{{ old('headline', $newsletterSettings?->headline ?: 'Tilmeld dig vores nyhedsbrev') }}" class="ui-field__control">
                        </label>

                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Kort tekst
                                <x-help-tooltip text="En kort forklaring om hvad modtageren får, fx nyheder, tilbud eller inspiration." />
                            </span>
                            <textarea name="copy" class="ui-field__control ui-field__control--textarea">{{ old('copy', $newsletterSettings?->copy ?: 'Få nyheder, inspiration og særlige tilbud direkte i indbakken.') }}</textarea>
                        </label>

                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Knaptekst
                                <x-help-tooltip text="Teksten på selve tilmeldingsknappen." />
                            </span>
                            <input type="text" name="button_label" value="{{ old('button_label', $newsletterSettings?->button_label ?: 'Tilmeld') }}" class="ui-field__control">
                        </label>

                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Placering
                                <x-help-tooltip text="Vælg hvor modulet skal være tænkt ind, så themes og kommende integrationer ved hvor det hører hjemme." />
                            </span>
                            <select name="placement" class="ui-field__control">
                                @php($currentPlacement = old('placement', $newsletterSettings?->placement ?: 'footer'))
                                <option value="footer" @selected($currentPlacement === 'footer')>Footer</option>
                                <option value="section" @selected($currentPlacement === 'section')>Sideafsnit</option>
                                <option value="both" @selected($currentPlacement === 'both')>Begge steder</option>
                            </select>
                        </label>
                    </div>
                </section>

                <section class="site-global-module-card">
                    <div class="site-global-module-card__header">
                        <div>
                            <p class="site-global-module-card__eyebrow">Data og samtykke</p>
                            <h4 class="site-global-module-card__title">Hvordan tilmeldinger håndteres</h4>
                            <p class="site-global-module-card__copy">Vi starter let: gem i CMS eller markér at en ekstern løsning skal tilsluttes senere.</p>
                        </div>
                    </div>

                    <div class="site-global-form__grid">
                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Leveringsmåde
                                <x-help-tooltip text="Kun i CMS er den simple første version. Ekstern betyder at vi senere kobler Mailchimp, Brevo eller lignende på." />
                            </span>
                            @php($currentMode = old('delivery_mode', $newsletterSettings?->delivery_mode ?: 'cms'))
                            <select name="delivery_mode" class="ui-field__control">
                                <option value="cms" @selected($currentMode === 'cms')>Kun i CMS</option>
                                <option value="external" @selected($currentMode === 'external')>Ekstern integration senere</option>
                            </select>
                        </label>

                        <label class="ui-field">
                            <span class="ui-field__label ui-field__label--with-help">
                                Samtykke-tekst
                                <x-help-tooltip text="Den korte tekst ved checkbox eller formular, så kunden tydeligt kan forklare hvad brugeren siger ja til." />
                            </span>
                            <textarea name="consent_text" class="ui-field__control ui-field__control--textarea">{{ old('consent_text', $newsletterSettings?->consent_text ?: 'Ja tak, jeg vil gerne modtage nyheder, inspiration og relevante tilbud på e-mail.') }}</textarea>
                        </label>

                        <div class="site-dashboard-panel__details">
                            <div class="site-dashboard-panel__detail">
                                <span class="site-dashboard-panel__detail-label">Status</span>
                                <strong>{{ old('is_enabled', $newsletterSettings?->is_enabled) ? 'Aktiveret' : 'Ikke aktiveret' }}</strong>
                            </div>

                            <div class="site-dashboard-panel__detail">
                                <span class="site-dashboard-panel__detail-label">Mål lige nu</span>
                                <strong>Samle subscribers og leads</strong>
                            </div>

                            <div class="site-dashboard-panel__detail">
                                <span class="site-dashboard-panel__detail-label">Næste lag</span>
                                <strong>Booking-checkbox og ekstern sync</strong>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </fieldset>

        <div class="site-dashboard-panel__actions">
            @if ($canUpdateSite)
                <button type="submit" class="ui-button ui-button--ink">Gem nyhedsbrev</button>
            @else
                <p class="ui-copy">Denne tenant-rolle giver kun læseadgang til nyhedsbrevsopsætningen.</p>
            @endif
        </div>
    </form>
</section>
