const registerPricingGuide = () => {
    if (! window.Alpine || window.Alpine.__pricingGuideRegistered) {
        return;
    }

    window.Alpine.__pricingGuideRegistered = true;

    window.Alpine.data('pricingGuide', () => ({
        packages: {},
        journey: 'scale',
        locations: 1,
        staff: 1,
        bookings: 300,
        sections: 3,
        traffic_tier: 'low',
        lead_module: false,
        seo_copy: false,
        annualBilling: false,
        isAuthenticated: false,

        hydrate(rawPackages, rawSelection) {
            if (! rawPackages) {
                return;
            }

            try {
                this.packages = JSON.parse(rawPackages);
            } catch (error) {
                console.warn('Could not parse pricing guide packages.', error);
                this.packages = {};
            }

            this.isAuthenticated = this.rootAuthenticatedState();
            this.applySelection(rawSelection);
        },

        applySelection(rawSelection) {
            if (! rawSelection) {
                return;
            }

            try {
                const selection = JSON.parse(rawSelection);

                if (selection.package_key && this.packages[selection.package_key]) {
                    this.journey = selection.package_key;
                }

                this.locations = this.normalizeNumericValue(selection.locations, this.sliderMin('locations'), this.sliderMax('locations'));
                this.staff = this.normalizeNumericValue(selection.staff, this.sliderMin('staff'), this.sliderMax('staff'));
                this.bookings = this.normalizeNumericValue(selection.bookings, this.sliderMin('bookings'), this.sliderMax('bookings'));
                this.sections = this.normalizeNumericValue(selection.sections, this.sliderMin('sections'), this.sliderMax('sections'));
                this.traffic_tier = this.normalizeTrafficTier(selection.traffic_tier ?? selection.package_options?.traffic_tier);
                this.lead_module = this.normalizeBooleanValue(selection.lead_module ?? selection.package_options?.lead_module);
                this.seo_copy = this.normalizeBooleanValue(selection.seo_copy ?? selection.package_options?.seo_copy);
                this.annualBilling = this.normalizeBillingCycle(selection.billing_cycle ?? selection.package_options?.billing_cycle) === 'annual';
            } catch (error) {
                console.warn('Could not parse pricing guide selection.', error);
            }
        },

        rootAuthenticatedState() {
            return document.querySelector('.pricing-page')?.dataset?.authenticated === '1';
        },

        activePackageKey() {
            if (this.packages[this.journey]) {
                return this.journey;
            }

            return Object.keys(this.packages)[0] ?? 'scale';
        },

        activePackage() {
            const key = this.activePackageKey();
            const packageMeta = this.packages[key] ?? {};
            const visibleFields = this.packageVisibleFields(packageMeta);
            const pricing = this.calculatePackagePricing(packageMeta, visibleFields);

            return {
                key,
                title: packageMeta.title ?? 'Studio',
                badge: packageMeta.badge ?? '',
                headline: packageMeta.headline ?? '',
                supportCopy: packageMeta.supportCopy ?? '',
                footnote: packageMeta.footnote ?? '',
                footnotePoint: packageMeta.footnotePoint ?? '',
                href: packageMeta.href ?? '#',
                label: packageMeta.label ?? 'Se løsning',
                tone: packageMeta.tone ?? key,
                featured: Boolean(packageMeta.featured),
                points: Array.isArray(packageMeta.points) ? packageMeta.points : [],
                price: pricing.price,
                priceNote: pricing.priceNote,
                detail: pricing.detail,
                cardDetail: pricing.cardDetail ?? pricing.detail,
                visibleFields,
            };
        },

        calculatePackagePricing(packageMeta, visibleFields) {
            const pricing = packageMeta.pricing ?? null;
            const usageSummary = this.setupSummary(visibleFields);

            if (! pricing || typeof pricing !== 'object') {
                return {
                    price: packageMeta.price ?? '',
                    priceNote: packageMeta.priceSuffix ?? '',
                    detail: usageSummary,
                };
            }

            switch (pricing.mode) {
                case 'flat':
                    if (this.annualBilling) {
                        return this.annualBillingPriceMeta(Number(pricing.amount ?? 0), {
                            detail: 'Fast pris på standard hjemmeside, hvor domænet tilkobles nemt via jeres nuværende udbyder.',
                            cardDetail: 'Dette får I med:',
                        });
                    }

                    return {
                        price: this.formatPrice(pricing.amount, {
                            prefix: pricing.prefix ?? '',
                            suffix: pricing.suffix ?? 'kr.',
                        }),
                        priceNote: packageMeta.priceSuffix ?? '',
                        detail: 'Fast pris på standard hjemmeside, hvor domænet tilkobles nemt via jeres nuværende udbyder.',
                        cardDetail: 'Dette får I med:',
                    };

                case 'launch_configurable': {
                    const baseAmount = Number(pricing.base_amount ?? pricing.amount ?? 0);
                    const includedPages = Math.max(1, Number(pricing.included_pages ?? 1));
                    const perPageAmount = Number(pricing.per_page_amount ?? 0);
                    const addOns = pricing.add_ons ?? {};
                    const setupFees = pricing.setup_fees ?? {};
                    const trafficTiers = pricing.traffic_tiers ?? {};
                    const extraPages = Math.max(0, Number(this.sections) - includedPages);
                    const launchTotal = baseAmount
                        + (extraPages * perPageAmount)
                        + Number(trafficTiers[this.traffic_tier]?.amount ?? 0)
                        + (this.lead_module ? Number(addOns.lead_module ?? 0) : 0);
                    const setupFee = this.seo_copy ? Number(setupFees.seo_copy ?? 0) : 0;
                    if (this.annualBilling) {
                        return this.annualBillingPriceMeta(launchTotal, {
                            extraNote: setupFee > 0 ? `+${this.formatNumber(setupFee)} kr. i opstart for professionel opsætning` : '',
                            detail: usageSummary || `${this.sliderValue('sections')} sider`,
                            cardDetail: usageSummary || `${this.sliderValue('sections')} sider`,
                        });
                    }

                    const priceNote = setupFee > 0
                        ? `vejledende ud fra sider, trafik og tilvalg · +${this.formatNumber(setupFee)} kr. i opstart for professionel opsætning · ekskl. moms`
                        : (packageMeta.priceSuffix ?? '');

                    return {
                        price: this.formatPrice(launchTotal, {
                            prefix: pricing.prefix ?? '',
                            suffix: pricing.suffix ?? 'kr./md.',
                        }),
                        priceNote,
                        detail: usageSummary || `${this.sliderValue('sections')} sider`,
                        cardDetail: usageSummary || `${this.sliderValue('sections')} sider`,
                    };
                }

                case 'scale_configurable': {
                    const baseAmount = Number(pricing.base_amount ?? 0);
                    const includedStaff = Math.max(0, Number(pricing.included_staff ?? 0));
                    const staffAmount = Number(pricing.staff_amount ?? 0);
                    const extraStaff = Math.max(0, Number(this.staff ?? 0) - includedStaff);
                    const monthlyPrice = baseAmount
                        + (extraStaff * staffAmount)
                        + this.resolveTierPrice(this.locations, pricing.location_tiers)
                        + this.resolveTierPrice(this.bookings, pricing.booking_tiers);

                    if (this.annualBilling) {
                        return this.annualBillingPriceMeta(monthlyPrice, {
                            extraNote: 'starter med 3 mdr. gratis',
                            detail: usageSummary,
                        });
                    }

                    return {
                        price: pricing.intro_label ?? '0 kr. de første 3 måneder',
                        priceNote: `derefter ${this.formatPrice(monthlyPrice, {
                            prefix: pricing.prefix ?? '',
                            suffix: pricing.suffix ?? 'kr./måned',
                        })} · vejledende · ekskl. moms`,
                        detail: usageSummary,
                    };
                }

                case 'intro_booking_tiered': {
                    const monthlyPrice = this.resolveTierPrice(this.bookings, pricing.tiers);
                    const recurringPrice = this.formatPrice(monthlyPrice, {
                        prefix: pricing.recurring_prefix ?? '',
                        suffix: pricing.suffix ?? 'kr./måned',
                    });

                    return {
                        price: pricing.intro_label ?? '0 kr. de første 3 måneder',
                        priceNote: `derefter ${recurringPrice} · vejledende · ekskl. moms`,
                        detail: usageSummary,
                    };
                }

                case 'booking_tiered': {
                    const monthlyPrice = this.resolveTierPrice(this.bookings, pricing.tiers);

                    if (this.annualBilling) {
                        return this.annualBillingPriceMeta(monthlyPrice, {
                            detail: usageSummary,
                        });
                    }

                    return {
                        price: this.formatPrice(monthlyPrice, {
                            prefix: pricing.prefix ?? '',
                            suffix: pricing.suffix ?? 'kr./måned',
                        }),
                        priceNote: packageMeta.priceSuffix ?? '',
                        detail: usageSummary,
                    };
                }

                case 'custom_quote':
                    return {
                        price: this.formatPrice(pricing.amount, {
                            prefix: pricing.prefix ?? 'Fra',
                            suffix: pricing.suffix ?? 'kr.',
                        }),
                        priceNote: packageMeta.priceSuffix ?? '',
                        detail: 'Vi bruger jeres valg som pejlemærke og sender et konkret tilbud.',
                    };

                default:
                    return {
                        price: packageMeta.price ?? '',
                        priceNote: packageMeta.priceSuffix ?? '',
                        detail: usageSummary,
                    };
            }
        },

        resolveTierPrice(value, tiers) {
            const normalizedTiers = Array.isArray(tiers) ? tiers : [];

            if (! normalizedTiers.length) {
                return 0;
            }

            const numericValue = Number(value ?? 0);
            const matchedTier = normalizedTiers.find((tier) => numericValue <= Number(tier?.up_to ?? 0));

            return Number((matchedTier ?? normalizedTiers.at(-1))?.amount ?? 0);
        },

        packageVisibleFields(packageMeta) {
            const visibleFields = Array.isArray(packageMeta.visibleFields) ? packageMeta.visibleFields : [];

            return visibleFields.length
                ? visibleFields
                : ['locations', 'staff', 'bookings', 'sections'];
        },

        fieldVisible(field) {
            return this.packageVisibleFields(this.packages[this.activePackageKey()] ?? {}).includes(String(field));
        },

        setupSummary(fields) {
            const summaryMap = {
                locations: `${this.sliderValue('locations')} lokationer`,
                staff: `${this.sliderValue('staff')} medarbejdere`,
                bookings: `${this.sliderValue('bookings')} bookinger/år`,
                sections: this.activePackageKey() === 'launch'
                    ? `${this.sliderValue('sections')} sider`
                    : `${this.sliderValue('sections')} sektioner`,
                traffic_tier: this.activePackageKey() === 'launch'
                    ? this.trafficTierLabel(this.traffic_tier)
                    : null,
                lead_module: this.lead_module ? 'nyhedsbrev- og leadmodul' : null,
                seo_copy: this.seo_copy ? 'professionel opsætning' : null,
            };

            return (Array.isArray(fields) ? fields : Object.keys(summaryMap))
                .filter((field) => summaryMap[field])
                .map((field) => summaryMap[field])
                .join(' · ');
        },

        fieldLabel(field) {
            return {
                locations: 'Antal lokationer',
                staff: 'Antal medarbejdere',
                bookings: 'Antal årlige bookinger',
                sections: this.activePackageKey() === 'launch'
                    ? 'Antal ønskede sider'
                    : 'Antal sektioner på hjemmeside',
                traffic_tier: 'Forventet trafik',
                lead_module: 'Ønskes nyhedsbrev- og leadmodul?',
                seo_copy: 'Professionel opsætning',
            }[field] ?? '';
        },

        fieldHint(field) {
            return {
                traffic_tier: 'Vælg det niveau, der passer bedst til jeres forventede besøgstal.',
                lead_module: 'Gør det nemt at samle leads og nyhedsbrevs-tilmeldinger.',
                seo_copy: 'Vi står for opsætning af DNS, domæne og SEO-tekster, så du kommer hurtigere og mere trygt online.',
            }[field] ?? '';
        },

        trafficTierOptions() {
            return [
                { value: 'low', label: 'Lav', hint: 'Op til ca. 2.500 besøg/md.' },
                { value: 'medium', label: 'Mellem', hint: 'Op til ca. 10.000 besøg/md.' },
                { value: 'high', label: 'Høj', hint: '10.000+ besøg/md.' },
            ];
        },

        trafficTierLabel(value) {
            return this.trafficTierOptions().find((option) => option.value === value)?.label ?? 'Lav';
        },

        toggleField(field) {
            this[field] = ! this.normalizeBooleanValue(this[field]);
        },

        toggleValue(field) {
            return this.normalizeBooleanValue(this[field]) ? 'Ja' : 'Nej';
        },

        currentBillingCycle() {
            return this.annualBilling ? 'annual' : 'monthly';
        },

        isRecommended(packageKey) {
            return this.activePackageKey() === String(packageKey);
        },

        planRecommendationClasses(packageKey) {
            return {
                'pricing-compare__plan--recommended': this.isRecommended(packageKey),
            };
        },

        valueRecommendationClasses(packageKey) {
            return {
                'pricing-compare__value--recommended': this.isRecommended(packageKey),
            };
        },

        packageCardClassList() {
            const activePackage = this.activePackage();
            const classNames = [`package-card--${activePackage.tone ?? activePackage.key}`];

            if (activePackage.featured) {
                classNames.push('package-card--featured');
            }

            return classNames.join(' ');
        },

        packagePointKey(point, index) {
            const pointMeta = this.normalizePoint(point);

            return `${this.activePackageKey()}-${index}-${pointMeta.label}`;
        },

        packagePointClasses(pointMeta, index) {
            const activePackage = this.activePackage();
            const classes = ['package-card__point'];

            if (['scale', 'signature'].includes(activePackage.key) && index === 0) {
                classes.push('package-card__point--included');
            }

            if (activePackage.footnotePoint && pointMeta.label === activePackage.footnotePoint) {
                classes.push('package-card__point--footnoted');
            }

            return classes.join(' ');
        },

        normalizePoint(point) {
            if (typeof point === 'string') {
                return {
                    label: point,
                    note: this.normalizeNote(''),
                };
            }

            if (point && typeof point === 'object') {
                return {
                    label: String(point.label ?? ''),
                    note: this.normalizeNote(point.note ?? ''),
                };
            }

            return {
                label: '',
                note: this.normalizeNote(''),
            };
        },

        normalizeNote(note) {
            if (typeof note === 'string') {
                return {
                    label: note,
                    title: '',
                    caption: '',
                    tiers: [],
                };
            }

            if (note && typeof note === 'object') {
                return {
                    label: String(note.label ?? ''),
                    title: String(note.title ?? ''),
                    caption: String(note.caption ?? ''),
                    tiers: Array.isArray(note.tiers)
                        ? note.tiers
                            .map((tier) => ({
                                range: String(tier?.range ?? ''),
                                price: String(tier?.price ?? ''),
                            }))
                            .filter((tier) => tier.range && tier.price)
                        : [],
                };
            }

            return {
                label: '',
                title: '',
                caption: '',
                tiers: [],
            };
        },

        activePackageHeadlineMarkup() {
            const headline = this.escapeHtml(this.activePackage().headline ?? '');

            return headline.replace(
                'PlateBook',
                '<span class="package-card__headline-brand"><span class="package-card__headline-brand-plate">Plate</span><span class="package-card__headline-brand-book">Book</span></span>',
            );
        },

        accountCtaLabel() {
            return 'Kom i gang med det samme';
        },

        sliderMin(field) {
            return {
                locations: 1,
                staff: 1,
                bookings: 50,
                sections: 1,
            }[field] ?? 0;
        },

        sliderMax(field) {
            return {
                locations: 10,
                staff: 100,
                bookings: 5000,
                sections: 5,
            }[field] ?? 100;
        },

        sliderStep(field) {
            return {
                locations: 1,
                staff: 1,
                bookings: 50,
                sections: 1,
            }[field] ?? 1;
        },

        sliderStyle(field) {
            const min = this.sliderMin(field);
            const max = this.sliderMax(field);
            const value = Number(this[field] ?? min);
            const percent = ((value - min) / (max - min)) * 100;

            return `--slider-percent: ${percent}%;`;
        },

        sliderValue(field) {
            const value = Number(this[field] ?? this.sliderMin(field));
            const max = this.sliderMax(field);

            if (field === 'bookings') {
                return value >= max
                    ? `${this.formatNumber(max)}+`
                    : this.formatNumber(value);
            }

            return value >= max ? `${max}+` : `${value}`;
        },

        normalizeNumericValue(value, min, max) {
            const numericValue = Number.isFinite(Number(value)) ? Number(value) : min;

            return Math.min(max, Math.max(min, numericValue));
        },

        normalizeBooleanValue(value) {
            return ['1', 1, true, 'true', 'on', 'yes'].includes(value);
        },

        normalizeTrafficTier(value) {
            return ['low', 'medium', 'high'].includes(value) ? value : 'low';
        },

        normalizeBillingCycle(value) {
            return ['monthly', 'annual'].includes(value) ? value : 'monthly';
        },

        sliderScaleStart(field) {
            const min = this.sliderMin(field);

            return field === 'bookings' ? this.formatNumber(min) : `${min}`;
        },

        sliderScaleEnd(field) {
            const max = this.sliderMax(field);

            if (field === 'bookings') {
                return `${this.formatNumber(max)}+`;
            }

            return `${max}+`;
        },

        formatNumber(value) {
            return new Intl.NumberFormat('da-DK').format(value);
        },

        formatPrice(value, { prefix = '', suffix = 'kr.' } = {}) {
            const parts = [prefix, this.formatNumber(value), suffix].filter(Boolean);

            return parts.join(' ');
        },

        annualBillingPriceMeta(monthlyAmount, { extraNote = '', detail = '', cardDetail = '' } = {}) {
            const annualAmount = Math.round(Number(monthlyAmount ?? 0) * 12 * 0.88);
            const monthlyEquivalent = Math.round(annualAmount / 12);

            return {
                price: this.formatPrice(annualAmount, { suffix: 'kr./år' }),
                priceNote: [
                    `svarer til ${this.formatPrice(monthlyEquivalent, { suffix: 'kr./md.' })}`,
                    '12% rabat',
                    'betales årligt',
                    extraNote || null,
                    'ekskl. moms',
                ].filter(Boolean).join(' · '),
                detail,
                cardDetail: cardDetail || detail,
            };
        },

        escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        },
    }));

    queueMicrotask(() => {
        document.querySelectorAll('[x-data^="pricingGuide"]').forEach((root) => {
            if (root.__pricingGuideHydrated) {
                return;
            }

            root.__pricingGuideHydrated = true;

            if (root._x_marker && ! root._x_dataStack) {
                delete root._x_marker;
            }

            if (window.Alpine.initTree) {
                window.Alpine.initTree(root);
            }
        });
    });
};

if (window.Alpine) {
    registerPricingGuide();
} else {
    document.addEventListener('alpine:init', registerPricingGuide, { once: true });
}
