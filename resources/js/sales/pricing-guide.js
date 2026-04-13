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
            const pricing = this.calculatePackagePricing(packageMeta);

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
                setupSummary: pricing.setupSummary,
            };
        },

        calculatePackagePricing(packageMeta) {
            const pricing = packageMeta.pricing ?? null;
            const setupSummary = this.setupSummary();

            if (! pricing || typeof pricing.base !== 'number') {
                return {
                    price: packageMeta.price ?? '',
                    priceNote: packageMeta.priceSuffix ?? '',
                    setupSummary,
                };
            }

            const modifiers = Object.entries(pricing.modifiers ?? {}).reduce((sum, [field, rule]) => {
                return sum + this.priceModifierFor(field, rule);
            }, 0);

            const total = Math.max(pricing.base + modifiers, 0);
            const prefix = pricing.prefix ?? 'Fra';
            const suffix = pricing.suffix ?? 'kr/måned';

            return {
                price: `${prefix} ${this.formatNumber(total)} ${suffix}`,
                priceNote: packageMeta.priceSuffix ?? '',
                setupSummary,
            };
        },

        priceModifierFor(field, rule) {
            const value = Number(this[field] ?? 0);
            const included = Number(rule?.included ?? 0);
            const step = Math.max(Number(rule?.step ?? 1), 1);
            const amount = Number(rule?.amount ?? 0);
            const overflow = Math.max(value - included, 0);

            return Math.ceil(overflow / step) * amount;
        },

        setupSummary() {
            return [
                `${this.sliderValue('locations')} lokationer`,
                `${this.sliderValue('staff')} medarbejdere`,
                `${this.sliderValue('bookings')} bookinger/år`,
            ].join(' · ');
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
