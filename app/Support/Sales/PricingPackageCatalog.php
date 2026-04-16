<?php

namespace App\Support\Sales;

use App\Models\Plan;
use Illuminate\Support\Collection;

class PricingPackageCatalog
{
    /**
     * @param  Collection<int, Plan>  $plans
     * @return array<int, array<string, mixed>>
     */
    public function packages(Collection $plans): array
    {
        $templatePlans = $plans->where('is_custom', false)->values();
        $launch = $templatePlans->get(0);
        $scale = $templatePlans->get(1);
        $signature = $plans->firstWhere('is_custom', true);
        $smsPricingNote = $this->smsPricingNote();

        return [
            [
                'key' => 'launch',
                'plan_id' => $launch?->id,
                'eyebrow' => 'Til dig der vil hurtigt i gang',
                'title' => 'Atelier',
                'badge' => 'Domæne klar',
                'headline' => 'Et professionelt startpunkt for dig, der vil hurtigt online med en løsning, der skaber et godt førstehåndsindtryk og giver plads til at bygge videre, når behovene vokser.',
                'support_copy' => 'Pakken inkluderer teknisk support',
                'price' => '199 kr./md.',
                'annual_price' => '2.101 kr./år',
                'price_suffix' => 'vejledende ud fra sider, trafik og tilvalg · ekskl. moms',
                'annual_suffix' => 'svarer til 175 kr./md. · 12% rabat · betales årligt · ekskl. moms',
                'pricing' => [
                    'mode' => 'launch_configurable',
                    'base_amount' => 199,
                    'included_pages' => 1,
                    'per_page_amount' => 10,
                    'traffic_tiers' => [
                        'low' => ['label' => 'Lav trafik', 'amount' => 0],
                        'medium' => ['label' => 'Mellem trafik', 'amount' => 35],
                        'high' => ['label' => 'Høj trafik', 'amount' => 90],
                    ],
                    'add_ons' => [
                        'lead_module' => 18,
                    ],
                    'setup_fees' => [
                        'seo_copy' => 299,
                    ],
                    'suffix' => 'kr./md.',
                ],
                'visible_fields' => ['sections', 'traffic_tier', 'lead_module', 'seo_copy'],
                'points' => [
                    'Professionelt modulopbygget website',
                    'SEO og metadata',
                    'Temabaserede layouts',
                    'Forskellige farvepaletter',
                    'Hosting på vores platform',
                    'Automatisk backup',
                    'Kunde-CMS til indhold og opdateringer',
                    'Mulighed for nyhedsbrev- og leadmodul',
                    'Tydelige CTA\'er til konvertering',
                    'Nem DNS- og domæneopsætning',
                    'SSL og sikker forbindelse',
                    'Mobilvenligt design',
                ],
                'href' => route('contact', ['plan_id' => $launch?->id, 'package' => 'launch']),
                'label' => 'Vælg Atelier',
                'tone' => 'launch',
                'featured' => false,
            ],
            [
                'key' => 'scale',
                'plan_id' => $scale?->id,
                'eyebrow' => 'Til virksomheder i vækst',
                'title' => 'Studio',
                'badge' => '3 mdr. gratis',
                'headline' => 'Til virksomheder, der vil have booking integreret som en naturlig del af kundeoplevelsen. Studio er bygget med PlateBook, så I kan skabe flere bookinger direkte fra jeres egen hjemmeside.',
                'support_copy' => 'Pakken inkluderer support, samt teknisk hjælp til opsætning.',
                'price' => '0 kr. de første 3 måneder',
                'annual_price' => '3.157 kr./år',
                'price_suffix' => 'derefter vejledende ud fra medarbejdere, lokationer og bookinger · ekskl. moms',
                'annual_suffix' => 'svarer til 263 kr./md. · 12% rabat · betales årligt · ekskl. moms',
                'pricing' => [
                    'mode' => 'scale_configurable',
                    'intro_label' => '0 kr. de første 3 måneder',
                    'base_amount' => 299,
                    'included_staff' => 1,
                    'staff_amount' => 25,
                    'location_tiers' => [
                        ['up_to' => 1, 'amount' => 0],
                        ['up_to' => 4, 'amount' => 35],
                        ['up_to' => 7, 'amount' => 100],
                        ['up_to' => 10, 'amount' => 200],
                    ],
                    'booking_tiers' => [
                        ['up_to' => 250, 'amount' => 0],
                        ['up_to' => 750, 'amount' => 50],
                        ['up_to' => 2000, 'amount' => 100],
                        ['up_to' => 3500, 'amount' => 150],
                        ['up_to' => 5000, 'amount' => 200],
                    ],
                    'suffix' => 'kr./måned',
                ],
                'visible_fields' => ['bookings', 'locations', 'staff', 'sections'],
                'points' => [
                    'Alt fra Atelier',
                    'Hosting på vores platform',
                    'Automatisk backup',
                    'Online booking direkte på hjemmesiden',
                    'Medarbejdere og lokationer i samme løsning',
                    [
                        'label' => 'Automatiske bookingbekræftelser og påmindelser',
                        'note' => $smsPricingNote,
                    ],
                    'Leadflow og CTA’er til flere bookinger',
                    'Mere branding og flere indholdssider',
                ],
                'href' => route('contact', ['plan_id' => $scale?->id, 'package' => 'scale', 'trial' => 1]),
                'label' => 'Vælg Studio',
                'tone' => 'scale',
                'featured' => true,
            ],
            [
                'key' => 'signature',
                'plan_id' => $signature?->id,
                'eyebrow' => 'Til skræddersyede projekter',
                'title' => 'Signature',
                'badge' => 'Skræddersyet',
                'headline' => 'Når design, funktioner og oplevelse skal formes mere frit omkring virksomheden og det udtryk du vil stå med.',
                'support_copy' => 'Pakken er dynamisk og prisen defineres efter ønsker',
                'price' => 'Fra 5.000 kr. + md. abonnement',
                'annual_price' => 'Fra 5.000 kr. + md. abonnement',
                'price_suffix' => 'opstartspris + løbende abonnement · vejledende efter scope',
                'annual_suffix' => 'opstartspris + løbende abonnement · vejledende efter scope',
                'pricing' => [
                    'mode' => 'custom_quote',
                    'amount' => 5000,
                    'prefix' => 'Fra',
                    'suffix' => 'kr. + md. abonnement',
                ],
                'visible_fields' => ['locations', 'sections', 'staff', 'bookings'],
                'points' => [
                    'Alt fra Atelier og Studio',
                    'Hosting på vores platform',
                    'Automatisk backup',
                    'Skræddersyet design og struktur',
                    'Særlige funktioner tilpasset jeres behov',
                    'Udvidet CMS med HTML-, CSS- og JS-tilpasninger',
                    'Tæt sparring gennem hele forløbet',
                    'Bygget omkring jeres brand, målgruppe og arbejdsgange',
                ],
                'footnote' => 'Muligheder for særlige funktioner afhænger af løsningens struktur og omfang.',
                'footnote_point' => 'Særlige funktioner tilpasset jeres behov',
                'href' => route('contact', ['plan_id' => $signature?->id, 'package' => 'signature']),
                'label' => 'Få et Signature-tilbud',
                'tone' => 'signature',
                'featured' => false,
            ],
            [
                'key' => 'platebook',
                'plan_id' => null,
                'eyebrow' => 'Til dig med eksisterende hjemmeside',
                'title' => 'Chairflow',
                'badge' => 'Booking only',
                'headline' => 'Et selvstændigt bookingsystem, hvis du vil beholde din nuværende hjemmeside og tilføje booking uden at bygge alt om.',
                'support_copy' => 'Pakken inkluderer teknisk support',
                'price' => '49 kr./måned',
                'annual_price' => '517 kr./år',
                'price_suffix' => 'vejledende efter antal bookinger · ekskl. moms',
                'annual_suffix' => 'svarer til 43 kr./md. · 12% rabat · betales årligt · ekskl. moms',
                'pricing' => [
                    'mode' => 'booking_tiered',
                    'suffix' => 'kr./måned',
                    'tiers' => [
                        ['up_to' => 300, 'amount' => 49],
                        ['up_to' => 1000, 'amount' => 69],
                        ['up_to' => 2500, 'amount' => 89],
                        ['up_to' => 5000, 'amount' => 119],
                    ],
                ],
                'visible_fields' => ['bookings', 'staff', 'locations'],
                'points' => [
                    'Selvstændigt bookingsystem',
                    'Hosting på vores platform',
                    'Automatisk backup',
                    'Let integration på eksisterende hjemmeside',
                    'Medarbejdere og behandlinger samlet ét sted',
                    [
                        'label' => 'Automatiske bookingbekræftelser og påmindelser',
                        'note' => $smsPricingNote,
                    ],
                    'Overblik over bookinger og aktivitet',
                ],
                'href' => route('contact', ['package' => 'platebook']),
                'label' => 'Kom i gang med Chairflow',
                'tone' => 'platebook',
                'featured' => false,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, values: array<string, bool|string>}>
     */
    public function comparisonRows(): array
    {
        $smsPricingNote = $this->smsPricingNote();

        return [
            [
                'label' => 'Priser fra',
                'values' => [
                    'launch' => '199 kr./md.',
                    'scale' => '299 kr./md.',
                    'signature' => 'Fra 5.000 kr. + md. abonnement',
                    'platebook' => '49 kr./md.',
                ],
            ],
            [
                'label' => 'Professionelt modulopbygget website',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'SEO og metadata',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Temabaserede layouts og farvepaletter',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Kunde-CMS til indhold og opdateringer',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Automatisk backup',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Nyhedsbrev og leadopsamling',
                'values' => [
                    'launch' => 'Tilvalg',
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Domæne og DNS opsætning',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Booking direkte på hjemmesiden',
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => 'CTA-baseret booking',
                ],
            ],
            [
                'label' => 'Medarbejdere i bookingflowet',
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Automatiske bookingbekræftelser og påmindelser',
                'note' => $smsPricingNote,
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Mere branding og flere indholdssider',
                'values' => [
                    'launch' => 'Basis',
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Skræddersyet design og struktur',
                'values' => [
                    'launch' => false,
                    'scale' => false,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Udvidet CMS med HTML-, CSS- og JS-tilpasninger',
                'values' => [
                    'launch' => false,
                    'scale' => false,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
        ];
    }

    /**
     * @return array{label: string, title: string, caption: string, tiers: array<int, array{range: string, price: string}>}
     */
    private function smsPricingNote(): array
    {
        return [
            'label' => 'Fra 0,7 DKK pr. SMS-besked',
            'title' => 'SMS-priser',
            'caption' => 'Prisen falder, jo flere SMS-beskeder I har brug for. Prisen er baseret på månedligt forbrug.',
            'tiers' => [
                [
                    'range' => '1-250 SMS',
                    'price' => '0,70 DKK pr. SMS',
                ],
                [
                    'range' => '251-500 SMS',
                    'price' => '0,63 DKK pr. SMS',
                ],
                [
                    'range' => '501-1.000 SMS',
                    'price' => '0,55 DKK pr. SMS',
                ],
                [
                    'range' => '1.001+ SMS',
                    'price' => '0,38 DKK pr. SMS',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function packageMap(Collection $plans): array
    {
        return collect($this->packages($plans))
            ->mapWithKeys(fn (array $package) => [$package['key'] => $package])
            ->all();
    }

    /**
     * @return array{package_key: string, locations: int, staff: int, bookings: int, sections: int, traffic_tier: string, lead_module: bool, seo_copy: bool, billing_cycle: string}
     */
    public function defaultSelection(): array
    {
        return [
            'package_key' => 'scale',
            'locations' => 1,
            'staff' => 1,
            'bookings' => 300,
            'sections' => 3,
            'traffic_tier' => 'low',
            'lead_module' => false,
            'seo_copy' => false,
            'billing_cycle' => 'monthly',
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, array<string, mixed>>  $packageMap
     * @return array{package_key: string, plan_id: int|null, locations: int, staff: int, bookings: int, sections: int, traffic_tier: string, lead_module: bool, seo_copy: bool, billing_cycle: string, package_options: array{traffic_tier: string, lead_module: bool, seo_copy: bool, billing_cycle: string}}
     */
    public function normalizeSelection(array $input, array $packageMap): array
    {
        $defaults = $this->defaultSelection();
        $packageKey = (string) ($input['package_key'] ?? $input['package'] ?? $defaults['package_key']);
        $rawOptions = $input['package_options'] ?? [];
        $packageOptions = is_array($rawOptions) ? $rawOptions : [];

        if (! array_key_exists($packageKey, $packageMap)) {
            $packageKey = $defaults['package_key'];
        }

        $trafficTier = $this->trafficTierValue($input['traffic_tier'] ?? $packageOptions['traffic_tier'] ?? $defaults['traffic_tier']);
        $leadModule = $this->booleanValue($input['lead_module'] ?? $packageOptions['lead_module'] ?? $defaults['lead_module']);
        $seoCopy = $this->booleanValue($input['seo_copy'] ?? $packageOptions['seo_copy'] ?? $defaults['seo_copy']);
        $billingCycle = $this->billingCycleValue($input['billing_cycle'] ?? $packageOptions['billing_cycle'] ?? $defaults['billing_cycle']);

        return [
            'package_key' => $packageKey,
            'plan_id' => $packageMap[$packageKey]['plan_id'] ?? null,
            'locations' => $this->clamp((int) ($input['locations'] ?? $defaults['locations']), 1, 10),
            'staff' => $this->clamp((int) ($input['staff'] ?? $defaults['staff']), 1, 100),
            'bookings' => $this->clamp((int) ($input['bookings'] ?? $defaults['bookings']), 50, 5000),
            'sections' => $this->clamp((int) ($input['sections'] ?? $defaults['sections']), 1, 5),
            'traffic_tier' => $trafficTier,
            'lead_module' => $leadModule,
            'seo_copy' => $seoCopy,
            'billing_cycle' => $billingCycle,
            'package_options' => [
                'traffic_tier' => $trafficTier,
                'lead_module' => $leadModule,
                'seo_copy' => $seoCopy,
                'billing_cycle' => $billingCycle,
            ],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $packageMap
     * @param  array<string, mixed>  $selection
     * @return array<string, mixed>
     */
    public function resolveSelection(array $packageMap, array $selection): array
    {
        $normalizedSelection = $this->normalizeSelection($selection, $packageMap);
        $package = $packageMap[$normalizedSelection['package_key']] ?? $packageMap[$this->defaultSelection()['package_key']];
        $visibleFields = $this->packageVisibleFields($package);
        $usageSummary = $this->setupSummary($normalizedSelection, $visibleFields, (string) $normalizedSelection['package_key']);
        $pricing = $package['pricing'] ?? null;

        $resolvedPricing = match ($pricing['mode'] ?? null) {
            'launch_configurable' => $this->resolveLaunchPricing($normalizedSelection, $pricing, $package, $usageSummary),
            'scale_configurable' => $this->resolveScalePricing($normalizedSelection, $pricing, $package, $usageSummary),
            'flat' => [
                'price' => $this->formatPrice((int) ($pricing['amount'] ?? 0), $pricing['prefix'] ?? '', $pricing['suffix'] ?? 'kr.'),
                'price_note' => $package['price_suffix'] ?? '',
                'detail' => 'Fast pris på standard hjemmeside, hvor domænet tilkobles nemt via jeres nuværende udbyder.',
                'billing_note' => 'Starter har fast pris og bruger jeres valg som pejlemærke.',
            ],
            'intro_booking_tiered' => [
                'price' => $pricing['intro_label'] ?? '0 kr. de første 3 måneder',
                'price_note' => 'derefter '.$this->formatPrice($this->resolveTierPrice((int) $normalizedSelection['bookings'], $pricing['tiers'] ?? []), $pricing['recurring_prefix'] ?? '', $pricing['suffix'] ?? 'kr./måned').' · vejledende · ekskl. moms',
                'detail' => $usageSummary,
                'billing_note' => '0 kr. de første 3 måneder. Derefter reguleres prisen primært af antal bookinger.',
            ],
            'booking_tiered' => $this->resolveBookingTieredPricing($normalizedSelection, $pricing, $package, $usageSummary),
            'custom_quote' => [
                'price' => $this->formatPrice((int) ($pricing['amount'] ?? 0), $pricing['prefix'] ?? 'Fra', $pricing['suffix'] ?? 'kr.'),
                'price_note' => $package['price_suffix'] ?? '',
                'detail' => 'Vi bruger jeres valg som pejlemærke og sender et konkret tilbud.',
                'billing_note' => 'Custom går direkte til tilbud og scope-afklaring.',
            ],
            default => [
                'price' => $package['price'] ?? '',
                'price_note' => $package['price_suffix'] ?? '',
                'detail' => $usageSummary,
                'billing_note' => 'Prisen bekræftes efter en kort gennemgang.',
            ],
        };

        return [
            ...$package,
            ...$normalizedSelection,
            'price' => $resolvedPricing['price'],
            'price_note' => $resolvedPricing['price_note'],
            'detail' => $resolvedPricing['detail'],
            'billing_note' => $resolvedPricing['billing_note'],
            'visible_fields' => $visibleFields,
        ];
    }

    /**
     * @param  array<string, mixed>  $selection
     * @return array<string, int|string>
     */
    public function selectionQuery(array $selection): array
    {
        return [
            'package' => (string) ($selection['package_key'] ?? $this->defaultSelection()['package_key']),
            'locations' => (int) ($selection['locations'] ?? $this->defaultSelection()['locations']),
            'staff' => (int) ($selection['staff'] ?? $this->defaultSelection()['staff']),
            'bookings' => (int) ($selection['bookings'] ?? $this->defaultSelection()['bookings']),
            'sections' => (int) ($selection['sections'] ?? $this->defaultSelection()['sections']),
            'traffic_tier' => (string) ($selection['traffic_tier'] ?? $this->defaultSelection()['traffic_tier']),
            'lead_module' => (int) $this->booleanValue($selection['lead_module'] ?? false),
            'seo_copy' => (int) $this->booleanValue($selection['seo_copy'] ?? false),
            'billing_cycle' => (string) ($selection['billing_cycle'] ?? $this->defaultSelection()['billing_cycle']),
        ];
    }

    /**
     * @param  array<string, mixed>  $package
     * @return list<string>
     */
    private function packageVisibleFields(array $package): array
    {
        $visibleFields = $package['visible_fields'] ?? [];

        if (! is_array($visibleFields) || $visibleFields === []) {
            return ['locations', 'staff', 'bookings', 'sections'];
        }

        return array_values(array_map('strval', $visibleFields));
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  list<string>  $fields
     */
    private function setupSummary(array $selection, array $fields, string $packageKey = ''): string
    {
        $summaryMap = [
            'locations' => $selection['locations'].' lokationer',
            'staff' => $selection['staff'].' medarbejdere',
            'bookings' => $this->formatNumber((int) $selection['bookings']).' bookinger/år',
            'sections' => $selection['sections'].($packageKey === 'launch' ? ' sider' : ' sektioner'),
            'traffic_tier' => $this->trafficTierLabel((string) ($selection['traffic_tier'] ?? 'low')),
            'lead_module' => ($selection['lead_module'] ?? false) ? 'nyhedsbrev- og leadmodul' : null,
            'seo_copy' => ($selection['seo_copy'] ?? false) ? 'professionel opsætning' : null,
        ];

        return collect($fields)
            ->filter(fn (string $field): bool => array_key_exists($field, $summaryMap) && filled($summaryMap[$field]))
            ->map(fn (string $field): string => (string) $summaryMap[$field])
            ->implode(' · ');
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  array<string, mixed>  $pricing
     * @param  array<string, mixed>  $package
     * @return array{price: string, price_note: string, detail: string, billing_note: string}
     */
    private function resolveLaunchPricing(array $selection, array $pricing, array $package, string $usageSummary): array
    {
        $baseAmount = (int) ($pricing['base_amount'] ?? 0);
        $includedPages = max(1, (int) ($pricing['included_pages'] ?? 1));
        $perPageAmount = (int) ($pricing['per_page_amount'] ?? 0);
        $extraPages = max(0, (int) ($selection['sections'] ?? 1) - $includedPages);
        $addOns = is_array($pricing['add_ons'] ?? null) ? $pricing['add_ons'] : [];
        $trafficTiers = is_array($pricing['traffic_tiers'] ?? null) ? $pricing['traffic_tiers'] : [];
        $trafficTier = $this->trafficTierValue($selection['traffic_tier'] ?? 'low');
        $setupFees = is_array($pricing['setup_fees'] ?? null) ? $pricing['setup_fees'] : [];
        $launchTotal = $baseAmount
            + ($extraPages * $perPageAmount)
            + (int) (($trafficTiers[$trafficTier]['amount'] ?? 0))
            + (($selection['lead_module'] ?? false) ? (int) ($addOns['lead_module'] ?? 0) : 0);
        $setupFee = ($selection['seo_copy'] ?? false) ? (int) ($setupFees['seo_copy'] ?? 0) : 0;
        $annualBilling = $this->billingCycleValue($selection['billing_cycle'] ?? 'monthly') === 'annual';

        if ($annualBilling) {
            $annual = $this->annualBillingPriceMeta(
                $launchTotal,
                $setupFee > 0 ? '+'.$this->formatNumber($setupFee).' kr. i opstart for professionel opsætning' : null,
            );

            return [
                'price' => $annual['price'],
                'price_note' => $annual['note'],
                'detail' => $usageSummary !== '' ? $usageSummary : ((int) ($selection['sections'] ?? 1)).' sider',
                'billing_note' => 'Atelier reguleres efter sider og valgte tilvalg.',
            ];
        }

        return [
            'price' => $this->formatPrice($launchTotal, $pricing['prefix'] ?? '', $pricing['suffix'] ?? 'kr.'),
            'price_note' => $setupFee > 0
                ? collect([
                    'vejledende ud fra sider, trafik og tilvalg',
                    '+'.$this->formatNumber($setupFee).' kr. i opstart for professionel opsætning',
                    'ekskl. moms',
                ])->implode(' · ')
                : ($package['price_suffix'] ?? ''),
            'detail' => $usageSummary !== '' ? $usageSummary : ((int) ($selection['sections'] ?? 1)).' sider',
            'billing_note' => 'Atelier reguleres efter sider og valgte tilvalg.',
        ];
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  array<string, mixed>  $pricing
     * @param  array<string, mixed>  $package
     * @return array{price: string, price_note: string, detail: string, billing_note: string}
     */
    private function resolveScalePricing(array $selection, array $pricing, array $package, string $usageSummary): array
    {
        $baseAmount = (int) ($pricing['base_amount'] ?? 0);
        $includedStaff = max(0, (int) ($pricing['included_staff'] ?? 0));
        $staffAmount = (int) ($pricing['staff_amount'] ?? 0);
        $extraStaff = max(0, (int) ($selection['staff'] ?? 0) - $includedStaff);
        $staffTotal = $extraStaff * $staffAmount;
        $locationTotal = $this->resolveTierPrice((int) ($selection['locations'] ?? 1), $pricing['location_tiers'] ?? []);
        $bookingTotal = $this->resolveTierPrice((int) ($selection['bookings'] ?? 0), $pricing['booking_tiers'] ?? []);
        $scaleTotal = $baseAmount + $staffTotal + $locationTotal + $bookingTotal;
        $annualBilling = $this->billingCycleValue($selection['billing_cycle'] ?? 'monthly') === 'annual';

        if ($annualBilling) {
            $annual = $this->annualBillingPriceMeta($scaleTotal, 'starter med 3 mdr. gratis');

            return [
                'price' => $annual['price'],
                'price_note' => $annual['note'],
                'detail' => $usageSummary,
                'billing_note' => 'Studio reguleres efter medarbejdere, lokationer og bookinger.',
            ];
        }

        return [
            'price' => (string) ($pricing['intro_label'] ?? '0 kr. de første 3 måneder'),
            'price_note' => 'derefter '.$this->formatPrice($scaleTotal, $pricing['prefix'] ?? '', $pricing['suffix'] ?? 'kr./måned').' · vejledende · ekskl. moms',
            'detail' => $usageSummary,
            'billing_note' => 'Studio reguleres efter medarbejdere, lokationer og bookinger.',
        ];
    }

    /**
     * @param  array<string, mixed>  $selection
     * @param  array<string, mixed>  $pricing
     * @param  array<string, mixed>  $package
     * @return array{price: string, price_note: string, detail: string, billing_note: string}
     */
    private function resolveBookingTieredPricing(array $selection, array $pricing, array $package, string $usageSummary): array
    {
        $monthlyAmount = $this->resolveTierPrice((int) ($selection['bookings'] ?? 0), $pricing['tiers'] ?? []);

        if ($this->billingCycleValue($selection['billing_cycle'] ?? 'monthly') === 'annual') {
            $annual = $this->annualBillingPriceMeta($monthlyAmount);

            return [
                'price' => $annual['price'],
                'price_note' => $annual['note'],
                'detail' => $usageSummary,
                'billing_note' => 'PlateBook skalerer primært efter antal bookinger.',
            ];
        }

        return [
            'price' => $this->formatPrice($monthlyAmount, $pricing['prefix'] ?? '', $pricing['suffix'] ?? 'kr./måned'),
            'price_note' => $package['price_suffix'] ?? '',
            'detail' => $usageSummary,
            'billing_note' => 'PlateBook skalerer primært efter antal bookinger.',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    private function resolveTierPrice(int $value, array $tiers): int
    {
        if ($tiers === []) {
            return 0;
        }

        foreach ($tiers as $tier) {
            if ($value <= (int) ($tier['up_to'] ?? 0)) {
                return (int) ($tier['amount'] ?? 0);
            }
        }

        return (int) ($tiers[array_key_last($tiers)]['amount'] ?? 0);
    }

    private function formatPrice(int $amount, string $prefix = '', string $suffix = 'kr.'): string
    {
        $parts = array_filter([
            $prefix !== '' ? trim($prefix) : null,
            number_format($amount, 0, ',', '.').' '.trim($suffix),
        ]);

        return implode(' ', $parts);
    }

    private function formatNumber(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return min($max, max($min, $value));
    }

    private function booleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function trafficTierValue(mixed $value): string
    {
        return in_array($value, ['low', 'medium', 'high'], true)
            ? (string) $value
            : 'low';
    }

    private function billingCycleValue(mixed $value): string
    {
        return in_array($value, ['monthly', 'annual'], true)
            ? (string) $value
            : 'monthly';
    }

    private function trafficTierLabel(string $value): string
    {
        return match ($value) {
            'medium' => 'mellem trafik',
            'high' => 'høj trafik',
            default => 'lav trafik',
        };
    }

    /**
     * @return array{price: string, note: string}
     */
    private function annualBillingPriceMeta(int $monthlyAmount, ?string $extraNote = null): array
    {
        $annualAmount = (int) round($monthlyAmount * 12 * 0.88);
        $monthlyEquivalent = (int) round($annualAmount / 12);

        return [
            'price' => $this->formatPrice($annualAmount, '', 'kr./år'),
            'note' => collect([
                'svarer til '.$this->formatPrice($monthlyEquivalent, '', 'kr./md.'),
                '12% rabat',
                'betales årligt',
                $extraNote,
                'ekskl. moms',
            ])->filter()->implode(' · '),
        ];
    }
}
