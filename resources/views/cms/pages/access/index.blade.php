<x-app-layout>
    @php
        $accessHeader = match (true) {
            $canManageDeveloperAccounts => [
                'kicker' => 'Brugere & adgang',
                'title' => 'Platform- og tenantadgange',
                'copy' => 'Tilføj, rediger eller fjern adgange til systemet.',
            ],
            auth()->user()?->isDeveloper() => [
                'kicker' => 'Brugere & adgang',
                'title' => 'Adgangskonti til tenants',
                'copy' => 'Styr kundeadgange på tværs af tenants.',
            ],
            default => [
                'kicker' => 'Brugere & adgang',
                'title' => 'Adgangskonti til din tenant',
                'copy' => 'Tilføj, rediger eller fjern adgange til teamet i din tenant.',
            ],
        };
    @endphp

    <x-slot name="header">
        <div class="section-heading access-page__heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">{{ $accessHeader['kicker'] }}</p>
                <h2 class="section-heading__title">{{ $accessHeader['title'] }}</h2>
                <p class="section-heading__copy">{{ $accessHeader['copy'] }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $targetTenantId = old('form_target');
        $targetTenantId = is_string($targetTenantId) && str_starts_with($targetTenantId, 'tenant-')
            ? substr($targetTenantId, 7)
            : '';
    @endphp

    <div class="access-page" x-data="{ openTenant: '{{ $targetTenantId }}', tenantSearch: '' }">
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="access-grid{{ $canManageDeveloperAccounts ? ' access-grid--developer' : '' }}">
                @if ($canManageDeveloperAccounts)
                    @php
                        $developerErrors = $errors->getBag('developerAccess');
                    @endphp

                    <div class="access-grid__column access-grid__column--platform">
                        <section class="ui-card access-card access-card--developer">
                            <div class="access-card__header">
                                <div>
                                    <p class="access-card__eyebrow">Platform adgang</p>
                                    <h3 class="access-card__title">Developer-konti og adgangsniveauer</h3>
                                    <p class="access-card__copy">
                                        Brug fuld adgang til platformejere, kundeadgang til dem der opretter og administrerer kunder, og læseadgang til dem der kun skal kunne kigge med.
                                    </p>
                                </div>
                            </div>

                            <div class="access-members">
                                @foreach ($developerAccounts as $developer)
                                    <article class="access-member">
                                        <div class="access-member__copy">
                                            <p class="access-member__name">
                                                {{ $developer->displayNameWithEmploymentRole() }}
                                                @if ($developer->id === auth()->id())
                                                    <span class="access-member__you">(dig)</span>
                                                @endif
                                            </p>
                                            <p class="access-member__email">{{ $developer->email }}</p>
                                            <p class="access-member__meta">
                                                Platform: developer
                                                @if ($developer->employment_role)
                                                    · Ansættelsesrolle: {{ $developer->employment_role }}
                                                @endif
                                                · Adgang: {{ $developer->developerAccessLabel() }}
                                            </p>
                                        </div>

                                        <span class="access-member__badge access-member__badge--{{ $developer->developer_access }}">
                                            {{ $developer->developerAccessLabel() }}
                                        </span>
                                    </article>
                                @endforeach
                            </div>

                            <div class="access-card__form-shell">
                                <div class="access-card__intro">
                                    <p class="access-card__form-title">Opret eller opdater developer-konto</p>
                                    <p class="ui-copy">
                                        Hvis emailen allerede findes som developer, opdaterer vi navn, ansættelsesrolle og adgangsniveau. Password bruges kun ved nye developer-konti.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('cms.access.developers.store') }}" class="access-form">
                                    @csrf

                                    @if ($developerErrors->any())
                                        <div class="access-form__errors">
                                            <p class="ui-copy">Vi skal lige rette de her felter:</p>
                                            <ul class="ui-list">
                                                @foreach ($developerErrors->all() as $error)
                                                    <li class="ui-list__item">
                                                        <span class="ui-list__dot"></span>
                                                        <span>{{ $error }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="access-form__grid">
                                        <label class="ui-field">
                                            <span class="ui-field__label">Navn</span>
                                            <input
                                                type="text"
                                                name="name"
                                                value="{{ old('name') }}"
                                                class="ui-field__control"
                                            >
                                        </label>

                                        <label class="ui-field">
                                            <span class="ui-field__label">Email</span>
                                            <input
                                                type="email"
                                                name="email"
                                                value="{{ old('email') }}"
                                                class="ui-field__control"
                                            >
                                        </label>

                                        <label class="ui-field">
                                            <span class="ui-field__label">Midlertidig adgangskode</span>
                                            <input
                                                type="password"
                                                name="password"
                                                value=""
                                                class="ui-field__control"
                                            >
                                        </label>

                                        <label class="ui-field">
                                            <span class="ui-field__label">Developer-adgang</span>
                                            <select name="developer_access" class="ui-field__control">
                                                @foreach ($developerAccessOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(old('developer_access', 'full_access') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="ui-field">
                                            <span class="ui-field__label">Ansættelsesrolle</span>
                                            <input
                                                type="text"
                                                name="employment_role"
                                                value="{{ old('employment_role') }}"
                                                class="ui-field__control"
                                                placeholder="Fx CEO, softwareingeniør eller websupport"
                                            >
                                        </label>
                                    </div>

                                    <div class="access-form__actions">
                                        <button type="submit" class="ui-button ui-button--ink">Gem developer-adgang</button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>
                @endif

                <div class="access-grid__column access-grid__column--tenants">
                    <section class="ui-card access-card access-card--tenant-list">
                        <div class="access-card__header">
                            <div>
                                <p class="access-card__eyebrow">Tenants</p>
                                <h3 class="access-card__title">Vælg en kunde</h3>
                                <p class="access-card__copy">
                                    Åben en tenant for at se brugere, roller og oprette eller ændre adgang.
                                </p>
                            </div>
                        </div>

                        <label class="ui-field access-tenant-list__search">
                            <span class="ui-field__label">Søg kunde</span>
                            <input
                                type="text"
                                x-model.trim="tenantSearch"
                                class="ui-field__control"
                                placeholder="Søg på navn, CVR eller email"
                            >
                        </label>

                        <div class="access-tenant-list">
                            @foreach ($tenants as $tenant)
                                <button
                                    type="button"
                                    class="access-tenant-list__item"
                                    x-show="('{{ strtolower($tenant->name . ' ' . ($tenant->cvr_number ?? '') . ' ' . ($tenant->display_email ?? '')) }}').includes(tenantSearch.toLowerCase())"
                                    x-on:click="openTenant = '{{ $tenant->id }}'"
                                >
                                    <div class="access-tenant-list__copy">
                                        <p class="access-tenant-list__name">
                                            {{ $tenant->name }}
                                            @if ($tenant->cvr_number)
                                                <span class="access-tenant-list__cvr">* CVR {{ $tenant->cvr_number }}</span>
                                            @endif
                                        </p>
                                        <p class="access-tenant-list__meta">
                                            {{ $tenant->display_email ?? 'Ingen firma-email endnu' }}{{ $tenant->phone ? ' · ' . $tenant->phone : '' }}
                                        </p>
                                    </div>

                                    <div class="access-tenant-list__stats">
                                        <span class="dashboard-feed__meta">{{ $tenant->users->count() }} brugere</span>
                                        <span class="dashboard-feed__meta">{{ $tenant->sites_count }} sites</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div
            class="access-drawer"
            x-cloak
            x-show="openTenant"
            x-on:keydown.escape.window="openTenant = ''"
        >
            <div class="access-drawer__backdrop" x-on:click="openTenant = ''"></div>

            <aside class="access-drawer__panel" x-on:click.stop>
                @foreach ($tenants as $tenant)
                    @php
                        $formTarget = "tenant-{$tenant->id}";
                        $errorBag = $errors->getBag("tenantAccess{$tenant->id}");
                        $isTargetedForm = old('form_target') === $formTarget;
                        $tenantRoleLabels = [
                            'owner' => 'Owner',
                            'editor' => 'Editor',
                            'viewer' => 'Viewer',
                        ];
                    @endphp

                    <div x-show="openTenant === '{{ $tenant->id }}'" x-cloak>
                        <div class="access-drawer__header">
                            <div>
                                <p class="access-card__eyebrow">Tenant adgang</p>
                                <h3 class="access-card__title">{{ $tenant->name }}</h3>
                                <p class="access-card__copy">
                                    {{ $tenant->display_email ?? 'Ingen firma-email endnu' }}{{ $tenant->phone ? ' · ' . $tenant->phone : '' }}
                                </p>
                            </div>

                            <button type="button" class="ui-button ui-button--outline access-drawer__close" x-on:click="openTenant = ''">
                                Luk
                            </button>
                        </div>

                        <div class="access-drawer__body">
                            <div class="access-card__meta">
                                <span class="dashboard-feed__meta">{{ $tenant->users->count() }} brugere</span>
                                <span class="dashboard-feed__meta">{{ $tenant->sites_count }} sites</span>
                                @if ($tenant->cvr_number)
                                    <span class="dashboard-feed__meta">CVR {{ $tenant->cvr_number }}</span>
                                @endif
                            </div>

                            <div class="access-members">
                                @foreach ($tenant->users as $member)
                                    <article class="access-member">
                                        <div class="access-member__copy">
                                            <p class="access-member__name">
                                                {{ $member->name }}
                                                @if ($member->id === auth()->id())
                                                    <span class="access-member__you">(dig)</span>
                                                @endif
                                            </p>
                                            <p class="access-member__email">{{ $member->email }}</p>
                                            <p class="access-member__meta">
                                                Platform: {{ $member->role }} · Tenant: {{ $tenantRoleLabels[$member->pivot->role] ?? $member->pivot->role }}
                                            </p>
                                        </div>

                                        <span class="access-member__badge access-member__badge--{{ $member->pivot->role }}">
                                            {{ $tenantRoleLabels[$member->pivot->role] ?? $member->pivot->role }}
                                        </span>
                                    </article>
                                @endforeach
                            </div>

                            <div class="access-card__form-shell">
                                <div class="access-card__intro">
                                    <p class="access-card__form-title">Tilføj bruger til {{ $tenant->name }}</p>
                                    <p class="ui-copy">
                                        Hvis emailen allerede findes i systemet, bliver brugeren bare knyttet til tenanten med den valgte rolle. Password bruges kun ved nye accounts.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('cms.access.users.store', $tenant) }}" class="access-form">
                                    @csrf
                                    <input type="hidden" name="form_target" value="{{ $formTarget }}">

                                    @if ($isTargetedForm && $errorBag->any())
                                        <div class="access-form__errors">
                                            <p class="ui-copy">Vi skal lige rette de her felter:</p>
                                            <ul class="ui-list">
                                                @foreach ($errorBag->all() as $error)
                                                    <li class="ui-list__item">
                                                        <span class="ui-list__dot"></span>
                                                        <span>{{ $error }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="access-form__grid">
                                        <label class="ui-field">
                                            <span class="ui-field__label">Navn</span>
                                            <input
                                                type="text"
                                                name="name"
                                                value="{{ $isTargetedForm ? old('name') : '' }}"
                                                class="ui-field__control"
                                            >
                                        </label>

                                        <label class="ui-field">
                                            <span class="ui-field__label">Email</span>
                                            <input
                                                type="email"
                                                name="email"
                                                value="{{ $isTargetedForm ? old('email') : '' }}"
                                                class="ui-field__control"
                                            >
                                        </label>

                                        <label class="ui-field">
                                            <span class="ui-field__label">Midlertidig adgangskode</span>
                                            <input
                                                type="password"
                                                name="password"
                                                value=""
                                                class="ui-field__control"
                                            >
                                        </label>

                                        <label class="ui-field">
                                            <span class="ui-field__label">Tenant-rolle</span>
                                            <select name="tenant_role" class="ui-field__control">
                                                @foreach ($tenantRoleLabels as $value => $label)
                                                    <option value="{{ $value }}" @selected(($isTargetedForm ? old('tenant_role') : 'viewer') === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    </div>

                                    <div class="access-form__actions">
                                        <button type="submit" class="ui-button ui-button--ink">Opret eller tilknyt bruger</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </aside>
        </div>
    </div>
</x-app-layout>
