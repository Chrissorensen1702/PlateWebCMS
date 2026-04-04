<x-app-layout>
    @php($customerSearchIndex = $tenants->mapWithKeys(fn ($tenant) => [(string) $tenant->id => mb_strtolower(trim($tenant->name . ' ' . ($tenant->cvr_number ?? '') . ' ' . ($tenant->display_email ?? '')), 'UTF-8')])->all())
    @php($createCustomerSiteErrors = $errors->getBag('createCustomerSite'))

    <div
        class="customers-page"
        x-data="{
            openCustomer: '',
            openCreate: @js($createCustomerSiteErrors->any()),
            customerSearch: '',
            customerSearchIndex: @js($customerSearchIndex),
            matchesCustomer(customerId) {
                const searchTerm = this.customerSearch.trim().toLowerCase();
                const customerText = this.customerSearchIndex[customerId] ?? '';

                return ! searchTerm || customerText.includes(searchTerm);
            },
            hasCustomerMatches() {
                const searchTerm = this.customerSearch.trim().toLowerCase();

                if (! searchTerm) {
                    return true;
                }

                return Object.values(this.customerSearchIndex).some((customerText) => customerText.includes(searchTerm));
            },
        }"
    >
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="dashboard-page__surface customers-page__surface">
                <section class="ui-card dashboard-panel customers-page__panel">
                    <div class="customers-page__header">
                        <div>
                            <p class="section-heading__kicker">Kunder</p>
                            <h2 class="dashboard-panel__title">Kunder og websites</h2>
                            <p class="customers-page__copy">
                                Find og rediger for kunde. Åbn deres websites og hop direkte videre til det aktuelle site-dashboard.
                            </p>
                        </div>

                        @if ($canCreateCustomerSites)
                            <button type="button" class="dashboard-stat-card__action customers-page__action" x-on:click="openCreate = true">
                                <span class="customers-page__action-icon" aria-hidden="true">+</span>
                                <span>Opret kunde og site</span>
                            </button>
                        @endif
                    </div>

                    <label class="ui-field dashboard-customer-search">
                        <span class="ui-field__label">Søg kunde</span>
                        <input
                            type="text"
                            x-model.trim="customerSearch"
                            class="ui-field__control"
                            placeholder="Søg på navn, CVR eller e-mail"
                        >
                    </label>

                    <div class="dashboard-feed">
                        @forelse ($tenants as $tenant)
                            <button
                                type="button"
                                class="dashboard-customer-item"
                                x-show="matchesCustomer('{{ $tenant->id }}')"
                                x-on:click="openCustomer = '{{ $tenant->id }}'"
                            >
                                <div class="dashboard-feed__row">
                                    <p class="dashboard-feed__title">{{ $tenant->name }}</p>
                                    <div class="customers-page__meta-group">
                                        <span class="dashboard-feed__meta">{{ $tenant->sites_count }} sites</span>
                                        @if ($tenant->cvr_number)
                                            <span class="dashboard-feed__meta">CVR {{ $tenant->cvr_number }}</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="dashboard-feed__copy">
                                    {{ $tenant->display_email ?? 'Ingen firma-email endnu' }}
                                </p>
                            </button>
                        @empty
                            <p class="dashboard-feed__empty">
                                Der er ingen kunder endnu. Opret den første kunde for at komme videre.
                            </p>
                        @endforelse

                        @if ($tenants->isNotEmpty())
                            <p class="dashboard-feed__empty" x-cloak x-show="customerSearch && ! hasCustomerMatches()">
                                Ingen kunder matcher din søgning endnu.
                            </p>
                        @endif
                    </div>
                </section>
            </div>
        </div>

        <div class="dashboard-drawer" x-show="openCustomer" x-on:keydown.escape.window="openCustomer = ''" x-cloak>
            <div class="dashboard-drawer__backdrop" x-on:click="openCustomer = ''"></div>

            <aside class="dashboard-drawer__panel" x-on:click.stop="">
                @foreach ($tenants as $tenant)
                    <div x-show="openCustomer === '{{ $tenant->id }}'" x-cloak>
                        <div class="dashboard-drawer__header">
                            <div>
                                <p class="section-heading__kicker">Kunde</p>
                                <h3 class="dashboard-panel__title">{{ $tenant->name }}</h3>
                                <p class="dashboard-feed__copy">
                                    {{ $tenant->display_email ?? 'Ingen firma-email endnu' }}{{ $tenant->phone ? ' - ' . $tenant->phone : '' }}
                                </p>
                            </div>

                            <button type="button" class="ui-button ui-button--outline dashboard-drawer__close" x-on:click="openCustomer = ''">
                                Luk
                            </button>
                        </div>

                        <div class="dashboard-drawer__meta">
                            <span class="dashboard-feed__meta">{{ $tenant->sites_count }} sites</span>
                            @if ($tenant->cvr_number)
                                <span class="dashboard-feed__meta">CVR {{ $tenant->cvr_number }}</span>
                            @endif
                        </div>

                        <div class="dashboard-drawer__list">
                            @forelse ($tenant->sites as $site)
                                <article class="dashboard-site-entry">
                                    <div class="dashboard-feed__row">
                                        <div>
                                            <p class="dashboard-feed__title">{{ $site->name }}</p>
                                            <p class="dashboard-feed__copy">
                                                {{ $site->plan?->name ?? 'Ingen plan' }}{{ $site->is_online ? ' - online' : ' - kladde' }}
                                            </p>
                                        </div>

                                        <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--ink dashboard-site-entry__action">
                                            Åbn site
                                        </a>
                                    </div>
                                </article>
                            @empty
                                <div class="dashboard-feed__empty">
                                    Kunden har ikke nogen sites endnu.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </aside>
        </div>

        @if ($canCreateCustomerSites)
            <div class="dashboard-drawer" x-show="openCreate" x-on:keydown.escape.window="openCreate = false" x-cloak>
                <div class="dashboard-drawer__backdrop" x-on:click="openCreate = false"></div>

                <aside class="dashboard-drawer__panel dashboard-drawer__panel--form" x-on:click.stop="">
                    <div class="dashboard-drawer__header">
                        <div>
                            <p class="section-heading__kicker">Nyt kundeprojekt</p>
                            <h3 class="dashboard-panel__title">Opret kunde og site</h3>
                            <p class="dashboard-feed__copy">
                                Det her laver en ny tenant, en login-bruger til kunden, et nyt website og en starter-forside + kontaktside.
                            </p>
                        </div>

                        <button type="button" class="ui-button ui-button--outline dashboard-drawer__close" x-on:click="openCreate = false">
                            Luk
                        </button>
                    </div>

                    @if ($createCustomerSiteErrors->any())
                        <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                            <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                            <ul class="ui-list">
                                @foreach ($createCustomerSiteErrors->all() as $error)
                                    <li class="ui-list__item">
                                        <span class="ui-list__dot"></span>
                                        <span>{{ $error }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('cms.dashboard.customer-sites.store') }}" class="dashboard-create-site-form">
                        @csrf
                        <input type="hidden" name="form_target" value="customers-create">

                        @include('cms.pages.customer-sites.partials.form-fields')

                        <div class="dashboard-create-site-form__actions">
                            <button type="submit" class="ui-button ui-button--success">
                                Opret kunde og site
                            </button>
                        </div>
                    </form>
                </aside>
            </div>
        @endif
    </div>
</x-app-layout>
