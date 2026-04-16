<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Support\Sales\PricingPackageCatalog;
use App\Support\Sites\SiteThemes;
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

    public function getStarted(): View
    {
        return view('sales.pages.get-started', [
            'flowSteps' => [
                [
                    'eyebrow' => 'Trin 01',
                    'title' => 'Kortlæg jeres behov',
                    'copy' => 'Kortlæg jeres behov, så I får en løsning og en pris, der passer til jeres forretning.',
                    'tag' => 'Prisoverslag',
                ],
                [
                    'eyebrow' => 'Trin 02',
                    'title' => 'Beregn jeres pris',
                    'copy' => 'Vælg løsning og udfyld den dynamiske prisberegner, så I får et konkret overslag med det samme.',
                    'tag' => 'Konto og kontakt',
                    'action' => [
                        'label' => 'Åbn prisberegner',
                        'href' => route('templates').'#pricing-guide',
                    ],
                ],
                [
                    'eyebrow' => 'Trin 03',
                    'title' => 'Kom i gang med det samme',
                    'copy' => 'Når beregningen er gennemført, kan I oprette konto og gå direkte videre med den løsning, I har valgt.',
                    'tag' => 'Opsaetning',
                ],
                [
                    'eyebrow' => 'Trin 04',
                    'title' => 'Tilpas hjemmeside og booking',
                    'copy' => 'Tilpas indhold, opsætning og booking, så løsningen matcher jeres arbejdsgange og ønsker.',
                    'tag' => 'Tilpasning',
                ],
                [
                    'eyebrow' => 'Trin 05',
                    'title' => 'Vi er klar til at hjælpe',
                    'copy' => 'Har I brug for sparring, står vi klar, så I kommer godt fra start uden unødige forsinkelser.',
                    'tag' => 'Klar til start',
                ],
                [
                    'eyebrow' => 'Trin 06',
                    'title' => 'Gå live',
                    'copy' => 'Når alt er på plads, kan løsningen sættes live, så I er klar til at tage imod kunder online.',
                    'tag' => 'Lancering',
                ],
            ],
            'checkpoints' => [
                'Prisoverslag foer kontooprettelse, saa alle nye spor starter med rigtig kontekst.',
                'Kontoen aabnes foerst, naar beregningen er gennemfoert og klar til at blive gemt.',
                'Flowet er bygget til at foere brugeren videre uden at hoppe frem og tilbage mellem sider.',
            ],
        ]);
    }

    public function templates(Request $request, PricingPackageCatalog $pricingPackageCatalog): View
    {
        $plans = $this->plans();
        $packages = $pricingPackageCatalog->packages($plans);

        return view('sales.pages.templates', [
            'packages' => $packages,
            'comparisonRows' => $pricingPackageCatalog->comparisonRows(),
            'initialSelection' => $pricingPackageCatalog->normalizeSelection(
                $request->query(),
                collect($packages)->mapWithKeys(fn (array $package) => [$package['key'] => $package])->all(),
            ),
        ]);
    }

    public function about(): View
    {
        return view('sales.pages.about', [
            'pillars' => [
                [
                    'title' => 'Vi bygger sammenhaengende loesninger',
                    'copy' => 'Maalet er ikke bare en flot side, men et setup hvor hjemmeside, booking og CMS spiller sammen og giver mening i den daglige drift.',
                ],
                [
                    'title' => 'Vi saelger det, du faktisk kan bruge',
                    'copy' => 'Pakkerne er bygget, sa du kan starte enkelt og bygge videre, naar virksomheden vokser. Det goer baade pris og forventninger lettere at afkode.',
                ],
                [
                    'title' => 'Du bevarer kontrol efter levering',
                    'copy' => 'Kunden skal kunne opdatere indhold, arbejde med sektioner og komme tilbage i loesningen uden at vaere afhængig af et nyt projekt hver gang.',
                ],
            ],
            'highlights' => [
                'Samlet hjemmeside, booking og CMS i samme retning',
                'Pakker der er lettere at forstaa og lettere at saelge',
                'Et kontrolleret CMS, hvor design og kvalitet stadig holdes intakt',
            ],
            'steps' => $this->steps(),
        ]);
    }

    public function designs(): View
    {
        $themes = collect(SiteThemes::all())
            ->map(fn (array $theme, string $key) => [
                'key' => $key,
                'label' => $theme['label'],
                'description' => $theme['description'],
                'vibe' => $theme['vibe'],
                'recommended_for' => $theme['recommended_for'],
            ])
            ->values()
            ->all();

        return view('sales.pages.designs', [
            'themes' => $themes,
            'designNotes' => [
                [
                    'title' => 'Bygget til forskellige udtryk',
                    'copy' => 'Du kan starte med et theme, der matcher salonens stemning, og derefter justere farver, sektioner og indhold uden at begynde forfra.',
                ],
                [
                    'title' => 'Klar til baade templates og custom',
                    'copy' => 'Designsiden viser retningen i universet, men loesningerne kan stadig landes som baade en skarp template og et mere frit Signature-spor.',
                ],
            ],
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
            'launch' => 'Jeg vil gerne høre mere om Atelier fra 199 kr./md. og høre hvordan tilvalg og professionel opsætning passer til min løsning.',
            'scale' => 'Jeg vil gerne høre mere om Studio med 3 mdr. gratis og høre hvordan pris og setup derefter passer til min forretning.',
            'platebook' => 'Jeg vil gerne høre mere om Chairflow fra 49 kr./måned og hvordan prisen skalerer med antal bookinger.',
            'signature' => 'Jeg vil gerne have et vejledende tilbud på Signature fra 5.000 kr. og høre om næste skridt.',
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
                'title' => 'Atelier',
                'badge' => 'Domæne klar',
                'headline' => 'Et professionelt startpunkt for dig, der vil hurtigt online med en løsning, der skaber et godt førstehåndsindtryk og giver plads til at bygge videre, når behovene vokser.',
                'price' => '199 kr./md.',
                'annual_price' => '199 kr./md.',
                'price_suffix' => 'vejledende ud fra sider, trafik og tilvalg · ekskl. moms',
                'annual_suffix' => 'vejledende ud fra sider, trafik og tilvalg · ekskl. moms',
                'pricing' => [
                    'mode' => 'flat',
                    'amount' => 199,
                    'suffix' => 'kr./md.',
                ],
                'visible_fields' => ['locations', 'sections'],
                'points' => [
                    'Professionelt modulopbygget website',
                    'SEO og metadata',
                    'Temabaserede layouts',
                    'Forskellige farvepaletter',
                    'Hosting på vores platform',
                    'Kunde-CMS til indhold og opdateringer',
                    'Nyhedsbrev og leadopsamling',
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
                'eyebrow' => 'Til virksomheder i vækst',
                'title' => 'Studio',
                'badge' => '3 mdr. gratis',
                'headline' => 'Til virksomheder, der vil have booking integreret som en naturlig del af kundeoplevelsen. Studio er bygget med PlateBook, så I kan skabe flere bookinger direkte fra jeres egen hjemmeside.',
                'price' => '0 kr. de første 3 måneder',
                'annual_price' => '299 kr./måned',
                'price_suffix' => 'derefter vejledende · ekskl. moms',
                'annual_suffix' => '0 kr. de første 3 måneder · derefter vejledende · ekskl. moms',
                'pricing' => [
                    'mode' => 'scale_configurable',
                    'intro_label' => '0 kr. de første 3 måneder',
                    'base_amount' => 299,
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
                    'Online booking direkte på hjemmesiden',
                    'Medarbejdere og lokationer i samme løsning',
                    [
                        'label' => 'Automatiske bookingbekræftelser og påmindelser',
                        'note' => 'Fra 0,7 DKK pr. SMS-besked',
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
                'eyebrow' => 'Til skræddersyede projekter',
                'title' => 'Signature',
                'badge' => 'Skræddersyet',
                'headline' => 'Når design, funktioner og oplevelse skal formes mere frit omkring virksomheden og det udtryk du vil stå med.',
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
                'eyebrow' => 'Til dig med eksisterende hjemmeside',
                'title' => 'Chairflow',
                'badge' => 'Booking only',
                'headline' => 'Et selvstændigt bookingsystem, hvis du vil beholde din nuværende hjemmeside og tilføje booking uden at bygge alt om.',
                'price' => '49 kr./måned',
                'annual_price' => '49 kr./måned',
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
                    'Selvstændigt bookingsystem',
                    'Hosting på vores platform',
                    'Let integration på eksisterende hjemmeside',
                    'Medarbejdere og behandlinger samlet ét sted',
                    [
                        'label' => 'Automatiske bookingbekræftelser og påmindelser',
                        'note' => 'Fra 0,7 DKK pr. SMS-besked',
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
