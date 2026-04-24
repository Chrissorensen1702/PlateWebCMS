@extends('sales.layouts.default')

@section('title', 'Priser')
@section('body-class', 'marketing-body marketing-body--templates')

@section('header')
    @include('sales.layouts.header')
@endsection

@php
    $starterFeatures = [
        'Professionel hjemmeside',
        'Nem redigering i kunde-CMS',
        'Online booking med PlateBook',
        'Overblik over aktiviteter og bookinger',
        'Hosting og SSL',
        'Domæne og DNS opsætning',
        'Automatisk backup',
        'SMS-påmindelser',
    ];

    $proFeatures = [
        'Alt fra Starter-pakken',
        'Vagtplan inkl. CSV-eksport',
        'Kompetancer og medarbejdere',
        'Integreret betaling',
        'Gratis SMS-påmindelser',
        'Fuld statistik',
    ];

    $connectFeatures = [
        'Alt fra Pro-pakken',
        'Egen kundeapp',
        'Loyalitetsprogrammer',
        'Pointsystem',
        'Klippekort',
        'Tap-to-pay',
    ];
@endphp

@section('main-content')
    <section class="ui-section ui-section--tight pricing-overview">
        <div class="ui-shell" id="pricing-guide">
            <div
                class="pricing-overview__shell"
                x-data="{
                    billing: 'monthly',
                    billingIndicatorLeft: 0,
                    billingIndicatorWidth: 0,
                    init() {
                        this.$nextTick(() => this.updateBillingIndicator());
                        window.addEventListener('resize', () => this.updateBillingIndicator(), { passive: true });
                    },
                    setBilling(value) {
                        this.billing = value;
                        this.$nextTick(() => this.updateBillingIndicator());
                    },
                    updateBillingIndicator() {
                        const activeRef = this.billing === 'annual' ? this.$refs.billingAnnual : this.$refs.billingMonthly;

                        if (! activeRef) {
                            return;
                        }

                        this.billingIndicatorLeft = activeRef.offsetLeft;
                        this.billingIndicatorWidth = activeRef.offsetWidth;
                    },
                }"
            >
                <div class="pricing-overview__intro">
                    <p class="section-heading__kicker">Priser og retninger</p>
                    <h1 class="pricing-overview__title">Det skal være let at vælge rigtigt</h1>
                    <p class="pricing-overview__copy">
                        Hos os ved vi, at ikke alle virksomheder har brug for det samme. Derfor har vi lavet forskellige pakker med priser, der følger størrelsen på dit setup.
                    </p>
                </div>

                <div class="pricing-billing-toggle pricing-billing-toggle--plans" role="group" aria-label="Betalingsperiode">
                    <p class="pricing-billing-toggle__hint">Spar 15% ved årlig betaling</p>

                    <div class="pricing-billing-toggle__controls">
                        <span
                            class="pricing-billing-toggle__indicator"
                            aria-hidden="true"
                            x-bind:style="`width: ${billingIndicatorWidth}px; transform: translate3d(${billingIndicatorLeft}px, 0, 0); opacity: ${billingIndicatorWidth ? 1 : 0};`"
                        ></span>

                        <button
                            type="button"
                            class="pricing-billing-toggle__option"
                            x-ref="billingMonthly"
                            x-bind:class="{ 'is-active': billing === 'monthly' }"
                            x-bind:aria-pressed="billing === 'monthly' ? 'true' : 'false'"
                            x-on:click="setBilling('monthly')"
                        >
                            Månedligt
                        </button>

                        <button
                            type="button"
                            class="pricing-billing-toggle__option"
                            x-ref="billingAnnual"
                            x-bind:class="{ 'is-active': billing === 'annual' }"
                            x-bind:aria-pressed="billing === 'annual' ? 'true' : 'false'"
                            x-on:click="setBilling('annual')"
                        >
                            Årligt
                        </button>
                    </div>
                </div>

                <div class="pricing-plan-grid">
                    <article class="ui-card pricing-plan-card pricing-plan-card--start">
                        <div class="pricing-plan-card__header">
                            <h2 class="pricing-plan-card__title">Starter</h2>
                        </div>

                        <div class="pricing-plan-card__price-block">
                            <div class="pricing-plan-card__price-row pricing-plan-card__price-row--free">
                                <span class="pricing-plan-card__price">0,-</span>
                                <span class="pricing-plan-card__price-note">ingen binding<br>gratis oprettelse</span>
                            </div>
                        </div>

                        <p class="pricing-plan-card__summary">
                            En gratis pakke til dig som vil hurtigt igang, uden tunge omkostninger.
                        </p>

                        <div class="pricing-plan-card__cta">
                            <a href="{{ route('contact') }}" class="ui-button ui-button--outline">Vælg Starter</a>
                        </div>

                        <ul class="pricing-plan-card__features">
                            @foreach ($starterFeatures as $featureLabel)
                                <li class="pricing-plan-card__feature">
                                    <span class="pricing-plan-card__status" aria-hidden="true"></span>
                                    <span>{{ $featureLabel }}</span>
                                </li>
                            @endforeach
                        </ul>


                    </article>

                    <article class="ui-card pricing-plan-card pricing-plan-card--featured">
                        <div class="pricing-plan-card__header">
                            <h2 class="pricing-plan-card__title">Pro</h2>
                        </div>

                        <div class="pricing-plan-card__price-block">
                            <div class="pricing-plan-card__price-row">
                                <span class="pricing-plan-card__price" x-text="billing === 'monthly' ? '149,-' : '127,-'">149,-</span>
                                <span class="pricing-plan-card__price-note">ekskl. moms<br>pr. måned pr. medarbejder</span>
                            </div>
                            <p class="pricing-plan-card__billing-hint" x-cloak x-show="billing === 'annual'">Ved årlig betaling</p>
                        </div>

                        <p class="pricing-plan-card__summary">
                            Et setup til dig med flere medarbejdere, kompetancer, og som ønsker at samle alt i é setup.
                        </p>

                        <div class="pricing-plan-card__cta">
                            <a href="{{ route('contact') }}" class="ui-button ui-button--ink">Vælg Pro</a>
                        </div>

                        <ul class="pricing-plan-card__features">
                            @foreach ($proFeatures as $featureLabel)
                                <li class="pricing-plan-card__feature">
                                    <span class="pricing-plan-card__status" aria-hidden="true"></span>
                                    <span>{{ $featureLabel }}</span>
                                </li>
                            @endforeach
                        </ul>


                    </article>

                    <article class="ui-card pricing-plan-card pricing-plan-card--connect">
                        <div class="pricing-plan-card__header">
                            <h2 class="pricing-plan-card__title">Connect</h2>
                        </div>

                        <div class="pricing-plan-card__price-block">
                            <div class="pricing-plan-card__price-row">
                                <span class="pricing-plan-card__price" x-text="billing === 'monthly' ? '169,-' : '144,-'">169,-</span>
                                <span class="pricing-plan-card__price-note">ekskl. moms<br>pr. måned pr. medarbejder</span>
                            </div>
                            <p class="pricing-plan-card__billing-hint" x-cloak x-show="billing === 'annual'">Ved årlig betaling</p>
                            <p class="pricing-plan-card__price-addon">+ 3 kr. pr. aktiv app-bruger / md.</p>
                        </div>

                        <p class="pricing-plan-card__summary">
                            Saml hele kundeoplevelsen, og skab relationer til dine kunder gennem loyaltitetsprogrammer.
                        </p>

                        <div class="pricing-plan-card__cta">
                            <a href="{{ route('contact') }}" class="ui-button ui-button--outline">Vælg Connect</a>
                        </div>

                        <ul class="pricing-plan-card__features">
                            @foreach ($connectFeatures as $featureLabel)
                                <li class="pricing-plan-card__feature">
                                    <span class="pricing-plan-card__status" aria-hidden="true"></span>
                                    <span>{{ $featureLabel }}</span>
                                </li>
                            @endforeach
                        </ul>


                    </article>
                </div>

                <article class="pricing-custom-note">
                    <div class="pricing-custom-note__copy">
                        <p class="section-heading__kicker">Custom design</p>
                        <h2 class="pricing-custom-note__title">Vil du have noget, der føles mere som dit eget?</h2>
                        <p class="pricing-custom-note__text">
                            Vi kan stadig bygge custom design ovenpå PlateWeb, hvis du vil have et mere unikt visuelt udtryk eller en løsning,
                            der kræver mere end standardpakken.
                        </p>
                    </div>

                    <div class="pricing-custom-note__actions">
                        <a href="{{ route('contact') }}" class="ui-button ui-button--ink">Tal med os om custom</a>
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection
