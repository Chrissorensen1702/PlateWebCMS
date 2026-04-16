<x-app-layout>
    <x-slot name="header">
        <div class="section-heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">SELVBETJENING</p>
                <h2 class="section-heading__title">Bestillinger</h2>
                <p class="section-heading__copy">
                    Her samler vi pakkevalg, vejledende pris og status for de løsninger kunderne selv har gemt eller oprettet.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="orders-page">
        <div class="ui-shell">
            <section class="ui-card orders-page__surface">
                <div class="orders-page__header">
                    <div>
                        <p class="section-heading__kicker">Overblik</p>
                        <h3 class="orders-page__title">Pakker, priser og oprettelser</h3>
                        <p class="orders-page__copy">
                            Brug siden til hurtigt at se hvilken løsning kunden har valgt, hvad den vejledende pris er, og om der allerede er oprettet tenant og website i CMS'et.
                        </p>
                    </div>

                    <div class="orders-page__meta">
                        <span class="dashboard-feed__meta">{{ $orders->count() }} bestillinger</span>
                    </div>
                </div>

                <div class="orders-page__list">
                    @forelse ($orders as $order)
                        <article class="orders-card">
                            <div class="orders-card__top">
                                <div>
                                    <div class="orders-card__title-row">
                                        <p class="orders-card__package">{{ $order['package_title'] }}</p>
                                        <span class="orders-card__status orders-card__status--{{ $order['status']['tone'] }}">{{ $order['status']['label'] }}</span>
                                    </div>

                                    <p class="orders-card__customer">
                                        {{ $order['tenant']?->name ?? $order['user']?->name ?? 'Ukendt kunde' }}
                                        @if ($order['user']?->email)
                                            · {{ $order['user']->email }}
                                        @endif
                                    </p>
                                </div>

                                <div class="orders-card__pricing">
                                    <p class="orders-card__price">{{ $order['price'] }}</p>
                                    @if ($order['price_note'])
                                        <p class="orders-card__price-note">{{ $order['price_note'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="orders-card__meta-row">
                                <span class="dashboard-feed__meta">Pakke: {{ $order['package_title'] }}</span>
                                @if ($order['tenant']?->company_email)
                                    <span class="dashboard-feed__meta">{{ $order['tenant']->company_email }}</span>
                                @endif
                                @if ($order['solution']->created_at)
                                    <span class="dashboard-feed__meta">{{ $order['solution']->created_at->format('d.m.Y') }}</span>
                                @endif
                            </div>

                            @if ($order['selection_summary'] !== '')
                                <p class="orders-card__summary">{{ $order['selection_summary'] }}</p>
                            @endif

                            <div class="orders-card__details">
                                <div class="orders-card__detail">
                                    <span class="orders-card__detail-label">Tenant</span>
                                    <span class="orders-card__detail-value">{{ $order['tenant']?->name ?? 'Ikke oprettet endnu' }}</span>
                                </div>

                                <div class="orders-card__detail">
                                    <span class="orders-card__detail-label">Website</span>
                                    <span class="orders-card__detail-value">{{ $order['site']?->name ?? 'Ikke oprettet endnu' }}</span>
                                </div>

                                <div class="orders-card__detail">
                                    <span class="orders-card__detail-label">Plan på site</span>
                                    <span class="orders-card__detail-value">{{ $order['site']?->plan?->name ?? $order['solution']->plan?->name ?? 'Ingen plan' }}</span>
                                </div>
                            </div>

                            @if ($order['site'])
                                <div class="orders-card__actions">
                                    <a href="{{ route('cms.sites.show', $order['site']) }}" class="orders-card__action">
                                        Åbn website i CMS
                                    </a>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="orders-page__empty">
                            <p class="ui-copy">Der er ingen selvbetjente bestillinger endnu. Når en kunde gemmer en løsning eller opretter sig fra prissiden, lander den her.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
