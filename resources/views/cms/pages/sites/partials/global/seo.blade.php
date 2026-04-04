<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">SEO</p>
            <h3 class="site-dashboard-panel__title">Global synlighed</h3>
            <p class="site-dashboard-panel__copy">Her kan vi samle globale SEO-indstillinger, så de ikke forsvinder rundt på de enkelte sider.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    <div class="site-dashboard-panel__details">
        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Kanonisk base</span>
            <strong>{{ $site->primary_domain ?? 'Ikke sat endnu' }}</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Nuværende status</span>
            <strong>Klar til globalt SEO-modul</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Hvorfor her?</span>
            <strong>Holder fælles metadata og indekseringsvalg adskilt fra sidedesigneren</strong>
        </div>
    </div>
</section>
