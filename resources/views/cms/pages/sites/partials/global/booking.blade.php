<section class="ui-card site-dashboard-panel">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Bookingsystem</p>
            <h3 class="site-dashboard-panel__title">Booking og integrationer</h3>
            <p class="site-dashboard-panel__copy">Når websitet skal kobles sammen med bookingsystemet, er det her den globale opsætning bør leve.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    <div class="site-dashboard-panel__details">
        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Status</span>
            <strong>Ikke koblet endnu</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Placering</span>
            <strong>Globalt website-modul</strong>
        </div>

        <div class="site-dashboard-panel__detail">
            <span class="site-dashboard-panel__detail-label">Formål</span>
            <strong>Samler bookinglinks, widgets og fremtidige integrationer ét sted</strong>
        </div>
    </div>
</section>
