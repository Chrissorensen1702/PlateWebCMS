<?php

namespace App\Support\Sites;

use App\Models\SitePage;
use App\Models\SitePageDraft;
use InvalidArgumentException;

class SitePageTemplates
{
    /**
     * @return array<string, array{
     *     label: string,
     *     description: string,
     *     areas: list<string>,
     *     area_templates: list<array{type: string, key: string, label: string, data: array<string, mixed>}>
     * }>
     */
    public static function availableForTheme(string $theme): array
    {
        $availableAreaTypes = array_keys(SitePageAreaBlueprints::availableForTheme($theme));

        return collect(self::definitions())
            ->filter(function (array $template) use ($availableAreaTypes): bool {
                return collect($template['area_templates'])
                    ->pluck('type')
                    ->every(fn (string $type): bool => in_array($type, $availableAreaTypes, true));
            })
            ->all();
    }

    /**
     * @return array{
     *     label: string,
     *     description: string,
     *     areas: list<string>,
     *     area_templates: list<array{type: string, key: string, label: string, data: array<string, mixed>}>
     * }
     */
    public static function definition(string $template): array
    {
        $definition = self::definitions()[$template] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException("Unknown page template [{$template}].");
        }

        return $definition;
    }

    public static function createForPage(SitePage|SitePageDraft $page, string $template): void
    {
        $definition = self::availableForTheme($page->site->theme)[$template] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException("Page template [{$template}] is not available for theme [{$page->site->theme}].");
        }

        foreach ($definition['area_templates'] as $index => $areaTemplate) {
            SitePageAreaBlueprints::createForPage($page, $areaTemplate['type'], [
                'key' => $areaTemplate['key'],
                'label' => $areaTemplate['label'],
                'sort_order' => $index + 1,
                'data' => $areaTemplate['data'],
            ]);
        }
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     description: string,
     *     areas: list<string>,
     *     area_templates: list<array{type: string, key: string, label: string, data: array<string, mixed>}>
     * }>
     */
    private static function definitions(): array
    {
        return [
            'contact' => [
                'label' => 'Kontaktside',
                'description' => 'Faerdig side med intro, praktisk info og kontaktmulighed.',
                'areas' => ['Topsektion', 'Infoboks', 'Kontaktsektion'],
                'area_templates' => [
                    [
                        'type' => 'hero',
                        'key' => 'contact-hero',
                        'label' => 'Overskrift',
                        'data' => [
                            'eyebrow' => 'Kontakt',
                            'title' => 'Kontakt os',
                            'copy' => 'Her kan besoegende hurtigt forstaa, hvordan de kommer i kontakt med virksomheden.',
                            'primary_cta_label' => 'Kontakt os',
                            'primary_cta_href' => '/kontakt',
                            'secondary_cta_label' => 'Send en forespoergsel',
                            'secondary_cta_href' => '/kontakt',
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'contact-info',
                        'label' => 'Infoboks',
                        'data' => [
                            'eyebrow' => 'Info',
                            'title' => 'Saadan faar du fat i os',
                            'copy' => 'Brug denne boks til korte oplysninger om aabningstider, svartid eller hvad kunden kan forvente.',
                            'items' => [
                                'Svar inden for 24 timer',
                                'Telefon og email er aabne pa hverdage',
                                'Beskrivelse af naeste skridt i dialogen',
                            ],
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'key' => 'contact-form',
                        'label' => 'Kontaktsektion',
                        'data' => [
                            'eyebrow' => 'Skriv til os',
                            'title' => 'Send en forespoergsel',
                            'copy' => 'Afslut siden med en tydelig invitation til at tage kontakt.',
                            'cta_label' => 'Kontakt os',
                            'cta_href' => '/kontakt',
                        ],
                    ],
                ],
            ],
            'about' => [
                'label' => 'Om os-side',
                'description' => 'Fortael virksomhedens historie, vaerdier og naeste skridt.',
                'areas' => ['Intro', 'Historie', 'Vaerdier', 'Kontaktsektion'],
                'area_templates' => [
                    [
                        'type' => 'hero',
                        'key' => 'about-hero',
                        'label' => 'Intro',
                        'data' => [
                            'eyebrow' => 'Om os',
                            'title' => 'Laer virksomheden at kende',
                            'copy' => 'Denne topblok saetter scenen og fortaeller kort, hvem virksomheden er til for.',
                            'primary_cta_label' => 'Kontakt os',
                            'primary_cta_href' => '/kontakt',
                            'secondary_cta_label' => 'Se services',
                            'secondary_cta_href' => '/services',
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'about-story',
                        'label' => 'Historie',
                        'data' => [
                            'eyebrow' => 'Historie',
                            'title' => 'Hvorfor vi startede',
                            'copy' => 'Brug denne blok til virksomhedens baggrund, motivation og udvikling.',
                            'items' => [
                                'Hvordan virksomheden opstod',
                                'Hvilket problem I loeser',
                                'Hvad der goer jer anderledes',
                            ],
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'about-values',
                        'label' => 'Vaerdier',
                        'data' => [
                            'eyebrow' => 'Vaerdier',
                            'title' => 'Det staar vi for',
                            'copy' => 'Brug boksen til virksomhedens tilgang, styrker og samarbejdsform.',
                            'items' => [
                                'Tydelig kommunikation',
                                'Kvalitet i leverancen',
                                'Taet kundedialog',
                            ],
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'key' => 'about-cta',
                        'label' => 'Kontaktsektion',
                        'data' => [
                            'eyebrow' => 'Naeste skridt',
                            'title' => 'Vil du hoere mere?',
                            'copy' => 'Afslut siden med en enkel invitation til dialog eller tilbud.',
                            'cta_label' => 'Tag kontakt',
                            'cta_href' => '/kontakt',
                        ],
                    ],
                ],
            ],
            'services' => [
                'label' => 'Services-side',
                'description' => 'Vis ydelser, proces og en tydelig vej til tilbud eller kontakt.',
                'areas' => ['Intro', 'Ydelser', 'Proces', 'Kontaktsektion'],
                'area_templates' => [
                    [
                        'type' => 'hero',
                        'key' => 'services-hero',
                        'label' => 'Intro',
                        'data' => [
                            'eyebrow' => 'Services',
                            'title' => 'Det kan vi hjaelpe med',
                            'copy' => 'Brug toppen til et hurtigt overblik over virksomhedens vigtigste ydelser.',
                            'primary_cta_label' => 'Faa et tilbud',
                            'primary_cta_href' => '/kontakt',
                            'secondary_cta_label' => 'Kontakt os',
                            'secondary_cta_href' => '/kontakt',
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'services-list',
                        'label' => 'Ydelser',
                        'data' => [
                            'eyebrow' => 'Overblik',
                            'title' => 'Vores ydelser',
                            'copy' => 'Brug denne blok til at beskrive hovedydelserne paa en let skimbar maade.',
                            'items' => [
                                'Ydelse 1 med kort forklaring',
                                'Ydelse 2 med kort forklaring',
                                'Ydelse 3 med kort forklaring',
                            ],
                            'service_prices' => [
                                'Fra 499 kr.',
                                'Fra 799 kr.',
                                'Pris efter aftale',
                            ],
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'services-process',
                        'label' => 'Proces',
                        'data' => [
                            'eyebrow' => 'Saadan arbejder vi',
                            'title' => 'Fra foerste kontakt til levering',
                            'copy' => 'Brug denne blok til at skabe tryghed om processen og forventningsafstemme.',
                            'items' => [
                                'Afklaring af behov',
                                'Plan og tilbud',
                                'Levering og opfoelgning',
                            ],
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'key' => 'services-cta',
                        'label' => 'Kontaktsektion',
                        'data' => [
                            'eyebrow' => 'Klar til dialog',
                            'title' => 'Lad os tale om opgaven',
                            'copy' => 'Afslut med en tydelig invitation til tilbud, moede eller kontakt.',
                            'cta_label' => 'Faa et tilbud',
                            'cta_href' => '/kontakt',
                        ],
                    ],
                ],
            ],
            'landing' => [
                'label' => 'Landingpage',
                'description' => 'Kompakt side med budskab, fordele og en tydelig afslutning.',
                'areas' => ['Intro', 'Fordele', 'Kontaktsektion'],
                'area_templates' => [
                    [
                        'type' => 'hero',
                        'key' => 'landing-hero',
                        'label' => 'Intro',
                        'data' => [
                            'eyebrow' => 'Landingpage',
                            'title' => 'Det vigtigste budskab foerst',
                            'copy' => 'Brug topsektionen til kampagne, produkt eller et konkret tilbud.',
                            'primary_cta_label' => 'Kom i gang',
                            'primary_cta_href' => '/kontakt',
                            'secondary_cta_label' => 'Laes mere',
                            'secondary_cta_href' => '#landing-highlights',
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'landing-highlights',
                        'label' => 'Fordele',
                        'data' => [
                            'eyebrow' => 'Fordele',
                            'title' => 'Hvorfor vaelge os',
                            'copy' => 'Brug afsnittet til 2-4 korte fordele der styrker beslutningen.',
                            'items' => [
                                'Klar og konkret vaerdi',
                                'Tryg proces',
                                'Tydelig handling',
                            ],
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'key' => 'landing-conversion',
                        'label' => 'Kontaktsektion',
                        'data' => [
                            'eyebrow' => 'Naeste skridt',
                            'title' => 'Er du klar til at komme videre?',
                            'copy' => 'Afslut landingpagen med en enkel og tydelig handling.',
                            'cta_label' => 'Book en snak',
                            'cta_href' => '/kontakt',
                        ],
                    ],
                ],
            ],
            'pricing' => [
                'label' => 'Prisside',
                'description' => 'Vis pakker, prisniveauer og inkluderet indhold på en side med tydelig konvertering.',
                'areas' => ['Intro', 'Nøgletal', 'Det får du', 'Citat', 'Kontaktsektion'],
                'area_templates' => [
                    [
                        'type' => 'hero',
                        'key' => 'pricing-hero',
                        'label' => 'Intro',
                        'data' => [
                            'eyebrow' => 'Priser',
                            'title' => 'Få et klart overblik over mulighederne',
                            'copy' => 'Brug toppen til at vise prisniveau, målgruppe eller hvad kunden kan forvente.',
                            'heading_size' => 'standard',
                            'text_align' => 'center',
                            'button_align' => 'center',
                            'secondary_cta_mode' => 'hide',
                            'primary_cta_label' => 'Få et tilbud',
                            'primary_cta_href' => '/kontakt',
                            'secondary_cta_label' => 'Se mere',
                            'secondary_cta_href' => '/kontakt',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'key' => 'pricing-packages',
                        'label' => 'Nøgletal',
                        'data' => [
                            'eyebrow' => 'Pakker',
                            'title' => 'Vælg den løsning der passer',
                            'copy' => 'Brug denne blok til tre prisniveauer eller pakker med korte forklaringer.',
                            'display_style' => 'cards',
                            'section_tone' => 'accent',
                            'items' => [
                                'Fra 6.900 kr | Basis til små behov og en enkel start',
                                'Fra 12.900 kr | Standard til virksomheder der vil have mere dybde',
                                'Fra 19.900 kr | Premium til større behov med ekstra sparring',
                            ],
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'pricing-included',
                        'label' => 'Det får du',
                        'data' => [
                            'eyebrow' => 'Inkluderet',
                            'title' => 'Det er med i løsningen',
                            'copy' => 'Gør det tydeligt hvad kunden får, og hvad processen indeholder.',
                            'text_align' => 'left',
                            'items_style' => 'list',
                            'section_tone' => 'default',
                            'items' => [
                                'Fast onboarding og tydelig forventningsafstemning',
                                'Konkrete leverancer med klare deadlines',
                                'Opfølgning og sparring efter levering',
                            ],
                        ],
                    ],
                    [
                        'type' => 'quote',
                        'key' => 'pricing-quote',
                        'label' => 'Citat',
                        'data' => [
                            'eyebrow' => 'Kundeoplevelse',
                            'quote_text' => 'Prissiden gjorde det meget lettere for vores kunder at forstå forskellen på pakkerne og vælge den rigtige løsning.',
                            'quote_author' => 'Mette Larsen',
                            'quote_role' => 'Indehaver, Example Studio',
                            'text_align' => 'center',
                            'section_tone' => 'accent',
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'key' => 'pricing-cta',
                        'label' => 'Kontaktsektion',
                        'data' => [
                            'eyebrow' => 'Klar til næste skridt',
                            'title' => 'Vil du have en konkret pris?',
                            'copy' => 'Afslut siden med en enkel vej til tilbud eller afklarende spørgsmål.',
                            'layout_style' => 'center',
                            'section_tone' => 'accent',
                            'show_phone' => '0',
                            'cta_label' => 'Få et tilbud',
                            'cta_href' => '/kontakt',
                        ],
                    ],
                ],
            ],
            'faq' => [
                'label' => 'FAQ-side',
                'description' => 'Saml de vigtigste spørgsmål og svar, så kunderne hurtigt finder svar og kommer videre.',
                'areas' => ['Intro', 'Spørgsmål og svar', 'Citat', 'Kontaktsektion'],
                'area_templates' => [
                    [
                        'type' => 'hero',
                        'key' => 'faq-hero',
                        'label' => 'Intro',
                        'data' => [
                            'eyebrow' => 'FAQ',
                            'title' => 'Ofte stillede spørgsmål',
                            'copy' => 'Brug introen til at hjælpe besøgende hurtigt videre til svarene.',
                            'heading_size' => 'standard',
                            'text_align' => 'center',
                            'button_align' => 'center',
                            'secondary_cta_mode' => 'hide',
                            'primary_cta_label' => 'Kontakt os',
                            'primary_cta_href' => '/kontakt',
                            'secondary_cta_label' => 'Læs mere',
                            'secondary_cta_href' => '/kontakt',
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'key' => 'faq-questions',
                        'label' => 'Spørgsmål og svar',
                        'data' => [
                            'eyebrow' => 'Spørgsmål',
                            'title' => 'Det spørger kunderne typisk om',
                            'copy' => 'Skriv spørgsmål og svar i et format der er let at skimme.',
                            'layout_style' => 'stacked',
                            'section_tone' => 'default',
                            'items' => [
                                'Hvordan foregår opstarten? | Vi starter med en kort afklaring og vender tilbage med et oplæg.',
                                'Hvor hurtigt kan I gå i gang? | Det afhænger af opgaven, men vi giver altid en realistisk tidsplan.',
                                'Kan vi få en fast pris? | Ja, når opgaven er afklaret, kan vi sende et konkret tilbud.',
                            ],
                        ],
                    ],
                    [
                        'type' => 'quote',
                        'key' => 'faq-quote',
                        'label' => 'Citat',
                        'data' => [
                            'eyebrow' => 'Tryghed',
                            'quote_text' => 'Vi bruger FAQ-siden som et sted hvor kunderne hurtigt kan få ro på deres vigtigste spørgsmål, før de tager kontakt.',
                            'quote_author' => 'Jonas Mikkelsen',
                            'quote_role' => 'Projektleder',
                            'text_align' => 'center',
                            'section_tone' => 'accent',
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'key' => 'faq-cta',
                        'label' => 'Kontaktsektion',
                        'data' => [
                            'eyebrow' => 'Mangler du et svar?',
                            'title' => 'Så hjælper vi gerne videre',
                            'copy' => 'Afslut siden med en direkte vej til kontakt, hvis kunden stadig er i tvivl.',
                            'layout_style' => 'split',
                            'section_tone' => 'default',
                            'show_phone' => '1',
                            'cta_label' => 'Kontakt os',
                            'cta_href' => '/kontakt',
                        ],
                    ],
                ],
            ],
            'cases' => [
                'label' => 'Case-side',
                'description' => 'Vis resultater, samarbejdet og næste skridt på en mere tillidsopbyggende måde.',
                'areas' => ['Intro', 'Nøgletal', 'Samarbejdet', 'Citat', 'Kontaktsektion'],
                'area_templates' => [
                    [
                        'type' => 'hero',
                        'key' => 'case-hero',
                        'label' => 'Intro',
                        'data' => [
                            'eyebrow' => 'Case',
                            'title' => 'Se hvordan vi løste opgaven',
                            'copy' => 'Brug topsektionen til at sætte scenen med kunde, udfordring og resultat.',
                            'heading_size' => 'large',
                            'text_align' => 'left',
                            'button_align' => 'left',
                            'secondary_cta_mode' => 'show',
                            'primary_cta_label' => 'Book en snak',
                            'primary_cta_href' => '/kontakt',
                            'secondary_cta_label' => 'Kontakt os',
                            'secondary_cta_href' => '/kontakt',
                        ],
                    ],
                    [
                        'type' => 'stats',
                        'key' => 'case-results',
                        'label' => 'Nøgletal',
                        'data' => [
                            'eyebrow' => 'Resultater',
                            'title' => 'Det kunden fik ud af samarbejdet',
                            'copy' => 'Vis 2-4 tydelige resultater, gevinster eller forbedringer i et mere visuelt format.',
                            'display_style' => 'cards',
                            'section_tone' => 'accent',
                            'items' => [
                                '+42% | Flere relevante henvendelser',
                                '3 uger | Fra afklaring til lancering',
                                '98% | Positiv feedback på det nye udtryk',
                            ],
                        ],
                    ],
                    [
                        'type' => 'content',
                        'key' => 'case-process',
                        'label' => 'Samarbejdet',
                        'data' => [
                            'eyebrow' => 'Samarbejdet',
                            'title' => 'Sådan kom vi i mål',
                            'copy' => 'Brug dette afsnit til at forklare proces, leverancer og samarbejdsform.',
                            'text_align' => 'left',
                            'items_style' => 'list',
                            'section_tone' => 'default',
                            'items' => [
                                'Først afklaring af mål og behov',
                                'Derefter design, indhold og tilpasninger',
                                'Til sidst lancering og opfølgning',
                            ],
                        ],
                    ],
                    [
                        'type' => 'quote',
                        'key' => 'case-quote',
                        'label' => 'Citat',
                        'data' => [
                            'eyebrow' => 'Kundens ord',
                            'quote_text' => 'Det nye setup gjorde det meget lettere for os at forklare vores værdi og få de rigtige kunder i tale.',
                            'quote_author' => 'Camilla Holm',
                            'quote_role' => 'Direktør, Client Name',
                            'text_align' => 'left',
                            'section_tone' => 'accent',
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'key' => 'case-cta',
                        'label' => 'Kontaktsektion',
                        'data' => [
                            'eyebrow' => 'Vil du have samme resultat?',
                            'title' => 'Lad os tale om din opgave',
                            'copy' => 'Afslut med en tydelig invitation til næste samtale eller et tilbud.',
                            'layout_style' => 'split',
                            'section_tone' => 'default',
                            'show_phone' => '1',
                            'cta_label' => 'Kontakt os',
                            'cta_href' => '/kontakt',
                        ],
                    ],
                ],
            ],
        ];
    }
}
