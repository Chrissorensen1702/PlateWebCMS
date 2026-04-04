<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Support\Collection;
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
        return view('sales.pages.templates', [
            'plans' => $this->plans()->where('is_custom', false)->values(),
            'notes' => [
                [
                    'title' => 'God til hurtige launches',
                    'copy' => 'Template-pakker fungerer bedst, naar kunden vil hurtigt i luften med et professionelt udtryk og et kontrolleret CMS.',
                ],
                [
                    'title' => 'Let at saelge og gentage',
                    'copy' => 'Du kan genbruge samme sideopbygning, samme indholdsblokke og samme onboarding, saa leveringen bliver mere effektiv.',
                ],
                [
                    'title' => 'Nemt at opgradere senere',
                    'copy' => 'En templateside kan senere udvides med flere sider, flere sektioner eller loeftes til et mere custom spor.',
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

    public function contact(): View
    {
        return view('sales.pages.contact', [
            'plans' => $this->plans(),
            'contactPoints' => [
                [
                    'title' => 'Template eller custom?',
                    'copy' => 'Du behoever ikke kende den praecise pakke endnu. Beskriv bare projektet, saa finder vi den rigtige retning sammen.',
                ],
                [
                    'title' => 'Klar til lead-pipeline',
                    'copy' => 'Alle forespoergsler bliver gemt i databasen, saa salgssiden og CMS-projektet allerede arbejder sammen.',
                ],
                [
                    'title' => 'Godt sted at starte dialogen',
                    'copy' => 'Formularen er oplagt til at samle scope, budgetforventning og hvilke sektioner kunden gerne vil kunne redigere selv.',
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
}
