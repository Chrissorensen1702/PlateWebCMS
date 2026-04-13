@php($bookingErrors = $errors->getBag('updateSiteBooking'))
@php($bookingSettings = $site->bookingSettings)
@php($primaryContact = $site->tenant?->primary_contact)
@php($isEnabled = (bool) old('is_enabled', $bookingSettings?->is_enabled))
@php($connectionMode = old('connection_mode', $bookingSettings?->connection_mode ?? 'create'))
@php($bookingReference = old('booking_reference', $bookingSettings?->booking_reference))
@php($resolvedDashboardUrl = \App\Support\Http\PublicSiteUrl::sanitize($bookingSettings?->dashboard_url))
@php($ownerName = $bookingSettings?->owner_name ?: $primaryContact?->name)
@php($ownerEmail = $bookingSettings?->owner_email ?: ($site->tenant?->display_email ?: $primaryContact?->email))
@php($provisionedAt = $bookingSettings?->provisioned_at)
@php($statusLabel = ! $isEnabled ? 'Ikke tilkoblet' : ($bookingReference ? 'Tilkoblet' : ($connectionMode === 'create' ? 'Klar til aktivering' : 'Afventer reference')))
@php($statusTone = ! $isEnabled ? 'muted' : ($bookingReference ? 'active' : 'draft'))
@php($connectionLabel = $connectionMode === 'existing' ? 'Eksisterende bookingsystem' : 'Nyt bookingsystem')

<section
    class="ui-card site-dashboard-panel"
    x-data="{
        enabled: @js($isEnabled),
        mode: @js($connectionMode),
    }"
>
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Bookingsystem</p>
            <h3 class="site-dashboard-panel__title">Tilkobl bookingsystem</h3>
            <p class="site-dashboard-panel__copy">Denne side handler kun om koblingen mellem sitet og bookingsystemet. Al bookinglogik, drift og opsaetning bliver i bookingsystemet.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    @if ($bookingErrors->any())
        <div class="site-page-form-card__errors site-page-form-card__errors--inline">
            <p class="ui-copy">Der er lige et par bookingfelter vi skal have rettet:</p>
            <ul class="ui-list">
                @foreach ($bookingErrors->all() as $error)
                    <li class="ui-list__item">
                        <span class="ui-list__dot"></span>
                        <span>{{ $error }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="site-booking-workspace">
        <div class="site-booking-overview">
            <div class="site-booking-overview__content">
                <span class="site-dashboard-panel__detail-label">Ren integration</span>
                <h4 class="site-booking-overview__title">CMS gemmer kun koblingen. Bookingsystemet ejer resten.</h4>
                <p class="site-booking-overview__copy">
                    Brug siden her til at aktivere et nyt bookingsystem eller koble et eksisterende sammen med sitet.
                    Sektioner, "BOOK NU"-knapper og andet website-setup kommer senere andre steder i CMS.
                </p>

                <div class="site-booking-status-row">
                    <span class="site-booking-pill site-booking-pill--{{ $statusTone }}">{{ $statusLabel }}</span>
                    <span class="site-booking-pill">{{ $connectionLabel }}</span>
                </div>
            </div>

            <div class="site-dashboard-panel__details">
                <div class="site-dashboard-panel__detail">
                    <span class="site-dashboard-panel__detail-label">Reference</span>
                    <strong>{{ $bookingReference ?: 'Ingen reference endnu' }}</strong>
                    <p class="site-booking-overview__detail-copy">Ved eksisterende bookingsystemer er referencekoden nok til at koble sitet sammen.</p>
                </div>

                <div class="site-dashboard-panel__detail">
                    <span class="site-dashboard-panel__detail-label">Booking-ejer</span>
                    <strong>{{ $ownerEmail ?: 'Bruger tenantens CMS-login' }}</strong>
                    <p class="site-booking-overview__detail-copy">
                        {{ $provisionedAt
                            ? 'Aktiveret '. $provisionedAt->format('d.m.Y \k\l. H:i')
                            : 'Ved aktivering bruger CMS den eksisterende brugeridentitet fra tenantens login.' }}
                    </p>
                </div>

                <div class="site-dashboard-panel__detail">
                    <span class="site-dashboard-panel__detail-label">Drift</span>
                    <strong>Foregar direkte i bookingsystemet</strong>
                    <p class="site-booking-overview__detail-copy">Tablet-login, services, tider og hele bookingflowet bliver ved med at bo i bookingsystemet.</p>
                </div>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('cms.sites.booking.update', $site) }}"
            class="site-global-form"
        >
            @csrf
            @method('PATCH')
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

            <fieldset @disabled(! $canUpdateSite)>
                <div class="site-global-module-split">
                    <section class="site-global-module-card">
                        <div class="site-global-module-card__header">
                            <div>
                                <p class="site-global-module-card__eyebrow">Aktivering</p>
                                <h4 class="site-global-module-card__title">Benyt bookingsystem</h4>
                                <p class="site-global-module-card__copy">V1 er bevidst enkel: sla til hvis sitet skal kobles til bookingsystemet, eller fra hvis koblingen ikke skal bruges.</p>
                            </div>
                        </div>

                        <div class="site-page-form-card__toggles">
                            <input type="hidden" name="is_enabled" value="0">
                            <label class="site-page-form-card__checkbox">
                                <input type="checkbox" name="is_enabled" value="1" x-model="enabled" @checked($isEnabled)>
                                <span class="ui-field__label--with-help">
                                    Benyt bookingsystem
                                    <x-help-tooltip text="Aktiver eller afbryd koblingen mellem dette site og bookingsystemet. Selve driften bliver i bookingproduktet." />
                                </span>
                            </label>
                        </div>

                        <p class="site-global-module-note">
                            Nar booking er aktiveret, kan du enten aktivere et nyt bookingsystem med tenantens CMS-login eller koble et eksisterende bookingsystem via referencekode.
                        </p>

                        <div class="site-booking-choice-grid" x-show="enabled" x-cloak>
                            <label class="site-booking-choice" x-bind:class="{ 'site-booking-choice--active': mode === 'create' }">
                                <input type="radio" name="connection_mode" value="create" x-model="mode" @checked($connectionMode === 'create')>

                                <span class="site-booking-choice__body">
                                    <strong>Aktiver nyt bookingsystem</strong>
                                    <small>CMS opretter bookingtenant og ejerlogin automatisk ud fra tenantens CMS-bruger.</small>
                                    <span class="site-booking-choice__meta">Ingen ekstra kontoformular pa denne side</span>
                                </span>
                            </label>

                            <label class="site-booking-choice" x-bind:class="{ 'site-booking-choice--active': mode === 'existing' }">
                                <input type="radio" name="connection_mode" value="existing" x-model="mode" @checked($connectionMode === 'existing')>

                                <span class="site-booking-choice__body">
                                    <strong>Tilkobl eksisterende bookingsystem</strong>
                                    <small>Brug referencekoden fra et allerede eksisterende bookingsystem.</small>
                                    <span class="site-booking-choice__meta">God til booking-only kunder eller migreringer</span>
                                </span>
                            </label>
                        </div>
                    </section>

                    <section class="site-global-module-card" x-show="enabled" x-cloak>
                        <div class="site-global-module-card__header">
                            <div>
                                <p class="site-global-module-card__eyebrow">Kobling</p>
                                <h4 class="site-global-module-card__title">Hvad skal CMS gemme?</h4>
                                <p class="site-global-module-card__copy">Kun reference og systemstatus. Al anden bookingopsaetning bliver liggende i bookingsystemet.</p>
                            </div>
                        </div>

                        <div class="site-global-module-note" x-show="mode === 'create'" x-cloak>
                            Klik pa aktivering, og sa opretter CMS automatisk bookingtenant, ejerlogin og standardlokation med samme login-identitet som i CMS.
                        </div>

                        <div class="site-global-module-note" x-show="mode === 'existing'" x-cloak>
                            Indsaet referencekoden fra bookingsystemet. Det er den eneste oplysning CMS behover for at gemme koblingen.
                        </div>

                        <div class="site-global-form__grid site-global-form__grid--top-space" x-show="mode === 'existing'" x-cloak>
                            <label class="ui-field">
                                <span class="ui-field__label ui-field__label--with-help">
                                    Referencekode
                                    <x-help-tooltip text="Koden eller sluggen som identificerer det eksisterende bookingsystem, der skal kobles til dette site." />
                                </span>
                                <input type="text" name="booking_reference" value="{{ $bookingReference }}" class="ui-field__control" placeholder="fx salon-maane eller tenant-ref">
                            </label>
                        </div>

                        <p class="site-global-module-note">
                            Dashboard-linket styres automatisk som systemstandard, sa kunden altid kan hoppe videre til bookingsystemets login fra CMS.
                        </p>
                    </section>
                </div>

                <div class="site-global-module-split" x-show="enabled" x-cloak>
                    <section class="site-global-module-card">
                        <div class="site-global-module-card__header">
                            <div>
                                <p class="site-global-module-card__eyebrow">Hurtig adgang</p>
                                <h4 class="site-global-module-card__title">Drift og genveje</h4>
                                <p class="site-global-module-card__copy">Koblingen giver en hurtig vej videre til det system, som faktisk bruges i den daglige drift.</p>
                            </div>
                        </div>

                        <div class="site-booking-quick-actions">
                            @if ($resolvedDashboardUrl)
                                <a href="{{ $resolvedDashboardUrl }}" class="ui-button ui-button--ink" target="_blank" rel="noreferrer">
                                    Aabn bookingsystem
                                </a>
                            @endif
                        </div>

                        <div class="site-dashboard-panel__details">
                            <div class="site-dashboard-panel__detail">
                                <span class="site-dashboard-panel__detail-label">Nyt bookingsystem</span>
                                <strong>Aktiveres med CMS-identitet</strong>
                                <p class="site-booking-overview__detail-copy">CMS bruger tenantens eksisterende brugeridentitet, sa kunden ikke skal gennem en ekstra oprettelsesformular her.</p>
                            </div>

                            <div class="site-dashboard-panel__detail">
                                <span class="site-dashboard-panel__detail-label">Eksisterende bookingsystem</span>
                                <strong>Kraver kun referencekode</strong>
                                <p class="site-booking-overview__detail-copy">Nar sitet allerede har et bookingsystem, holder vi koblingen sa let som muligt.</p>
                            </div>

                            <div class="site-dashboard-panel__detail">
                                <span class="site-dashboard-panel__detail-label">Website-placering</span>
                                <strong>Kommer andre steder i CMS</strong>
                                <p class="site-booking-overview__detail-copy">Bookingsektioner, header-knapper og andet front-end setup horer ikke hjemme pa denne integrationsside.</p>
                            </div>
                        </div>
                    </section>
                </div>
            </fieldset>

            <div class="site-dashboard-panel__actions">
                @if ($canUpdateSite)
                    <button
                        type="submit"
                        name="submit_action"
                        value="provision"
                        class="ui-button ui-button--ink"
                        x-show="enabled && mode === 'create'"
                        x-cloak
                    >
                        Aktivér bookingsystem
                    </button>

                    <button
                        type="submit"
                        name="submit_action"
                        value="save"
                        class="ui-button ui-button--ink"
                        x-show="enabled && mode === 'existing'"
                        x-cloak
                    >
                        Tilkobl bookingsystem
                    </button>

                    <button
                        type="submit"
                        name="submit_action"
                        value="save"
                        class="ui-button ui-button--outline"
                        x-show="!enabled"
                        x-cloak
                    >
                        Gem status
                    </button>
                @else
                    <p class="ui-copy">Denne tenant-rolle giver kun laeseadgang til booking-koblingen.</p>
                @endif
            </div>
        </form>
    </div>
</section>
