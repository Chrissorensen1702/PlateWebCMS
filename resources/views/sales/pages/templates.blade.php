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
        ],
    ])->all();

    $defaultPackage = collect($packages)->firstWhere('key', 'scale') ?? collect($packages)->first();
    $launchPackage = collect($packages)->firstWhere('key', 'launch') ?? $defaultPackage;
@endphp

@section('main-content')
    <div class="pricing-page" x-data="pricingGuide()" x-init="hydrate($el.dataset.guidePackages)" data-guide-packages='@json($guidePackages)'>
        <section class="ui-section ui-section--tight pricing-hero">
            <div class="ui-shell pricing-hero__shell">
                <div class="pricing-hero__lead">
                    <div class="pricing-hero__column pricing-hero__column--content">
                        <div class="pricing-hero__intro" data-reveal style="--reveal-delay: 40ms;">
                            <p class="pricing-hero__eyebrow">Vejledende tilbud og gratis prøve</p>
                            <h1 class="pricing-hero__title">
                                <span class="pricing-hero__title-line">Få en pris der matcher</span>
                                <span class="pricing-hero__title-line">jeres behov</span>
                            </h1>
                            <p class="pricing-hero__copy">
                                Vælg den retning der passer bedst, få en vejledende pris med det samme og start i 30 dage
                                gratis. Vi bekræfter den endelige løsning og pris bagefter.
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
                                        <div class="pricing-guide__field pricing-guide__field--slider" x-bind:style="sliderStyle('locations')">
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

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-bind:style="sliderStyle('staff')">
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

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-bind:style="sliderStyle('bookings')">
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

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-bind:style="sliderStyle('sections')">
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

                                <div class="pricing-guide__result" aria-live="polite">
                                    <div class="pricing-guide__result-copywrap">
                                        <p class="pricing-guide__result-eyebrow">Anbefalet tilbud</p>
                                        <div class="pricing-guide__result-meta">
                                            <div class="pricing-guide__result-priceblock">
                                                <p class="pricing-guide__result-title" x-text="recommendation().title">{{ $defaultPackage['title'] ?? '' }}</p>
                                                <p class="pricing-guide__result-copy" x-text="recommendation().reason">Det bedste match, hvis I vil samle hjemmeside, indhold og booking i én løsning og starte med et vejledende tilbud.</p>
                                            </div>
                                            <div class="pricing-guide__result-priceblock pricing-guide__result-priceblock--right">
                                                <p class="pricing-guide__result-price" x-text="recommendation().price">{{ $defaultPackage['price'] ?? '' }}</p>
                                                <p class="pricing-guide__result-note" x-text="recommendation().priceNote">{{ $defaultPackage['price_suffix'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="pricing-guide__result-trial">
                                        30 dages gratis prøve. Ingen binding før vi har bekræftet den endelige løsning.
                                    </p>
                                </div>

                                <div class="pricing-guide__footer">
                                    <a class="ui-button ui-button--ink pricing-guide__jump" x-bind:href="recommendation().href" x-text="recommendation().label" href="{{ $defaultPackage['href'] ?? '#' }}">{{ $defaultPackage['label'] ?? 'Se løsning' }}</a>
                                    <p class="pricing-billing-note">Alle priser er vejledende og ekskl. moms.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pricing-hero__column pricing-hero__column--offer" data-reveal style="--reveal-delay: 180ms;">
                        <article id="pricing-package-launch" class="ui-card ui-card--hover package-card package-card--launch pricing-hero__offer-card">
                            <div class="package-card__top">
                                <span class="package-card__badge">{{ $launchPackage['badge'] ?? '' }}</span>
                            </div>

                            <div class="package-card__heading">
                                <h2 class="package-card__title">{{ $launchPackage['title'] ?? '' }}</h2>

                                <div class="package-card__price-block">
                                    <p class="package-card__price">{{ $launchPackage['price'] ?? '' }}</p>
                                    <p class="package-card__price-note">{{ $launchPackage['price_suffix'] ?? '' }}</p>
                                    <p class="package-card__delivery">{{ $launchPackage['delivery'] ?? '' }}</p>
                                </div>
                            </div>

                            <p class="package-card__headline">{{ $launchPackage['headline'] ?? '' }}</p>

                            <a href="{{ $launchPackage['href'] ?? '#' }}" class="ui-button ui-button--ink package-card__action">
                                {{ $launchPackage['label'] ?? 'Se løsning' }}
                            </a>

                            <ul class="package-card__points">
                                @foreach (($launchPackage['points'] ?? []) as $point)
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
