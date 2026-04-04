<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Domæne</p>
            <h3 class="site-dashboard-panel__title">Site-domæner og publicering</h3>
            <p class="site-dashboard-panel__copy">Når vi senere bygger selvbetjening til domæner, er det her den naturlige plads i CMS’et.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    <div class="site-dashboard-panel__details">
        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Primært domæne</span>
            <strong>{{ $site->primary_domain ?? 'Ikke sat endnu' }}</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Antal domæner</span>
            <strong>{{ $site->domains->count() }}</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Næste skridt</span>
            <strong>Domænemodul og DNS-verificering kan bygges her senere</strong>
        </div>
    </div>
</section>
