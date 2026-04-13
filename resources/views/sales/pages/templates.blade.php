@extends('sales.layouts.default')

@section('title', 'Vores priser')
@section('body-class', 'marketing-body marketing-body--templates')

@section('header')
    @include('sales.layouts.header')
@endsection

@php
    $guidePackages = collect($packages)->mapWithKeys(fn (array $package) => [
        $package['key'] => [
            'key' => $package['key'],
            'title' => $package['title'],
            'price' => $package['price'],
            'annualPrice' => $package['annual_price'],
            'priceSuffix' => $package['price_suffix'],
            'annualSuffix' => $package['annual_suffix'],
            'href' => $package['href'],
            'label' => $package['label'],
            'badge' => $package['badge'],
            'headline' => $package['headline'],
            'delivery' => $package['delivery'],
            'points' => $package['points'],
            'tone' => $package['tone'],
            'featured' => $package['featured'],
            'pricing' => $package['pricing'] ?? null,
            'visibleFields' => $package['visible_fields'] ?? null,
        ],
    ])->all();

    $defaultPackage = collect($packages)->firstWhere('key', 'scale') ?? collect($packages)->first();
@endphp

@section('main-content')
    <div class="pricing-page" x-data="pricingGuide()" x-init="hydrate($el.dataset.guidePackages)" data-guide-packages='@json($guidePackages)'>
        <section class="ui-section ui-section--tight pricing-hero">
            <div class="ui-shell pricing-hero__shell">
                <div class="pricing-hero__lead">
                    <div class="pricing-hero__column pricing-hero__column--content">
                        <div class="pricing-hero__intro" data-reveal style="--reveal-delay: 40ms;">
                            <p class="pricing-hero__eyebrow">Vælg pakke og se vejledende pris</p>
                            <h1 class="pricing-hero__title">
                                <span class="pricing-hero__title-line">Få en pris der matcher</span>
                                <span class="pricing-hero__title-line">jeres behov</span>
                            </h1>
                            <p class="pricing-hero__copy">
                                Vælg den retning der passer bedst, juster jeres setup og se hvordan prisen ændrer sig.
                                For booking-løsninger vægter antal bookinger mest, mens custom går direkte til tilbud.
                            </p>
                        </div>

                        <div class="pricing-hero__controls" data-reveal style="--reveal-delay: 110ms;">
                            <div class="pricing-guide">
                                <div class="pricing-guide__header">
                                    <p class="pricing-guide__eyebrow">Vejledende tilbud</p>
                                    <h2 class="pricing-guide__title">Byg jeres setup</h2>
                                </div>

                                <div class="pricing-guide__fields">
                                    <div class="pricing-guide__field pricing-guide__field--compact">
                                        <p class="pricing-guide__label">Jeres behov</p>

                                        <div class="pricing-guide__options" role="group" aria-label="Jeres behov">
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'launch' }" x-bind:aria-pressed="(journey === 'launch').toString()" x-on:click="journey = 'launch'">Kun hjemmeside</button>
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'scale' }" x-bind:aria-pressed="(journey === 'scale').toString()" x-on:click="journey = 'scale'">Hjemmeside inkl. booking</button>
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'signature' }" x-bind:aria-pressed="(journey === 'signature').toString()" x-on:click="journey = 'signature'">Custom</button>
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'platebook' }" x-bind:aria-pressed="(journey === 'platebook').toString()" x-on:click="journey = 'platebook'">Kun booking</button>
                                        </div>
                                    </div>

                                    <div class="pricing-guide__metrics">
                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('locations')" x-bind:style="sliderStyle('locations')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label">Antal lokationer</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('locations')">1</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="locations" x-bind:min="sliderMin('locations')" x-bind:max="sliderMax('locations')" x-bind:step="sliderStep('locations')" value="1" aria-label="Antal lokationer">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('locations')">1</span>
                                                <span x-text="sliderScaleEnd('locations')">10+</span>
                                            </div>
                                        </div>

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('staff')" x-bind:style="sliderStyle('staff')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label">Antal medarbejdere</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('staff')">4</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="staff" x-bind:min="sliderMin('staff')" x-bind:max="sliderMax('staff')" x-bind:step="sliderStep('staff')" value="4" aria-label="Antal medarbejdere">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('staff')">1</span>
                                                <span x-text="sliderScaleEnd('staff')">100+</span>
                                            </div>
                                        </div>

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('bookings')" x-bind:style="sliderStyle('bookings')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label">Antal årlige bookinger</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('bookings')">300</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="bookings" x-bind:min="sliderMin('bookings')" x-bind:max="sliderMax('bookings')" x-bind:step="sliderStep('bookings')" value="300" aria-label="Antal årlige bookinger">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('bookings')">50</span>
                                                <span x-text="sliderScaleEnd('bookings')">5.000+</span>
                                            </div>
                                        </div>

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('sections')" x-bind:style="sliderStyle('sections')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label">Antal sektioner på hjemmeside</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('sections')">3</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="sections" x-bind:min="sliderMin('sections')" x-bind:max="sliderMax('sections')" x-bind:step="sliderStep('sections')" value="3" aria-label="Antal sektioner på hjemmeside">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('sections')">1</span>
                                                <span x-text="sliderScaleEnd('sections')">5+</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pricing-guide__footer">
                                    <div class="pricing-guide__summary" aria-live="polite">
                                        <p class="pricing-guide__summary-eyebrow">Vejledende pris</p>
                                        <div class="pricing-guide__summary-meta">
                                            <div class="pricing-guide__summary-copy">
                                                <p class="pricing-guide__summary-title" x-text="activePackage().title">{{ $defaultPackage['title'] ?? '' }}</p>
                                                <p class="pricing-guide__summary-note" x-text="activePackage().priceNote">{{ $defaultPackage['price_suffix'] ?? '' }}</p>
                                            </div>
                                            <p class="pricing-guide__summary-price" x-text="activePackage().price">{{ $defaultPackage['price'] ?? '' }}</p>
                                        </div>
                                        <p class="pricing-guide__summary-detail" x-text="activePackage().detail">1 lokation · 4 medarbejdere · 300 bookinger/år</p>
                                    </div>

                                    <div class="pricing-guide__footer-actions">
                                        <a class="ui-button ui-button--ink pricing-guide__jump" x-bind:href="activePackage().href" x-text="activePackage().label" href="{{ $defaultPackage['href'] ?? '#' }}">{{ $defaultPackage['label'] ?? 'Se løsning' }}</a>
                                        <p class="pricing-billing-note" x-text="activePackage().billingNote">Prisen justeres efter behov og brug · ekskl. moms.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pricing-hero__column pricing-hero__column--offer" data-reveal style="--reveal-delay: 180ms;">
                        <article
                            id="pricing-package-scale"
                            class="ui-card ui-card--hover package-card pricing-hero__offer-card"
                            x-bind:id="`pricing-package-${activePackage().key}`"
                            x-bind:class="packageCardClassList()"
                        >
                            <div class="package-card__top">
                                <span class="package-card__badge" x-text="activePackage().badge">{{ $defaultPackage['badge'] ?? '' }}</span>
                            </div>

                            <div class="package-card__heading">
                                <h2 class="package-card__title" x-text="activePackage().title">{{ $defaultPackage['title'] ?? '' }}</h2>

                                <div class="package-card__price-block">
                                    <p class="package-card__price" x-text="activePackage().price">{{ $defaultPackage['price'] ?? '' }}</p>
                                    <p class="package-card__price-note" x-text="activePackage().priceNote">{{ $defaultPackage['price_suffix'] ?? '' }}</p>
                                    <p class="package-card__delivery" x-text="activePackage().delivery">{{ $defaultPackage['delivery'] ?? '' }}</p>
                                </div>
                            </div>

                            <p class="package-card__headline" x-text="activePackage().headline">{{ $defaultPackage['headline'] ?? '' }}</p>
                            <p class="package-card__setup" x-text="activePackage().detail">1 lokation · 4 medarbejdere · 300 bookinger/år</p>

                            <ul class="package-card__points" x-html="activePackagePointsMarkup()">
                                @foreach (($defaultPackage['points'] ?? []) as $point)
                                    <li class="package-card__point">{{ $point }}</li>
                                @endforeach
                            </ul>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="ui-section ui-section--compact pricing-compare-section">
            <div class="ui-shell pricing-compare-shell">
                <div class="pricing-compare-intro" data-reveal style="--reveal-delay: 40ms;">
                    <p class="pricing-compare-intro__eyebrow">Sammenlign spor</p>
                    <h2 class="pricing-compare-intro__title">Hvad indgår i de forskellige retninger?</h2>
                    <p class="pricing-compare-intro__copy">
                        Brug oversigten som pejlemærke. Det endelige setup og den endelige pris bliver først bekræftet,
                        når vi har gennemgået jeres behov.
                    </p>
                </div>

                <div data-reveal style="--reveal-delay: 120ms;">
                    @include('sales.partials.plans-comparison', ['packages' => $packages, 'comparisonRows' => $comparisonRows])
                </div>
            </div>
        </section>
    </div>
@endsection
