const registerPricingGuide = () => {
    if (! window.Alpine || window.Alpine.__pricingGuideRegistered) {
        return;
    }

    window.Alpine.__pricingGuideRegistered = true;

    window.Alpine.data('pricingGuide', () => ({
        packages: {},
        journey: 'scale',
        locations: 1,
        staff: 4,
        bookings: 300,
        sections: 3,

        hydrate(rawPackages) {
            if (! rawPackages) {
                return;
            }

            try {
                this.packages = JSON.parse(rawPackages);
            } catch (error) {
                console.warn('Could not parse pricing guide packages.', error);
                this.packages = {};
            }
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
                title: packageMeta.title ?? 'Scale',
                badge: packageMeta.badge ?? '',
                headline: packageMeta.headline ?? '',
                delivery: packageMeta.delivery ?? '',
                href: packageMeta.href ?? '#',
                label: packageMeta.label ?? 'Se løsning',
                tone: packageMeta.tone ?? key,
                featured: Boolean(packageMeta.featured),
                points: Array.isArray(packageMeta.points) ? packageMeta.points : [],
                price: pricing.price,
                priceNote: pricing.priceNote,
                detail: pricing.detail,
                billingNote: pricing.billingNote,
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
                    billingNote: 'Prisen bekræftes efter en kort gennemgang.',
                };
            }

            switch (pricing.mode) {
                case 'flat':
                    return {
                        price: this.formatPrice(pricing.amount, {
                            prefix: pricing.prefix ?? '',
                            suffix: pricing.suffix ?? 'kr.',
                        }),
                        priceNote: packageMeta.priceSuffix ?? '',
                        detail: 'Fast pris på standard hjemmeside inkl. .dk-domæne.',
                        billingNote: 'Starter har fast pris og bruger jeres valg som pejlemærke.',
                    };

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
                        billingNote: '0 kr. de første 3 måneder. Derefter reguleres prisen primært af antal bookinger.',
                    };
                }

                case 'booking_tiered': {
                    const monthlyPrice = this.resolveTierPrice(this.bookings, pricing.tiers);

                    return {
                        price: this.formatPrice(monthlyPrice, {
                            prefix: pricing.prefix ?? '',
                            suffix: pricing.suffix ?? 'kr./måned',
                        }),
                        priceNote: packageMeta.priceSuffix ?? '',
                        detail: usageSummary,
                        billingNote: 'PlateBook skalerer primært efter antal bookinger.',
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
                        billingNote: 'Custom går direkte til tilbud og scope-afklaring.',
                    };

                default:
                    return {
                        price: packageMeta.price ?? '',
                        priceNote: packageMeta.priceSuffix ?? '',
                        detail: usageSummary,
                        billingNote: 'Prisen bekræftes efter en kort gennemgang.',
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
                sections: `${this.sliderValue('sections')} sektioner`,
            };

            return (Array.isArray(fields) ? fields : Object.keys(summaryMap))
                .filter((field) => summaryMap[field])
                .map((field) => summaryMap[field])
                .join(' · ');
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

        activePackagePointsMarkup() {
            return this.activePackage()
                .points
                .map((point) => `<li class="package-card__point">${this.escapeHtml(point)}</li>`)
                .join('');
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
