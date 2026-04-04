<div class="cms-overview__grid">
    <article class="ui-card ui-card--dark cms-overview__intro">
        <p class="ui-kicker ui-kicker--light">CMS retning</p>
        <h2 class="ui-title">Byg et system kunderne kan bruge uden at bryde designet.</h2>
        <p class="cms-overview__copy">
            Det nye projekt er sat op, saa salgssiden peger direkte ind i samme produkt som kundelogin, pakker og senere redigerbare containere, sider og medier.
        </p>
    </article>

    <div class="cms-overview__features">
        @foreach ($cmsFeatures as $feature)
            <article class="ui-card ui-card--soft cms-overview__feature">
                <p class="cms-overview__feature-title">{{ $feature }}</p>
            </article>
        @endforeach
    </div>
</div>
