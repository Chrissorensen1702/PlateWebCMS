<x-app-layout>
    <x-slot name="header">
        <div class="section-heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">SALGSINTERESSE</p>
                <h2 class="section-heading__title">Henvendelser</h2>
                <p class="section-heading__copy">
                    Her samler vi henvendelser fra salgssiden, så du hurtigt kan se hvem der har vist interesse og hvilken pakke de kom ind på.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="leads-page">
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            <section class="ui-card leads-page__surface">
                <div class="leads-page__header">
                    <div>
                        <p class="section-heading__kicker">Oversigt</p>
                        <h3 class="leads-page__title">Henvendelser fra salgssiden</h3>
                        <p class="leads-page__copy">
                            Hold øje med navn, kontaktoplysninger, valgt pakke og besked - samlet ét sted.
                        </p>
                    </div>

                    <div class="leads-page__meta">
                        <span class="dashboard-feed__meta">{{ $leads->count() }} henvendelser</span>
                    </div>
                </div>

                <div class="leads-page__list">
                    @forelse ($leads as $lead)
                        <article class="leads-card">
                            <div class="leads-card__top">
                                <div>
                                    <p class="leads-card__name">{{ $lead->name }}</p>
                                    <p class="leads-card__contact">
                                        {{ $lead->email }}@if ($lead->phone) · {{ $lead->phone }}@endif
                                    </p>
                                </div>

                                <span class="leads-card__status leads-card__status--{{ $lead->status ?? 'new' }}">
                                    {{ $statusLabels[$lead->status] ?? ucfirst($lead->status ?? 'new') }}
                                </span>
                            </div>

                            <div class="leads-card__meta-row">
                                <span class="dashboard-feed__meta">{{ $lead->plan?->name ?? 'Ingen pakke valgt' }}</span>
                                @if ($lead->company)
                                    <span class="dashboard-feed__meta">{{ $lead->company }}</span>
                                @endif
                                <span class="dashboard-feed__meta">{{ optional($lead->created_at)->format('d.m.Y') }}</span>
                            </div>

                            @if ($lead->message)
                                <p class="leads-card__message">{{ $lead->message }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="leads-page__empty">
                            <p class="ui-copy">Der er ingen henvendelser endnu. Når nogen sender en besked fra salgssiden, lander de her.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
