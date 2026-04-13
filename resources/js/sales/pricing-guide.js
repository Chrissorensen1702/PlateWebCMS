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

        recommendation() {
            const key = this.recommendedKey();
            const packageMeta = this.packages[key] ?? {};

            return {
                key,
                title: packageMeta.title ?? 'Scale',
                price: packageMeta.price ?? '',
                priceNote: packageMeta.priceSuffix ?? '',
                href: packageMeta.href ?? '#',
                label: packageMeta.label ?? 'Se løsning',
                reason: this.recommendationReason(key),
            };
        },

        recommendedKey() {
            if (this.journey === 'signature') {
                return 'signature';
            }

            if (this.journey === 'platebook') {
                if (this.complexityScore() >= 8 || this.locations >= 5 || this.staff >= 35 || this.bookings >= 2500) {
                    return 'signature';
                }

                return 'platebook';
            }

            if (this.journey === 'launch') {
                if (this.complexityScore() >= 9 || this.locations >= 5 || this.staff >= 30 || this.sections >= 5) {
                    return 'signature';
                }

                if (this.complexityScore() >= 4 || this.locations >= 2 || this.staff >= 8 || this.bookings >= 600 || this.sections >= 3) {
                    return 'scale';
                }

                return 'launch';
            }

            if (this.complexityScore() >= 10 || this.locations >= 6 || this.staff >= 50 || this.bookings >= 4000 || this.sections >= 5) {
                return 'signature';
            }

            return 'scale';
        },

        complexityScore() {
            return (
                this.scoreThresholds(this.locations, [2, 4, 7]) +
                this.scoreThresholds(this.staff, [8, 24, 60]) +
                this.scoreThresholds(this.bookings, [500, 1500, 3500]) +
                this.scoreThresholds(this.sections, [2, 4, 5])
            );
        },

        scoreThresholds(value, thresholds) {
            return thresholds.reduce((score, threshold) => score + (value >= threshold ? 1 : 0), 0);
        },

        isRecommended(packageKey) {
            return this.recommendedKey() === String(packageKey);
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

        recommendationReason(packageKey) {
            const setupSummary = `${this.sliderValue('locations')} lokationer, ${this.sliderValue('staff')} medarbejdere og ca. ${this.sliderValue('bookings')} bookinger om året.`;

            if (packageKey === 'platebook') {
                return `Det passer bedst, hvis I vil beholde den nuværende hjemmeside og kun koble booking på. ${setupSummary}`;
            }

            if (packageKey === 'launch') {
                return `Det letteste spor, hvis I vil hurtigt online med en enkel løsning. ${setupSummary}`;
            }

            if (packageKey === 'signature') {
                return `Jeres setup peger mod en mere fleksibel løsning med flere behov og mere frihed i opsætningen. ${setupSummary}`;
            }

            return `Det bedste match, hvis hjemmeside og booking skal spille sammen fra start uden at blive helt custom. ${setupSummary}`;
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
