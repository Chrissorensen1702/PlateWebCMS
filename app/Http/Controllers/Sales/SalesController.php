<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function home(): View
    {
        $plans = $this->plans();

        return view('sales.pages.home', [
            'templatePlans' => $plans->where('is_custom', false)->values(),
            'customTrackCount' => $plans->where('is_custom', true)->count(),
            'homeFeatures' => [
                [
                    'emoji' => '🌐',
                    'title' => 'Hjemmeside',
                    'points' => [
                        'Modulopbygget sidebygning',
                        'Farver der matcher dit brand',
                        'Kunde-CMS til hurtige opdateringer',
                        'Domæne og DNS sat rigtigt op',
                    ],
                    'href' => route('templates'),
                ],
                [
                    'emoji' => '📅',
                    'logo' => 'platebook',
                    'id' => 'produkt-bookingsystem',
                    'title' => 'Bookingsystem',
                    'points' => [
                        'Integreret booking på din hjemmeside',
                        'Medarbejdere og kompetencer samlet',
                        'Overskuelig vagtplan',
                        'Aktivitetslog med historik',
                    ],
                    'href' => route('contact'),
                ],
                [
                    'emoji' => '📊',
                    'title' => 'Statistik',
                    'points' => [
                        'Indblik i populære ydelser',
                        'Omsætning og bruttoavance pr. periode',
                        'NPS og kundeanmeldelser',
                    ],
                    'href' => route('contact'),
                ],
                [
                    'emoji' => '💸',
                    'title' => 'Betaling',
                    'points' => [
                        'Hent ydelser direkte til betaling',
                        'Kortbetaling',
                        'Kontant betaling',
                        'MobilePay',
                    ],
                    'href' => route('contact'),
                ],
            ],
            'highlights' => [
                [
                    'title' => 'Templates med fart i leveringen',
                    'copy' => 'Fa faste pakker, tydelige rammer og et setup der er hurtigt at saelge igen og igen.',
                    'href' => route('templates'),
                    'label' => 'Se template-pakker',
                ],
                [
                    'title' => 'Custom build uden nyt bagland hver gang',
                    'copy' => 'Specialdesignede sites kan stadig lande i samme CMS-fundament, saa du genbruger login og indholdsmoduler.',
                    'href' => route('custom-build'),
                    'label' => 'Laes om custom build',
                ],
                [
                    'title' => 'Kunde-CMS som en fast del af pakken',
                    'copy' => 'Kunden redigerer kun de sektioner du har gjort tilgaengelige, mens layout og kvalitet forbliver under kontrol.',
                    'href' => route('sales.customer-cms'),
                    'label' => 'Se CMS-retningen',
                ],
            ],
            'steps' => $this->steps(),
        ]);
    }

    public function templates(): View
    {
        $plans = $this->plans();

        return view('sales.pages.templates', [
            'packages' => $this->packageShowcase($plans),
            'comparisonRows' => $this->packageComparisonRows(),
        ]);
    }

    public function customBuild(): View
    {
        return view('sales.pages.custom-build', [
            'plan' => $this->plans()->firstWhere('is_custom', true),
            'pillars' => [
                [
                    'title' => 'Skraeddersyet design',
                    'copy' => 'Frontenden bliver bygget omkring kundens brand, maalgruppe og funktionelle behov i stedet for en fast template.',
                ],
                [
                    'title' => 'Samme CMS-kerne',
                    'copy' => 'Selv om siden er unik, kan kunden stadig logge ind og redigere de sektioner du frigiver i et kendt flow.',
                ],
                [
                    'title' => 'Klar til specialfunktioner',
                    'copy' => 'Naar projektet kraever booking, forms, integrationer eller saerlige sektioner, er custom build det naturlige spor.',
                ],
            ],
            'steps' => $this->steps(),
        ]);
    }

    public function cms(): View
    {
        return view('sales.pages.customer-cms', [
            'cmsFeatures' => [
                'Roller til developere og kunder',
                'Pakker, der kan saelges fra den offentlige side',
                'Lead-opfangning direkte i databasen',
                'Sites koblet til plan og kunde',
                'Klar til sider, sektioner og mediehaandtering',
            ],
            'principles' => [
                [
                    'title' => 'Kunden redigerer indhold, ikke layout',
                    'copy' => 'Det er den vigtigste forskel fra et frit CMS. Du beskytter designet og giver kun adgang til det, der skal vedligeholdes.',
                ],
                [
                    'title' => 'Samme login paa tvaers af projekter',
                    'copy' => 'Template-kunder og custom-kunder kan ligge i samme platform, saa du undgaar at opfinde et nyt bagland for hver leverance.',
                ],
                [
                    'title' => 'Bygget til dine egne templates',
                    'copy' => 'Systemet kan formes omkring dine faste sektioner og workflows i stedet for at vaere et generisk alt-kan-alt-CMS.',
                ],
            ],
            'steps' => $this->steps(),
        ]);
    }

    public function mobileApp(): View
    {
        return view('sales.pages.mobile-app');
    }

    public function contact(Request $request): View
    {
        $plans = $this->plans();
        $selectedPlanId = $request->integer('plan_id') ?: null;
        $selectedPackage = (string) $request->query('package', '');
        $trialIntent = $request->boolean('trial');

        $defaultLeadMessage = match ($selectedPackage) {
            'launch' => 'Jeg vil gerne høre mere om Starter-pakken til 69 kr./måned inkl. .dk-domæne.',
            'scale' => 'Jeg vil gerne høre mere om hjemmeside + booking med 0 kr. de første 3 måneder og prisen derefter.',
            'platebook' => 'Jeg vil gerne høre mere om PlateBook fra 49 kr./måned og hvordan prisen skalerer med antal bookinger.',
            'signature' => 'Jeg vil gerne have et vejledende tilbud på en custom-løsning fra 5.000 kr. og høre om næste skridt.',
            default => '',
        };

        if ($trialIntent && $defaultLeadMessage === '') {
            $defaultLeadMessage = 'Jeg vil gerne høre mere om pakkerne og få bekræftet den løsning, der passer bedst til min forretning.';
        }

        return view('sales.pages.contact', [
            'plans' => $plans,
            'selectedPlanId' => $selectedPlanId,
            'defaultLeadMessage' => $defaultLeadMessage,
            'contactPoints' => [
                [
                    'title' => 'Start med et vejledende tilbud',
                    'copy' => 'Du kan tage udgangspunkt i den anbefalede loesning, starte dialogen og faa prisen bekraeftet efter en hurtig gennemgang.',
                ],
                [
                    'title' => 'Pris efter brug og behov',
                    'copy' => 'Booking-pakkerne vaegter primaert antal bookinger, mens custom gaar direkte til tilbud og scope-afklaring.',
                ],
                [
                    'title' => 'Endelig loesning bagefter',
                    'copy' => 'Naar vi har gennemgaaet behovet, bekraefter vi den endelige retning og pris, saa forventninger og levering passer sammen.',
                ],
            ],
        ]);
    }

    /**
     * @return Collection<int, Plan>
     */
    private function plans(): Collection
    {
        return Plan::query()
            ->active()
            ->ordered()
            ->with('featureItems')
            ->get();
    }

    /**
     * @return array<int, array{eyebrow: string, title: string, copy: string}>
     */
    private function steps(): array
    {
        return [
            [
                'eyebrow' => '01',
                'title' => 'Du vælger løsning',
                'copy' => 'Vi finder sammen ud af, om du skal bruge en template-løsning eller et mere skræddersyet custom build.',
            ],
            [
                'eyebrow' => '02',
                'title' => 'Vi bygger og sætter op',
                'copy' => 'Vi designer siden, kobler booking på og gør de dele klar, som du senere selv skal kunne redigere.',
            ],
            [
                'eyebrow' => '03',
                'title' => 'Du vedligeholder selv',
                'copy' => 'Når siden er live, kan du opdatere indhold, billeder og udvalgte sektioner i CMS’et uden at bryde layoutet.',
            ],
        ];
    }

    /**
     * @param  Collection<int, Plan>  $plans
     * @return array<int, array<string, mixed>>
     */
    private function packageShowcase(Collection $plans): array
    {
        $templatePlans = $plans->where('is_custom', false)->values();
        $launch = $templatePlans->get(0);
        $scale = $templatePlans->get(1);
        $signature = $plans->firstWhere('is_custom', true);

        return [
            [
                'key' => 'launch',
                'eyebrow' => 'Til dig der vil hurtigt i gang',
                'title' => 'Starter',
                'badge' => 'Inkl. .dk-domæne',
                'headline' => 'Et enkelt og professionelt startpunkt, når du vil hurtigt online med en side, der er nem at arbejde videre med.',
                'price' => '69 kr./måned',
                'annual_price' => '69 kr./måned',
                'delivery' => $launch?->build_time ?? 'Hurtig levering',
                'price_suffix' => 'inkl. .dk-domæne · ekskl. moms',
                'annual_suffix' => 'inkl. .dk-domæne · ekskl. moms',
                'pricing' => [
                    'mode' => 'flat',
                    'amount' => 69,
                    'suffix' => 'kr./måned',
                ],
                'visible_fields' => ['locations', 'sections'],
                'points' => [
                    'Professionel hjemmeside',
                    'Kunde-CMS til indhold',
                    '.dk-domæne inkluderet',
                ],
                'href' => route('contact', ['plan_id' => $launch?->id, 'package' => 'launch']),
                'label' => 'Vælg Starter',
                'tone' => 'launch',
                'featured' => false,
            ],
            [
                'key' => 'scale',
                'eyebrow' => 'Til virksomheder i vækst',
                'title' => 'Scale',
                'badge' => '3 mdr. gratis',
                'headline' => 'Når du vil have mere branding, mere indhold og booking tænkt direkte ind i løsningen fra start.',
                'price' => '89 kr./måned',
                'annual_price' => '89 kr./måned',
                'delivery' => $scale?->build_time ?? 'Efter aftale',
                'price_suffix' => '0 kr. de første 3 måneder · derefter vejledende · ekskl. moms',
                'annual_suffix' => '0 kr. de første 3 måneder · derefter vejledende · ekskl. moms',
                'pricing' => [
                    'mode' => 'intro_booking_tiered',
                    'suffix' => 'kr./måned',
                    'intro_label' => '0 kr. de første 3 måneder',
                    'tiers' => [
                        ['up_to' => 300, 'amount' => 89],
                        ['up_to' => 1000, 'amount' => 109],
                        ['up_to' => 2500, 'amount' => 139],
                        ['up_to' => 5000, 'amount' => 179],
                    ],
                ],
                'visible_fields' => ['bookings', 'locations', 'staff', 'sections'],
                'points' => [
                    'Booking direkte på hjemmesiden',
                    'Flere sider og stærkere branding',
                    'Leadflow og tydelige CTA’er',
                ],
                'href' => route('contact', ['plan_id' => $scale?->id, 'package' => 'scale', 'trial' => 1]),
                'label' => 'Start gratis i 3 måneder',
                'tone' => 'scale',
                'featured' => true,
            ],
            [
                'key' => 'signature',
                'eyebrow' => 'Til skræddersyede projekter',
                'title' => 'Custom',
                'badge' => 'Skræddersyet',
                'headline' => 'Når design, funktioner og oplevelse skal formes mere frit omkring virksomheden og det udtryk du vil stå med.',
                'price' => 'Fra 5.000 kr.',
                'annual_price' => 'Fra 5.000 kr.',
                'delivery' => $signature?->build_time ?? 'Efter tilbud',
                'price_suffix' => 'projektpris · vejledende efter scope',
                'annual_suffix' => 'projektpris · vejledende efter scope',
                'pricing' => [
                    'mode' => 'custom_quote',
                    'amount' => 5000,
                    'prefix' => 'Fra',
                    'suffix' => 'kr.',
                ],
                'visible_fields' => ['locations', 'sections', 'staff', 'bookings'],
                'points' => [
                    'Custom design og struktur',
                    'Særlige funktioner efter behov',
                    'Tæt sparring gennem forløbet',
                ],
                'href' => route('contact', ['plan_id' => $signature?->id, 'package' => 'signature']),
                'label' => 'Få et tilbud',
                'tone' => 'signature',
                'featured' => false,
            ],
            [
                'key' => 'platebook',
                'eyebrow' => 'Til dig med eksisterende hjemmeside',
                'title' => 'PlateBook',
                'badge' => 'Booking only',
                'headline' => 'Et selvstændigt bookingsystem, hvis du vil beholde din nuværende hjemmeside og tilføje booking uden at bygge alt om.',
                'price' => '49 kr./måned',
                'annual_price' => '49 kr./måned',
                'delivery' => 'Afhænger af setup',
                'price_suffix' => 'vejledende efter antal bookinger · ekskl. moms',
                'annual_suffix' => 'vejledende efter antal bookinger · ekskl. moms',
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
                    'Booking på eksisterende side',
                    'Vagtplan og medarbejdere samlet',
                    'Aktivitetslog og overblik',
                ],
                'href' => route('contact', ['package' => 'platebook']),
                'label' => 'Kom i gang med booking',
                'tone' => 'platebook',
                'featured' => false,
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, values: array<string, bool|string>}>
     */
    private function packageComparisonRows(): array
    {
        return [
            [
                'label' => 'Professionel hjemmeside',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Kunde-CMS til indhold',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Booking integreret på siden',
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Farver og branding',
                'values' => [
                    'launch' => 'Basis',
                    'scale' => 'Udvidet',
                    'signature' => 'Fri',
                    'platebook' => false,
                ],
            ],
            [
                'label' => 'Domæne og DNS opsætning',
                'values' => [
                    'launch' => true,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => 'Tilvalg',
                ],
            ],
            [
                'label' => 'Medarbejdere og kompetencer',
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Simpel vagtplan',
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Aktivitetslog og drifts-overblik',
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Statistik og indsigt',
                'values' => [
                    'launch' => false,
                    'scale' => true,
                    'signature' => true,
                    'platebook' => true,
                ],
            ],
            [
                'label' => 'Særlige funktioner efter behov',
                'values' => [
                    'launch' => false,
                    'scale' => 'Tilvalg',
                    'signature' => true,
                    'platebook' => false,
                ],
            ],
        ];
    }

}
