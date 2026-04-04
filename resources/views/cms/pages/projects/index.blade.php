<x-app-layout>
    @php($customerSearchIndex = $tenants->mapWithKeys(fn ($tenant) => [(string) $tenant->id => mb_strtolower(trim($tenant->name . ' ' . ($tenant->cvr_number ?? '') . ' ' . ($tenant->display_email ?? '')), 'UTF-8')])->all())

    <div
        class="projects-page"
        x-data="{
            customerSearch: '',
            openNoteItem: @js(old('note_item_id') ? (string) old('note_item_id') : ''),
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
            <div class="projects-page__layout">
                <section class="dashboard-page__surface projects-page__surface">
                    <div class="projects-page__header">
                        <div>
                            <p class="section-heading__kicker">Projektmappe</p>
                            <h2 class="dashboard-panel__title">Find kunder til projektmappen</h2>
                            <p class="projects-page__copy">
                                Søg i kunderne og tilføj dem til projektmappen, så vi har et samlet sted til de projekter vi arbejder videre på.
                            </p>
                        </div>
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
                            <article class="dashboard-customer-item projects-page__customer-item" x-show="matchesCustomer('{{ $tenant->id }}')">
                                <div class="dashboard-feed__row">
                                    <div>
                                        <p class="dashboard-feed__title">{{ $tenant->name }}</p>
                                        <p class="dashboard-feed__copy">
                                            {{ $tenant->display_email ?? 'Ingen firma-email endnu' }}
                                        </p>
                                    </div>

                                    <div class="projects-page__meta-group">
                                        <span class="dashboard-feed__meta">{{ $tenant->sites_count }} sites</span>
                                        @if ($tenant->cvr_number)
                                            <span class="dashboard-feed__meta">CVR {{ $tenant->cvr_number }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="projects-page__customer-actions">
                                    @if (in_array($tenant->id, $selectedTenantIds, true))
                                        <span class="dashboard-stat-card__action dashboard-stat-card__action--disabled">
                                            Allerede tilføjet
                                        </span>
                                    @elseif ($canManageProjects)
                                        <form method="POST" action="{{ route('cms.projects.store', $tenant) }}">
                                            @csrf
                                            <button type="submit" class="dashboard-stat-card__action">
                                                Tilføj til projektmappe
                                            </button>
                                        </form>
                                    @else
                                        <span class="dashboard-stat-card__action dashboard-stat-card__action--disabled">
                                            Læseadgang
                                        </span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="dashboard-feed__empty">
                                Der er ingen kunder endnu.
                            </p>
                        @endforelse

                        @if ($tenants->isNotEmpty())
                            <p class="dashboard-feed__empty" x-cloak x-show="customerSearch && ! hasCustomerMatches()">
                                Ingen kunder matcher din søgning endnu.
                            </p>
                        @endif
                    </div>
                </section>

                <section class="dashboard-page__surface projects-page__surface">
                    <div class="projects-page__header">
                        <div>
                            <p class="section-heading__kicker">Aktive projekter</p>
                            <h2 class="dashboard-panel__title">Kunder i projektmappen</h2>
                            <p class="projects-page__copy">
                                De kunder du lægger her, bliver samlet i højre side, så du hurtigt kan hoppe videre til deres websites og arbejde videre.
                            </p>
                        </div>
                    </div>

                    <div class="dashboard-feed">
                        @forelse ($projectFolderItems as $item)
                            @php($tenant = $item->tenant)
                            @if ($tenant)
                                <article class="dashboard-site-entry projects-page__project-card" x-data="{ sitesOpen: false }">
                                    <div class="projects-page__project-card-top">
                                        <div>
                                            <p class="dashboard-feed__title">{{ $tenant->name }}</p>
                                            <p class="dashboard-feed__copy">
                                                {{ $tenant->display_email ?? 'Ingen firma-email endnu' }}
                                            </p>
                                            <div class="projects-page__project-meta-group">
                                                <span class="dashboard-feed__meta">
                                                    {{ $tenant->sites->count() }} {{ \Illuminate\Support\Str::plural('site', $tenant->sites->count()) }}
                                                </span>
                                                @if ($tenant->cvr_number)
                                                    <span class="dashboard-feed__meta">CVR {{ $tenant->cvr_number }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="projects-page__project-actions">
                                            <button
                                                type="button"
                                                class="projects-page__toggle-trigger"
                                                x-bind:class="{ 'projects-page__toggle-trigger--open': sitesOpen }"
                                                x-on:click="sitesOpen = ! sitesOpen"
                                                :aria-expanded="sitesOpen.toString()"
                                                title="Vis sites"
                                                aria-label="Vis sites"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="m6.75 9.75 5.25 5.25 5.25-5.25" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9"/>
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                class="projects-page__note-trigger{{ $item->notes ? ' projects-page__note-trigger--filled' : '' }}"
                                                x-on:click="openNoteItem = '{{ $item->id }}'"
                                                title="{{ $item->notes ? 'Åbn note' : 'Tilføj note' }}"
                                                aria-label="{{ $item->notes ? 'Åbn note' : 'Tilføj note' }}"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M7 4.75A2.25 2.25 0 0 0 4.75 7v10A2.25 2.25 0 0 0 7 19.25h10A2.25 2.25 0 0 0 19.25 17V9.81a2.25 2.25 0 0 0-.66-1.59l-2.81-2.81a2.25 2.25 0 0 0-1.59-.66H7Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/>
                                                    <path d="M14 4.75V8a1 1 0 0 0 1 1h3.25" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"/>
                                                    <path d="M8.75 12.25h6.5M8.75 15.25h4.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.7"/>
                                                </svg>
                                            </button>

                                            @if ($canManageProjects)
                                                <form method="POST" action="{{ route('cms.projects.destroy', $item) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ui-button ui-button--outline dashboard-drawer__close">
                                                        Fjern
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="projects-page__site-list" x-show="sitesOpen" x-cloak>
                                        @forelse ($tenant->sites as $site)
                                            <div class="projects-page__site-item">
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
                                        @empty
                                            <p class="dashboard-feed__empty">
                                                Kunden har ikke nogen websites endnu.
                                            </p>
                                        @endforelse
                                    </div>
                                </article>
                            @endif
                        @empty
                            <p class="dashboard-feed__empty">
                                Projektmappen er tom endnu. Tilføj en kunde fra venstre side for at komme i gang.
                            </p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>

        <div class="projects-page__note-modal" x-show="openNoteItem" x-on:keydown.escape.window="openNoteItem = ''" x-cloak>
            <div class="projects-page__note-backdrop" x-on:click="openNoteItem = ''"></div>

            @foreach ($projectFolderItems as $item)
                @if ($item->tenant)
                    <section class="projects-page__note-dialog" x-show="openNoteItem === '{{ $item->id }}'" x-cloak>
                        <div class="projects-page__note-header">
                            <div>
                                <p class="section-heading__kicker">Noter</p>
                                <h2 class="dashboard-panel__title">{{ $item->tenant->name }}</h2>
                                <p class="projects-page__notes-copy">
                                    Skriv en kort note om status, næste step eller hvad vi arbejder på lige nu.
                                </p>
                            </div>

                            <button type="button" class="ui-button ui-button--outline projects-page__note-close" x-on:click="openNoteItem = ''">
                                Luk
                            </button>
                        </div>

                        @if ($canManageProjects)
                            <form method="POST" action="{{ route('cms.projects.update', $item) }}" class="projects-page__notes-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="note_item_id" value="{{ $item->id }}">
                                <textarea
                                    name="notes"
                                    rows="6"
                                    class="ui-field__control projects-page__notes-input"
                                    placeholder="Fx: Afventer designfeedback fra kunden eller næste step er at færdiggøre kontaktsektionen."
                                >{{ old('note_item_id') == $item->id ? old('notes', $item->notes) : $item->notes }}</textarea>

                                @if (old('note_item_id') == $item->id)
                                    @error('notes')
                                        <p class="ui-field__error">{{ $message }}</p>
                                    @enderror
                                @endif

                                <div class="projects-page__notes-actions">
                                    <button type="submit" class="dashboard-stat-card__action">
                                        Gem note
                                    </button>
                                </div>
                            </form>
                        @elseif ($item->notes)
                            <div class="projects-page__notes-display">
                                {!! nl2br(e($item->notes)) !!}
                            </div>
                        @else
                            <p class="dashboard-feed__empty">
                                Der er ikke skrevet nogen note endnu.
                            </p>
                        @endif
                    </section>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
