@php
    $benefitTabs = [
        [
            'key' => 'solution',
            'label' => 'Løsningen',
            'eyebrow' => 'Samlet platform',
            'title' => 'Vi har samlet alt i én løsning!',
            'description' => 'PlateWeb samler hjemmeside, booking, betaling, vagtplan og app i én platform, så du slipper for at få flere systemer til at spille sammen.',
            'lottie' => 'lotties/lottie-test2.json',
            'image_alt' => 'PlateWeb animation vist i Lottie',
            'items' => [
                [
                    'title' => 'Én samlet arbejdsgang',
                    'description' => 'Din hjemmeside, booking og daglige drift hænger sammen fra starten.',
                ],
                [
                    'title' => 'Mindre manuelt arbejde',
                    'description' => 'Færre løse værktøjer betyder færre fejl og mindre tid brugt på administration.',
                ],
                [
                    'title' => 'Bygget til vækst',
                    'description' => 'Start simpelt og udvid med flere moduler, når behovet opstår.',
                ],
            ],
        ],
        [
            'key' => 'website',
            'label' => 'Hjemmeside',
            'eyebrow' => 'Nem designer',
            'title' => 'Lav en professionel hjemmeside uden bøvl',
            'description' => 'Design din egen kundevendte hjemmeside med tydelige sektioner, billeder, tekster og ydelser, der kan tilpasses din forretning.',
            'image' => 'images/sales/maaneskoen-home-preview.png',
            'image_alt' => 'Eksempel på en kundevendt hjemmeside bygget med PlateWeb',
            'items' => [
                [
                    'title' => 'Modulopbygget side',
                    'description' => 'Sæt siden sammen af sektioner, der passer til dine services og dit brand.',
                ],
                [
                    'title' => 'CMS til indhold',
                    'description' => 'Opdater tekster, billeder og informationer uden at vente på en udvikler.',
                ],
                [
                    'title' => 'Klar til booking',
                    'description' => 'Kunderne kan gå direkte fra præsentation af din ydelse til booking.',
                ],
            ],
        ],
        [
            'key' => 'booking',
            'label' => 'Booking',
            'eyebrow' => 'Direkte bookinger',
            'title' => 'Et bookingsystem der hænger sammen med din side',
            'description' => 'Lad kunder booke direkte på din hjemmeside, mens du får et klart overblik over kalender, tider og kapacitet.',
            'image' => 'images/sales/test2-home-preview-cutout.png',
            'image_alt' => 'PlateWeb bookingkalender vist i systemet',
            'items' => [
                [
                    'title' => 'Booking på egen hjemmeside',
                    'description' => 'Kunderne bliver på din side og booker uden at blive sendt videre.',
                ],
                [
                    'title' => 'Overskuelig kalender',
                    'description' => 'Se bookinger, tider og aktivitet i et samlet system.',
                ],
                [
                    'title' => 'Klar til flere medarbejdere',
                    'description' => 'Bookinger kan kobles til medarbejdere, ydelser og tilgængelighed.',
                ],
            ],
        ],
        [
            'key' => 'payment',
            'label' => 'Betaling',
            'eyebrow' => 'Integreret betaling',
            'title' => 'Tag imod betaling samme sted som kunden booker',
            'description' => 'Betaling kan kobles direkte på bookingflowet, så booking, betaling og overblik hænger bedre sammen i hverdagen.',
            'image' => 'images/sales/plateweb-desktop-demo-screen1.png',
            'image_alt' => 'PlateWeb vist på en desktop-skærm',
            'items' => [
                [
                    'title' => 'Betaling i samme flow',
                    'description' => 'Gør det nemmere for kunden at gennemføre fra booking til betaling.',
                ],
                [
                    'title' => 'Mindre manuel opfølgning',
                    'description' => 'Færre løse betalinger og færre manuelle tjek i hverdagen.',
                ],
                [
                    'title' => 'Bedre overblik',
                    'description' => 'Saml betalinger og bookinger, så økonomien bliver lettere at følge.',
                ],
            ],
        ],
        [
            'key' => 'employee',
            'label' => 'Medarbejder',
            'eyebrow' => 'Vagtplan og drift',
            'title' => 'Hold styr på medarbejdere, vagter og tilgængelighed',
            'description' => 'Planlæg medarbejdere og tilgængelighed, så kunderne booker de rigtige tider hos de rigtige personer.',
            'image' => 'images/sales/muckop1.png',
            'image_alt' => 'Oversigt over vagter og medarbejdere i PlateWeb',
            'items' => [
                [
                    'title' => 'Vagtplan',
                    'description' => 'Saml medarbejdernes vagter og tilgængelighed ét sted.',
                ],
                [
                    'title' => 'Kompetencer og ydelser',
                    'description' => 'Kobl medarbejdere til de behandlinger eller services de kan udføre.',
                ],
                [
                    'title' => 'Overblik for teamet',
                    'description' => 'Gør det lettere for medarbejderne at følge med i kommende bookinger.',
                ],
            ],
        ],
        [
            'key' => 'customer-app',
            'label' => 'Kundeapp',
            'eyebrow' => 'App til kunder og medarbejdere',
            'title' => 'Giv kunder og medarbejdere adgang på farten',
            'description' => 'Med app-muligheder kan både kunder og medarbejdere få hurtig adgang til booking, overblik og relevante funktioner fra mobilen.',
            'image' => 'images/sales/mobilapp-intro.png',
            'image_alt' => 'PlateWeb mobilapp vist på telefoner',
            'items' => [
                [
                    'title' => 'Kundeapp',
                    'description' => 'Gør det nemt for kunderne at booke og følge med fra mobilen.',
                ],
                [
                    'title' => 'Medarbejderapp',
                    'description' => 'Giv medarbejdere adgang til deres tider og relevante informationer.',
                ],
                [
                    'title' => 'Samme platform',
                    'description' => 'Appen hænger sammen med hjemmeside, booking og administration.',
                ],
            ],
        ],
    ];
@endphp

<section id="produkt-bookingsystem" class="ui-section ui-section--compact marketing-benefits-section">
    <div id="hvorfor-vaelge-os"></div>

    <div class="ui-shell marketing-benefits-section__shell">
        <div class="marketing-benefits-intro" data-reveal style="--reveal-delay: 40ms;">
            <p class="marketing-benefits-kicker">En samlet løsning</p>
            <h2 class="marketing-benefits-title">Alt du skal bruge til hjemmeside, booking og drift</h2>
            <p class="marketing-benefits-copy">
                Hos os kombinerer vi hjemmeside, kunde-CMS, bookingsystem, vagtplan, betaling og app til både kunder og medarbejdere i én samlet platform. Det skal være nemt, effektivt - og det skal gavne din forretning!
            </p>
        </div>

        <div
            class="marketing-benefits-tabs"
            x-data="{
                active: '{{ $benefitTabs[0]['key'] }}',
                tabs: @js(array_column($benefitTabs, 'key')),
                indicatorLeft: 0,
                indicatorWidth: 0,
                direction: 1,
                init() {
                    this.$nextTick(() => this.updateIndicator());
                    window.addEventListener('resize', () => this.updateIndicator(), { passive: true });
                },
                activate(key) {
                    const currentIndex = this.tabs.indexOf(this.active);
                    const nextIndex = this.tabs.indexOf(key);

                    this.direction = nextIndex >= currentIndex ? 1 : -1;
                    this.active = key;
                    this.$nextTick(() => this.updateIndicator());
                },
                move(direction) {
                    const currentIndex = this.tabs.indexOf(this.active);
                    const nextIndex = (currentIndex + direction + this.tabs.length) % this.tabs.length;

                    this.activate(this.tabs[nextIndex]);
                    this.$nextTick(() => {
                        this.$refs[`benefitTab${nextIndex}`]?.focus();
                    });
                },
                updateIndicator() {
                    const activeIndex = this.tabs.indexOf(this.active);
                    const activeTab = this.$refs[`benefitTab${activeIndex}`];

                    if (! activeTab) {
                        return;
                    }

                    this.indicatorLeft = activeTab.offsetLeft;
                    this.indicatorWidth = activeTab.offsetWidth;
                },
            }"
        >
            <div class="marketing-benefits-tabs__list-wrap" data-reveal style="--reveal-delay: 110ms;">
                <div role="tablist" aria-label="Fordele ved PlateWeb" class="marketing-benefits-tabs__list">
                    <span
                        class="marketing-benefits-tabs__indicator"
                        aria-hidden="true"
                        x-bind:style="`width: ${indicatorWidth}px; transform: translate3d(${indicatorLeft}px, 0, 0); opacity: ${indicatorWidth ? 1 : 0};`"
                    ></span>

                    @foreach ($benefitTabs as $tabIndex => $tab)
                        <button
                            type="button"
                            role="tab"
                            id="marketing-benefits-tab-{{ $tab['key'] }}"
                            class="marketing-benefits-tabs__tab"
                            x-ref="benefitTab{{ $tabIndex }}"
                            x-on:click="activate('{{ $tab['key'] }}')"
                            x-on:keydown.arrow-right.prevent="move(1)"
                            x-on:keydown.arrow-left.prevent="move(-1)"
                            x-bind:class="{ 'is-active': active === '{{ $tab['key'] }}' }"
                            x-bind:aria-selected="active === '{{ $tab['key'] }}' ? 'true' : 'false'"
                            x-bind:tabindex="active === '{{ $tab['key'] }}' ? 0 : -1"
                            aria-controls="marketing-benefits-panel-{{ $tab['key'] }}"
                        >
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="marketing-benefits-tabs__panels" data-reveal style="--reveal-delay: 170ms;">
                @foreach ($benefitTabs as $tab)
                    <article
                        role="tabpanel"
                        id="marketing-benefits-panel-{{ $tab['key'] }}"
                        class="marketing-benefits-panel"
                        aria-labelledby="marketing-benefits-tab-{{ $tab['key'] }}"
                        x-bind:hidden="active !== '{{ $tab['key'] }}'"
                        x-bind:aria-hidden="active !== '{{ $tab['key'] }}' ? 'true' : 'false'"
                        x-bind:class="{ 'is-active': active === '{{ $tab['key'] }}', 'is-hidden': active !== '{{ $tab['key'] }}' }"
                        x-bind:style="`--benefits-panel-enter-x: ${direction > 0 ? '1.25rem' : '-1.25rem'}; --benefits-panel-enter-x-soft: ${direction > 0 ? '0.75rem' : '-0.75rem'};`"
                        x-cloak
                    >
                        <div class="marketing-benefits-panel__inner">
                            <div class="marketing-benefits-panel__media-column">
                                <figure class="marketing-benefits-panel__visual marketing-benefits-panel__visual--{{ $tab['key'] }}">
                                    @if (!empty($tab['lottie']))
                                        <div
                                            class="marketing-benefits-panel__lottie"
                                            data-lottie-src="{{ asset($tab['lottie']) }}"
                                            data-lottie-loop="true"
                                            aria-label="{{ $tab['image_alt'] }}"
                                        ></div>
                                    @else
                                        <img
                                            src="{{ asset($tab['image']) }}"
                                            alt="{{ $tab['image_alt'] }}"
                                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                            decoding="async"
                                        >
                                    @endif
                                </figure>
                            </div>

                            <div class="marketing-benefits-panel__content-column">
                                <div class="marketing-benefits-panel__content">
                                    <div class="marketing-benefits-panel__heading">
                                        <p class="marketing-benefits-panel__eyebrow">{{ $tab['eyebrow'] }}</p>
                                        <h3>{{ $tab['title'] }}</h3>
                                        <p>{{ $tab['description'] }}</p>
                                    </div>

                                    <div class="marketing-benefits-feature-list">
                                        @foreach ($tab['items'] as $item)
                                            <div class="marketing-benefits-feature">
                                                <span class="marketing-benefits-feature__check" aria-hidden="true"></span>
                                                <div>
                                                    <h4>{{ $item['title'] }}</h4>
                                                    <p>{{ $item['description'] }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
