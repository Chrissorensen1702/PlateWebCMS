<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Website plan</p>
            <h3 class="site-dashboard-panel__title">Plan og fælles ramme</h3>
            <p class="site-dashboard-panel__copy">Her holder vi styr på den valgte websiteplan og den overordnede ramme, som gælder for hele sitet.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    <div class="site-dashboard-panel__details">
        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Valgt plan</span>
            <strong>{{ $site->plan?->name ?? 'Ingen plan valgt' }}</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Status</span>
            <strong>{{ $site->is_online ? 'Online' : 'Offline' }}</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Sidst publiceret</span>
            <strong>{{ $site->last_published_at?->format('d.m.Y H:i') ?? 'Ikke publiceret endnu' }}</strong>
        </div>
    </div>
</section>
