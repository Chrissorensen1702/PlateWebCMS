<x-app-layout>
    @php($nameParts = \Illuminate\Support\Str::of(Auth::user()->name)->trim()->explode(' ')->filter()->values())
    @php($firstName = $nameParts->first() ?? Auth::user()->name)
    @php($lastName = $nameParts->skip(1)->implode(' '))
    @php($isDeveloper = Auth::user()->isDeveloper())

    @unless ($isDeveloper)
        <x-slot name="header">
            <div class="section-heading dashboard-page__heading">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">INTERNT OVERBLIK</p>
                    <h2 class="section-heading__title">Velkommen tilbage, {{ trim($firstName . ' ' . $lastName) }}</h2>
                </div>
            </div>
        </x-slot>
    @endunless

    <div class="dashboard-page">
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($canViewLeads)
                <div class="dashboard-page__surface-stack">
                    <div class="dashboard-page__developer-grid">
                        <div class="dashboard-page__developer-main">
                            <div class="dashboard-page__surface dashboard-page__surface--stats">
                                <p class="section-heading__kicker">HURTIGT OVERBLIK</p>

                                <div class="dashboard-page__stats">
                                    @foreach ($stats as $stat)
                                    <article class="ui-card dashboard-stat-card">
                                        <p class="dashboard-stat-card__label">{{ $stat['label'] }}</p>

                                        <div class="dashboard-stat-card__body">
                                            <p class="dashboard-stat-card__value">{{ $stat['value'] }}</p>
                                            <p class="dashboard-stat-card__copy">{{ $stat['copy'] }}</p>
                                        </div>

                                        @if (! empty($stat['action']))
                                            <div class="dashboard-stat-card__footer">
                                                @if (! empty($stat['action']['href']))
                                                    <a href="{{ $stat['action']['href'] }}" class="dashboard-stat-card__action">
                                                        {{ $stat['action']['label'] }}
                                                    </a>
                                                @else
                                                    <span class="dashboard-stat-card__action dashboard-stat-card__action--disabled">
                                                        {{ $stat['action']['label'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </article>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-page__developer-side">
                            <section class="ui-card ui-card--dark dashboard-hero dashboard-hero--cloud dashboard-page__cloud-panel">
                                <p class="ui-kicker ui-kicker--light">Laravel Cloud</p>

                                <div class="dashboard-cloud-panels">
                                    @foreach (($laravelCloudPanels ?? []) as $panel)
                                        @php($statusTone = $panel['status_tone'] ?? 'neutral')
                                        @php($statusDotStyles = match ($statusTone) {
                                            'success' => '--status-dot:#4ade80;--status-dot-ring:rgba(74,222,128,0.24);',
                                            'warning' => '--status-dot:#fbbf24;--status-dot-ring:rgba(251,191,36,0.18);',
                                            'danger' => '--status-dot:#f87171;--status-dot-ring:rgba(248,113,113,0.18);',
                                            default => '--status-dot:#94a3b8;--status-dot-ring:rgba(148,163,184,0.18);',
                                        })
                                        <article class="dashboard-cloud-panel-card">
                                            <div class="dashboard-cloud-panel-card__top">
                                                <div>
                                                    <p class="dashboard-cloud-panel-card__kicker">{{ $panel['panel_label'] }}</p>
                                                    <h4 class="dashboard-cloud-panel-card__title">{{ $panel['title'] }}</h4>
                                                    <p class="dashboard-cloud-panel-card__copy">{{ $panel['copy'] }}</p>
                                                    @if (! empty($panel['updated_at']))
                                                        <p class="dashboard-cloud-panel-card__meta">
                                                            Sidst opdateret {{ \Illuminate\Support\Carbon::parse($panel['updated_at'])->format('d.m.Y H:i') }}
                                                        </p>
                                                    @endif
                                                </div>

                                                @if (! empty($panel['status_label']))
                                                    <span class="dashboard-hero__status dashboard-hero__status--{{ $statusTone }}">
                                                        <span class="dashboard-hero__status-dot dashboard-hero__status-dot--{{ $statusTone }}" style="{{ $statusDotStyles }}"></span>
                                                        <span>{{ $panel['status_label'] }}</span>
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="dashboard-hero__cloud-grid dashboard-hero__cloud-grid--panel">
                                                @forelse (($panel['items'] ?? []) as $item)
                                                    <article class="dashboard-cloud-card">
                                                        <p class="dashboard-cloud-card__label">{{ $item['label'] }}</p>
                                                        <p class="dashboard-cloud-card__value">{{ $item['value'] }}</p>
                                                        <p class="dashboard-cloud-card__meta">{{ $item['meta'] }}</p>
                                                    </article>
                                                @empty
                                                    <div class="dashboard-cloud-empty">
                                                        Laravel Cloud er ikke forbundet endnu. Tilføj API-token og environment-id for at vise deploys, miljøstatus og metrics her.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            @else
                <div class="dashboard-page__surface-stack">
                    <div class="dashboard-page__surface dashboard-page__surface--stats">
                        <p class="section-heading__kicker">HURTIGT OVERBLIK</p>

                        <div class="dashboard-page__stats">
                            @foreach ($stats as $stat)
                            <article class="ui-card dashboard-stat-card">
                                <p class="dashboard-stat-card__label">{{ $stat['label'] }}</p>

                                <div class="dashboard-stat-card__body">
                                    <p class="dashboard-stat-card__value">{{ $stat['value'] }}</p>
                                    <p class="dashboard-stat-card__copy">{{ $stat['copy'] }}</p>
                                </div>

                                @if (! empty($stat['action']))
                                    <div class="dashboard-stat-card__footer">
                                        @if (! empty($stat['action']['href']))
                                            <a href="{{ $stat['action']['href'] }}" class="dashboard-stat-card__action">
                                                {{ $stat['action']['label'] }}
                                            </a>
                                        @else
                                            <span class="dashboard-stat-card__action dashboard-stat-card__action--disabled">
                                                {{ $stat['action']['label'] }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="dashboard-page__surface dashboard-page__surface--content">
                        <div class="dashboard-page__layout">
                            <section class="ui-card ui-card--dark dashboard-hero">
                                <p class="ui-kicker ui-kicker--light">Klar retning</p>
                                <h3 class="ui-title">Naeste sprint kan handle om sider, sektioner og kundespecifikt indhold.</h3>
                                <p class="dashboard-hero__copy">
                                    Grunden er lagt med pakker, leads, sites og et kundelogin-flow. Det giver os et naturligt sted at bygge videre med sektioner uden at miste kontrol over designet.
                                </p>

                                <div class="dashboard-hero__actions">
                                    <a href="{{ route('cms.sites.index') }}" class="ui-button ui-button--light">
                                        Gaa til site-editor
                                    </a>
                                </div>

                                <div class="dashboard-hero__plans">
                                    @foreach ($plans as $plan)
                                        <article class="dashboard-plan-card">
                                            <p class="dashboard-plan-card__kind">{{ $plan->kind }}</p>
                                            <p class="dashboard-plan-card__name">{{ $plan->name }}</p>
                                            <p class="dashboard-plan-card__copy">{{ $plan->headline }}</p>
                                        </article>
                                    @endforeach
                                </div>
                            </section>

                            <div class="dashboard-page__panels">
                                <section class="ui-card dashboard-panel">
                                    <p class="section-heading__kicker">Sites</p>
                                    <h3 class="dashboard-panel__title">Mine sites</h3>

                                    <div class="dashboard-feed">
                                        @forelse ($recentSites as $site)
                                            @php($primaryContact = $site->tenant?->primary_contact)
                                            <article class="dashboard-feed__item">
                                                <div class="dashboard-feed__row">
                                                    <a href="{{ route('cms.sites.show', $site) }}" class="dashboard-feed__title dashboard-feed__title--link">{{ $site->name }}</a>
                                                    <span class="dashboard-feed__meta">{{ $site->status }}</span>
                                                </div>
                                                <p class="dashboard-feed__copy">
                                                    {{ $site->plan?->name ?? 'Ingen plan' }}{{ $site->tenant?->name ? ' - ' . $site->tenant->name : '' }}{{ $primaryContact?->name ? ' - ' . $primaryContact->name : '' }}
                                                </p>
                                            </article>
                                        @empty
                                            <p class="dashboard-feed__empty">
                                                Der er ingen sites endnu. Naeste trin kan vaere et site-oprettelsesflow, der kobler kunde og pakke sammen.
                                            </p>
                                        @endforelse
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
