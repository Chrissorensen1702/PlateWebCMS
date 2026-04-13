<section class="marketing-hero ui-section ui-section--hero">
    <div class="marketing-hero__surface">
        <div class="marketing-hero__shell">
            <div class="marketing-hero__intro">
                <div class="marketing-hero__copy">
                    <p class="marketing-hero__eyebrow">
                        <span class="marketing-hero__eyebrow-text">Bygget til vækst og konvertering</span>
                    </p>
                    <p class="ui-title ui-title--display marketing-hero__title">
                        <span class="marketing-hero__title-line">Hjemmeside, booking og betaling</span>
                        <span class="marketing-hero__title-line">samlet ét sted</span>
                    </p>
                    <p class="marketing-hero__lede">
                        Design din hjemmeside, opdater indholdet i et simpelt kunde-CMS og lad kunder booke direkte på siden
                        - uden at sende dem videre til væk fra dit website
                    </p>
                </div>

                <div class="marketing-hero__actions">
                    <a href="{{ route('templates') }}" class="ui-button ui-button--ink marketing-hero__button marketing-hero__button--solid">Se løsning</a>
                    <a href="{{ route('contact') }}" class="ui-button ui-button--outline marketing-hero__button marketing-hero__button--ghost">Prøv gratis i 30 dage</a>
                </div>

                <ul class="marketing-hero__feature-strip" aria-label="Platformens styrker">
                    <li class="marketing-hero__feature-step">
                        <span class="marketing-hero__feature-pill">Nem website designer</span>
                    </li>
                    <li class="marketing-hero__feature-step">
                        <span class="marketing-hero__feature-pill">Simpelt kunde-CMS</span>
                    </li>
                    <li class="marketing-hero__feature-step">
                        <span class="marketing-hero__feature-pill">Booking - direkte på egen side</span>
                    </li>
                    <li class="marketing-hero__feature-step">
                        <span class="marketing-hero__feature-pill">Integreret betaling</span>
                    </li>
                    <li class="marketing-hero__feature-step marketing-hero__feature-step--solution">
                        <span class="marketing-hero__feature-pill marketing-hero__feature-pill--solution">Samlet på én enhed</span>
                    </li>
                </ul>
            </div>

            <div class="marketing-hero__showcase" aria-label="PlateWeb website preview">
                <div class="marketing-hero__device-scene">
                    <div class="marketing-hero__audience-badge" aria-label="Skabt til frisører og saloner">
                        <span>Skabt til</span>
                        <strong>frisører og saloner</strong>
                    </div>

                    <div class="marketing-hero__browser">
                        <div class="marketing-hero__browser-body">
                            <img
                                src="{{ asset('images/sales/plateweb-desktop-demo-screen1.png') }}"
                                alt="PlateWeb CMS dashboard"
                                class="marketing-hero__browser-image"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
