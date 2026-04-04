<?php

namespace App\Support\Sites;

use App\Models\SitePage;
use App\Models\SitePageDraft;
use App\Models\SitePageArea;
use App\Models\SitePageDraftArea;
use InvalidArgumentException;
use Illuminate\Support\Str;

class SitePageAreaBlueprints
{
    /**
     * @return array<string, array{label: string, description: string, key_base: string, category: string}>
     */
    public static function availableForTheme(string $theme): array
    {
        return collect(self::definitions())
            ->filter(fn (array $definition, string $type): bool => view()->exists("sites.themes.{$theme}.areas.{$type}") || view()->exists("sites.themes.base.areas.{$type}"))
            ->all();
    }

    /**
     * @return array<string, array{label: string, modules: list<array{type: string, label: string, description: string}>}>
     */
    public static function groupedForTheme(string $theme): array
    {
        $available = self::availableForTheme($theme);

        return collect(self::categories())
            ->map(function (array $categoryDefinition, string $categoryKey) use ($available): ?array {
                $modules = collect($available)
                    ->filter(fn (array $definition): bool => ($definition['category'] ?? null) === $categoryKey)
                    ->map(fn (array $definition, string $type): array => [
                        'type' => $type,
                        'label' => $definition['label'],
                        'description' => $definition['description'],
                    ])
                    ->values()
                    ->all();

                if ($modules === []) {
                    return null;
                }

                return [
                    'label' => $categoryDefinition['label'],
                    'modules' => $modules,
                ];
            })
            ->filter()
            ->all();
    }

    /**
     * @return array{label: string, description: string, key_base: string, category: string}
     */
    public static function definition(string $type): array
    {
        $definition = self::definitions()[$type] ?? null;

        if ($definition === null) {
            throw new InvalidArgumentException("Unknown area type [{$type}].");
        }

        return $definition;
    }

    public static function displayLabel(string $type, ?string $label = null, ?string $areaKey = null): string
    {
        $candidate = trim((string) $label);

        if ($candidate === '') {
            $candidate = $areaKey
                ? Str::headline(str_replace('-', ' ', $areaKey))
                : self::definition($type)['label'];
        }

        return self::normalizeLabel($type, $candidate);
    }

    /**
     * @param array{key?: string, sort_order?: int, label?: string|null, data?: array<string, mixed>} $config
     */
    public static function createForPage(SitePage|SitePageDraft $page, string $type, array $config = []): SitePageArea|SitePageDraftArea
    {
        $availableTypes = self::availableForTheme($page->site->theme);

        if (! array_key_exists($type, $availableTypes)) {
            throw new InvalidArgumentException("Area type [{$type}] is not available for theme [{$page->site->theme}].");
        }

        $key = $config['key'] ?? self::nextKey($page, $type);

        if ($page->areas()->where('area_key', $key)->exists()) {
            throw new InvalidArgumentException("Area key [{$key}] already exists on page [{$page->id}].");
        }

        $payload = array_merge(
            self::defaultPayload($page, $type),
            $config['data'] ?? [],
        );

        $label = self::displayLabel(
            $type,
            (string) ($config['label'] ?? $payload['editor_label'] ?? self::definition($type)['label']),
        );
        unset($payload['editor_label']);

        $area = $page->areas()->create([
            'area_key' => $key,
            'area_type' => $type,
            'label' => $label !== '' ? $label : null,
            'sort_order' => $config['sort_order'] ?? self::nextSortOrder($page),
            'is_active' => true,
        ]);

        $area->syncData($payload);

        return $area;
    }

    /**
     * @return array<string, array{label: string, description: string, key_base: string, category: string}>
     */
    private static function definitions(): array
    {
        return [
            'hero' => [
                'label' => 'Topsektion',
                'description' => 'Oeverste del af siden med overskrift, intro og knapper.',
                'key_base' => 'hero',
                'category' => 'intro',
            ],
            'content' => [
                'label' => 'Indholdssektion',
                'description' => 'Tekstafsnit med forklaring og eventuelle punktlister.',
                'key_base' => 'content',
                'category' => 'content',
            ],
            'contact' => [
                'label' => 'Kontaktsektion',
                'description' => 'Afsluttende afsnit med kontaktoplysninger og naeste skridt.',
                'key_base' => 'contact',
                'category' => 'practical',
            ],
            'stats' => [
                'label' => 'Nøgletal',
                'description' => 'Fremhæv tal, resultater eller korte highlights i et mere visuelt format.',
                'key_base' => 'stats',
                'category' => 'trust',
            ],
            'quote' => [
                'label' => 'Citat',
                'description' => 'Et stærkt citat, testimonial eller en udtalelse der bygger tillid.',
                'key_base' => 'quote',
                'category' => 'trust',
            ],
            'faq' => [
                'label' => 'FAQ',
                'description' => 'Spørgsmål og svar i et mere struktureret afsnit.',
                'key_base' => 'faq',
                'category' => 'practical',
            ],
        ];
    }

    /**
     * @return array<string, array{label: string}>
     */
    private static function categories(): array
    {
        return [
            'intro' => [
                'label' => 'Intro templates',
            ],
            'content' => [
                'label' => 'Indhold og produkter',
            ],
            'trust' => [
                'label' => 'Tillid',
            ],
            'practical' => [
                'label' => 'Praktisk information',
            ],
        ];
    }

    private static function nextSortOrder(SitePage|SitePageDraft $page): int
    {
        return ((int) $page->areas()->max('sort_order')) + 1;
    }

    private static function nextKey(SitePage|SitePageDraft $page, string $type): string
    {
        $baseKey = self::definition($type)['key_base'];
        $candidate = $baseKey;
        $index = 2;

        while ($page->areas()->where('area_key', $candidate)->exists()) {
            $candidate = "{$baseKey}-{$index}";
            $index++;
        }

        return $candidate;
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultPayload(SitePage|SitePageDraft $page, string $type): array
    {
        $page->loadMissing('site.tenant');

        $tenant = $page->site->tenant;
        $email = $tenant?->display_email;

        return match ($type) {
            'hero' => [
                'eyebrow' => $page->is_home ? 'Ny topsektion' : $page->name,
                'title' => $page->title,
                'copy' => 'Brug topsektionen til at praesentere sidens vigtigste budskab med det samme.',
                'image_url' => '',
                'image_alt' => '',
                'image_focus' => 'center',
                'heading_size' => 'large',
                'text_align' => 'left',
                'button_align' => 'left',
                'secondary_cta_mode' => 'show',
                'primary_cta_label' => 'Kontakt os',
                'primary_cta_href' => '/kontakt',
                'secondary_cta_label' => 'Laes mere',
                'secondary_cta_href' => $page->is_home ? '#content' : '#contact',
            ],
            'content' => [
                'eyebrow' => 'Indhold',
                'title' => 'Nyt tekstafsnit',
                'copy' => 'Brug dette afsnit til at folde sideindholdet ud med tekst, punkter og korte pointer.',
                'text_align' => 'left',
                'items_style' => 'list',
                'section_tone' => 'default',
                'items' => [
                    'Tilfoej dine vigtigste budskaber her.',
                    'Hold teksten kort, konkret og let at skimme.',
                ],
            ],
            'contact' => array_filter([
                'eyebrow' => 'Kontakt',
                'title' => 'Lad os tage en dialog',
                'copy' => 'Giv besoegende en tydelig vej videre med kontaktoplysninger eller en klar invitation til at tage kontakt.',
                'layout_style' => 'split',
                'section_tone' => 'default',
                'show_phone' => '1',
                'email' => $email,
                'phone' => $tenant?->phone,
                'cta_label' => $email ? 'Skriv til os' : 'Kontakt os',
                'cta_href' => $email ? "mailto:{$email}" : '/kontakt',
            ], fn (mixed $value): bool => $value !== null && $value !== ''),
            'stats' => [
                'eyebrow' => 'Nøgletal',
                'title' => 'Tal der skaber tillid',
                'copy' => 'Brug nøgletal til hurtigt at understøtte et budskab med konkrete resultater eller styrker.',
                'display_style' => 'cards',
                'section_tone' => 'default',
                'items' => [
                    '98% | Kundetilfredshed',
                    '24 timer | Typisk svartid',
                    '150+ | Gennemførte forløb',
                ],
            ],
            'quote' => [
                'eyebrow' => 'Udtalelse',
                'quote_text' => 'Brug citatet til at fremhæve en kundeoplevelse, en anbefaling eller et stærkt statement.',
                'quote_author' => 'Navn Efternavn',
                'quote_role' => 'Titel eller virksomhed',
                'text_align' => 'left',
                'section_tone' => 'accent',
            ],
            'faq' => [
                'eyebrow' => 'FAQ',
                'title' => 'Ofte stillede spørgsmål',
                'copy' => 'Saml de spørgsmål kunderne oftest stiller, så siden føles mere komplet og hjælpsom.',
                'layout_style' => 'stacked',
                'section_tone' => 'default',
                'items' => [
                    'Hvordan foregår opstarten? | Vi starter med en kort afklaring og vender derefter tilbage med næste skridt.',
                    'Hvor hurtigt kan vi komme i gang? | Det afhænger af opgaven, men vi giver altid en tydelig plan.',
                    'Kan vi få et fast tilbud? | Ja, når opgaven er afklaret, kan vi sende et konkret tilbud.',
                ],
            ],
            default => [],
        };
    }

    private static function normalizeLabel(string $type, string $label): string
    {
        return match (strtolower(trim($label))) {
            'hero' => 'Topsektion',
            'highlights' => 'Fordele',
            'cta' => $type === 'contact' ? 'Kontaktsektion' : 'Afslutning',
            'kontakt-cta' => 'Kontaktsektion',
            'stats' => 'Nøgletal',
            'quote' => 'Citat',
            default => $label,
        };
    }
}
