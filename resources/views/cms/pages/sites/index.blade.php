<x-app-layout>
    <x-slot name="header">
        <div class="section-heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">Sites</p>
                <h2 class="section-heading__title">Vaelg et site du vil redigere.</h2>
                <p class="section-heading__copy">
                    Herfra gaar du ind paa et kundesite og lander paa dets site-dashboard, hvor lokale sider og globalt indhold holdes adskilt.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="sites-page">
        <div class="ui-shell">
            <div class="sites-grid">
                @forelse ($sites as $site)
                    @php($primaryContact = $site->tenant?->primary_contact)
                    <article class="ui-card sites-card">
                        <div class="sites-card__header">
                            <div>
                                <p class="sites-card__eyebrow">{{ $site->theme }}</p>
                                <h3 class="sites-card__title">{{ $site->name }}</h3>
                            </div>

                            <span class="dashboard-feed__meta">{{ $site->status }}</span>
                        </div>

                        <p class="sites-card__copy">
                            {{ $site->plan?->name ?? 'Ingen plan valgt' }}{{ $site->tenant?->name ? ' - ' . $site->tenant->name : '' }}
                        </p>

                        <p class="sites-card__meta">
                            {{ $site->pages_count }} sider{{ $primaryContact?->name ? ' · Kontakt: ' . $primaryContact->name : '' }}
                        </p>

                        <div class="sites-card__actions">
                            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--ink">
                                @can('update', $site)
                                    Aaben site-dashboard
                                @else
                                    Se site-dashboard
                                @endcan
                            </a>
                            <a href="{{ route('sites.show', $site) }}" class="ui-button ui-button--outline">
                                Preview
                            </a>
                        </div>
                    </article>
                @empty
                    <article class="ui-card sites-empty">
                        <p class="section-heading__kicker">Ingen sites endnu</p>
                        <h3 class="ui-title">Der er ikke oprettet nogen sites til denne bruger.</h3>
                        <p class="ui-copy">
                            Naeste naturlige trin er at oprette et site i CMS'et eller koble en kunde til et eksisterende site.
                        </p>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
