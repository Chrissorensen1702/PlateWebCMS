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
            'supportCopy' => $package['support_copy'] ?? '',
            'points' => $package['points'],
            'footnote' => $package['footnote'] ?? null,
            'footnotePoint' => $package['footnote_point'] ?? null,
            'tone' => $package['tone'],
            'featured' => $package['featured'],
            'pricing' => $package['pricing'] ?? null,
            'visibleFields' => $package['visible_fields'] ?? null,
        ],
    ])->all();

    $defaultPackage = collect($packages)->firstWhere('key', $initialSelection['package_key'] ?? 'scale')
        ?? collect($packages)->firstWhere('key', 'scale')
        ?? collect($packages)->first();

    $defaultHeadlineMarkup = str_replace(
        'PlateBook',
        '<span class="package-card__headline-brand"><span class="package-card__headline-brand-plate">Plate</span><span class="package-card__headline-brand-book">Book</span></span>',
        e($defaultPackage['headline'] ?? ''),
    );
@endphp

@section('main-content')
    <div
        class="pricing-page"
        x-data="pricingGuide()"
        x-init="hydrate($el.dataset.guidePackages, $el.dataset.guideSelection)"
        data-guide-packages='@json($guidePackages)'
        data-guide-selection='@json($initialSelection)'
        data-authenticated="{{ auth()->check() ? '1' : '0' }}"
    >
        <section class="ui-section ui-section--tight pricing-hero">
            <div class="ui-shell pricing-hero__shell">
                <div class="pricing-hero__lead">
                    <div class="pricing-hero__column pricing-hero__column--content">
                        <div class="pricing-hero__intro" data-reveal style="--reveal-delay: 40ms;">
                            <p class="pricing-hero__eyebrow">Vælg pakke og se vejledende pris</p>
                            <h1 class="pricing-hero__title">
                                <span class="pricing-hero__title-line">En løsning, der passer</span>
                                <span class="pricing-hero__title-line">til jeres behov</span>
                            </h1>
                            <p class="pricing-hero__copy">
                                Vi ved, at en nyopstartet virksomhed ikke har samme behov eller budget som en etableret forretning.
                                Derfor afhænger prisen af det setup, I faktisk har brug for, så I ikke betaler for noget unødvendigt.
                            </p>
                        </div>

                        <div class="pricing-hero__controls" data-reveal style="--reveal-delay: 110ms;">
                            <div class="pricing-guide" id="pricing-guide">
                                <div class="pricing-guide__header">
                                    <h2 class="pricing-guide__title">Byg jeres setup</h2>

                                    <div class="pricing-billing-toggle pricing-billing-toggle--guide">
                                        <button
                                            type="button"
                                            class="pricing-billing-toggle__button"
                                            x-bind:class="{ 'pricing-billing-toggle__button--active': annualBilling }"
                                            x-bind:aria-pressed="annualBilling.toString()"
                                            x-on:click="annualBilling = !annualBilling"
                                        >
                                            <span class="pricing-billing-toggle__label">Månedlig</span>
                                            <span class="pricing-billing-toggle__track" aria-hidden="true">
                                                <span class="pricing-billing-toggle__thumb" x-bind:class="{ 'pricing-billing-toggle__thumb--active': annualBilling }"></span>
                                            </span>
                                            <span class="pricing-billing-toggle__label">Årlig</span>
                                            <span class="pricing-billing-toggle__saving">12% rabat</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="pricing-guide__fields">
                                    <div class="pricing-guide__field pricing-guide__field--compact">
                                        <p class="pricing-guide__label">Jeres behov</p>

                                        <div class="pricing-guide__options" role="group" aria-label="Jeres behov">
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'launch' }" x-bind:aria-pressed="(journey === 'launch').toString()" x-on:click="journey = 'launch'">Kun hjemmeside</button>
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'scale' }" x-bind:aria-pressed="(journey === 'scale').toString()" x-on:click="journey = 'scale'">Hjemmeside inkl. booking</button>
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'signature' }" x-bind:aria-pressed="(journey === 'signature').toString()" x-on:click="journey = 'signature'">Signature</button>
                                            <button type="button" class="pricing-guide__option" x-bind:class="{ 'pricing-guide__option--active': journey === 'platebook' }" x-bind:aria-pressed="(journey === 'platebook').toString()" x-on:click="journey = 'platebook'">Kun booking</button>
                                        </div>
                                    </div>

                                    <div class="pricing-guide__metrics">
                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('locations')" x-bind:style="sliderStyle('locations')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label" x-text="fieldLabel('locations')">Antal lokationer</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('locations')">1</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="locations" x-bind:min="sliderMin('locations')" x-bind:max="sliderMax('locations')" x-bind:step="sliderStep('locations')" value="1" x-bind:aria-label="fieldLabel('locations')">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('locations')">1</span>
                                                <span x-text="sliderScaleEnd('locations')">10+</span>
                                            </div>
                                        </div>

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('staff')" x-bind:style="sliderStyle('staff')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label" x-text="fieldLabel('staff')">Antal medarbejdere</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('staff')">1</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="staff" x-bind:min="sliderMin('staff')" x-bind:max="sliderMax('staff')" x-bind:step="sliderStep('staff')" value="1" x-bind:aria-label="fieldLabel('staff')">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('staff')">1</span>
                                                <span x-text="sliderScaleEnd('staff')">100+</span>
                                            </div>
                                        </div>

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('bookings')" x-bind:style="sliderStyle('bookings')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label" x-text="fieldLabel('bookings')">Antal årlige bookinger</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('bookings')">300</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="bookings" x-bind:min="sliderMin('bookings')" x-bind:max="sliderMax('bookings')" x-bind:step="sliderStep('bookings')" value="300" x-bind:aria-label="fieldLabel('bookings')">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('bookings')">50</span>
                                                <span x-text="sliderScaleEnd('bookings')">5.000+</span>
                                            </div>
                                        </div>

                                        <div class="pricing-guide__field pricing-guide__field--slider" x-cloak x-show="fieldVisible('sections')" x-bind:style="sliderStyle('sections')">
                                            <div class="pricing-guide__field-head">
                                                <p class="pricing-guide__label" x-text="fieldLabel('sections')">Antal sektioner på hjemmeside</p>
                                                <p class="pricing-guide__metric" x-text="sliderValue('sections')">3</p>
                                            </div>
                                            <input type="range" class="pricing-guide__range" x-model.number="sections" x-bind:min="sliderMin('sections')" x-bind:max="sliderMax('sections')" x-bind:step="sliderStep('sections')" value="3" x-bind:aria-label="fieldLabel('sections')">
                                            <div class="pricing-guide__scale">
                                                <span x-text="sliderScaleStart('sections')">1</span>
                                                <span x-text="sliderScaleEnd('sections')">5+</span>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="pricing-guide__field pricing-guide__field--toggle"
                                            x-cloak
                                            x-show="fieldVisible('lead_module')"
                                            x-bind:class="{ 'pricing-guide__field--toggle-active': lead_module }"
                                            x-on:click="toggleField('lead_module')"
                                        >
                                            <span class="pricing-guide__toggle-copy">
                                                <span class="pricing-guide__label" x-text="fieldLabel('lead_module')">Ønskes nyhedsbrev- og leadmodul?</span>
                                                <span class="pricing-guide__hint" x-text="fieldHint('lead_module')">Gør det nemt at samle leads og nyhedsbrevs-tilmeldinger.</span>
                                            </span>
                                            <span class="pricing-guide__toggle-state" x-text="toggleValue('lead_module')">Nej</span>
                                        </button>

                                        <div
                                            class="pricing-guide__field pricing-guide__field--choice"
                                            x-cloak
                                            x-show="fieldVisible('traffic_tier')"
                                        >
                                            <div class="pricing-guide__toggle-copy">
                                                <span class="pricing-guide__label" x-text="fieldLabel('traffic_tier')">Forventet trafik</span>
                                                <span class="pricing-guide__hint" x-text="fieldHint('traffic_tier')">Vælg det niveau, der passer bedst til jeres forventede besøgstal.</span>
                                            </div>

                                            <div class="pricing-guide__choice-options">
                                                <template x-for="option in trafficTierOptions()" :key="option.value">
                                                    <button
                                                        type="button"
                                                        class="pricing-guide__choice-option"
                                                        x-bind:class="{ 'pricing-guide__choice-option--active': traffic_tier === option.value }"
                                                        x-on:click="traffic_tier = option.value"
                                                    >
                                                        <span class="pricing-guide__choice-label" x-text="option.label"></span>
                                                        <span class="pricing-guide__choice-hint" x-text="option.hint"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="pricing-guide__field pricing-guide__field--toggle"
                                            x-cloak
                                            x-show="fieldVisible('seo_copy')"
                                            x-bind:class="{ 'pricing-guide__field--toggle-active': seo_copy }"
                                            x-on:click="toggleField('seo_copy')"
                                        >
                                            <span class="pricing-guide__toggle-copy">
                                                <span class="pricing-guide__label" x-text="fieldLabel('seo_copy')">Professionel opsætning</span>
                                                <span class="pricing-guide__hint" x-text="fieldHint('seo_copy')">Vi står for opsætning af DNS, domæne og SEO-tekster, så du kommer hurtigere og mere trygt online.</span>
                                            </span>
                                            <span class="pricing-guide__toggle-state" x-text="toggleValue('seo_copy')">Nej</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="pricing-guide__footer">
                                    <div class="pricing-guide__footer-actions">
                                        <form method="POST" action="{{ route('customer.solution.capture') }}" class="pricing-guide__capture-form">
                                            @csrf
                                            <input type="hidden" name="package_key" x-bind:value="activePackage().key">
                                            <input type="hidden" name="locations" x-bind:value="locations">
                                            <input type="hidden" name="staff" x-bind:value="staff">
                                            <input type="hidden" name="bookings" x-bind:value="bookings">
                                            <input type="hidden" name="sections" x-bind:value="sections">
                                            <input type="hidden" name="traffic_tier" x-bind:value="traffic_tier">
                                            <input type="hidden" name="lead_module" x-bind:value="lead_module ? 1 : 0">
                                            <input type="hidden" name="seo_copy" x-bind:value="seo_copy ? 1 : 0">
                                            <input type="hidden" name="billing_cycle" x-bind:value="currentBillingCycle()">

                                            <button type="submit" class="ui-button ui-button--ink pricing-guide__jump" x-text="accountCtaLabel()">
                                                Kom i gang med det samme
                                            </button>
                                        </form>

                                        <a class="pricing-guide__contact-link" x-bind:href="activePackage().href" href="{{ $defaultPackage['href'] ?? '#' }}">
                                            Tal med os om <span x-text="activePackage().title">{{ $defaultPackage['title'] ?? 'løsningen' }}</span>
                                        </a>
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
                            <div class="package-card__top" x-bind:class="{ 'package-card__top--with-badge': activePackage().key === 'scale' && activePackage().badge }">
                                <p class="package-card__support" x-text="activePackage().supportCopy">{{ $defaultPackage['support_copy'] ?? '' }}</p>

                                <template x-if="activePackage().key === 'scale' && activePackage().badge">
                                    <span class="package-card__badge package-card__badge--star">
                                        <span class="package-card__badge-text" x-text="activePackage().badge">{{ $defaultPackage['badge'] ?? '' }}</span>
                                    </span>
                                </template>
                            </div>

                            <div class="package-card__heading">
                                <h2 class="package-card__title" x-text="activePackage().title">{{ $defaultPackage['title'] ?? '' }}</h2>

                                <div class="package-card__price-block">
                                    <p class="package-card__price" x-text="activePackage().price">{{ $defaultPackage['price'] ?? '' }}</p>
                                    <p class="package-card__price-note" x-text="activePackage().priceNote">{{ $defaultPackage['price_suffix'] ?? '' }}</p>
                                </div>
                            </div>

                            <p class="package-card__headline" x-html="activePackageHeadlineMarkup()">{!! $defaultHeadlineMarkup !!}</p>
                            <p class="package-card__setup" x-text="activePackage().cardDetail">1 lokation · 1 medarbejder · 300 bookinger/år</p>

                            <ul class="package-card__points">
                                <template x-for="(point, index) in activePackage().points" :key="packagePointKey(point, index)">
                                    <li x-data="{ pointMeta: normalizePoint(point) }" x-bind:class="packagePointClasses(pointMeta, index)">
                                        <span class="package-card__point-copy" x-text="pointMeta.label"></span>

                                        <template x-if="pointMeta.note.label">
                                            <span class="package-card__point-note">
                                                <span
                                                    class="pricing-note pricing-note--align-start"
                                                    x-data="{ open: false }"
                                                    x-on:click.outside="open = false"
                                                    x-on:keydown.escape.window="open = false"
                                                >
                                                    <span class="pricing-note__label" x-text="pointMeta.note.label"></span>

                                                    <template x-if="pointMeta.note.tiers.length">
                                                        <span class="pricing-note__popover-wrap">
                                                            <button
                                                                type="button"
                                                                class="pricing-note__trigger"
                                                                x-bind:aria-expanded="open.toString()"
                                                                aria-label="Vis SMS-priser"
                                                                x-on:click="open = ! open"
                                                            >
                                                                ?
                                                            </button>

                                                            <div
                                                                class="pricing-note__popover"
                                                                x-cloak
                                                                x-show="open"
                                                                x-transition.opacity.duration.150ms
                                                                style="display: none;"
                                                            >
                                                                <p class="pricing-note__popover-title" x-text="pointMeta.note.title || 'SMS-priser'"></p>
                                                                <p class="pricing-note__popover-copy" x-show="pointMeta.note.caption" x-text="pointMeta.note.caption"></p>

                                                                <div class="pricing-note__tiers">
                                                                    <template x-for="tier in pointMeta.note.tiers" :key="`${tier.range}-${tier.price}`">
                                                                        <div class="pricing-note__tier">
                                                                            <span class="pricing-note__tier-range" x-text="tier.range"></span>
                                                                            <span class="pricing-note__tier-price" x-text="tier.price"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </span>
                                                    </template>
                                                </span>
                                            </span>
                                        </template>
                                    </li>
                                </template>
                            </ul>

                            <p class="package-card__footnote" x-cloak x-show="Boolean(activePackage().footnote)" x-text="activePackage().footnote">
                                {{ $defaultPackage['footnote'] ?? '' }}
                            </p>
                        </article>
                    </div>
                </div>

            </div>
        </section>

        <section class="ui-section ui-section--compact pricing-compare-section">
            <div class="ui-shell pricing-compare-shell">
                <div class="pricing-compare-intro" data-reveal style="--reveal-delay: 40ms;">
                    <p class="pricing-compare-intro__eyebrow">Sammenlign spor</p>
                    <h2 class="pricing-compare-intro__title">Hvad indgår i de forskellige løsninger?</h2>
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
