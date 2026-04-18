<?php

namespace App\Support\Sales;

use App\Models\Site;
use App\Models\SiteBookingSetting;
use App\Models\SiteColorSetting;
use App\Models\SiteFooterSetting;
use App\Models\SiteHeaderSetting;
use App\Models\SitePage;
use App\Models\SitePageArea;
use App\Models\SitePageAreaField;
use App\Models\Tenant;
use App\Support\Sites\SiteColorPalettes;
use App\Support\Sites\SiteThemes;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DesignShowcaseCatalog
{
    /**
     * @var array<string, string>
     */
    private const THEME_PALETTES = [
        'base' => 'harbor',
        'editorial' => 'rose',
        'minimal' => 'forest',
        'midnight' => 'midnight',
        'spotlight' => 'sunset',
    ];

    /**
     * @var array<string, array{
     *     site_name: string,
     *     tagline: string,
     *     cta_label: string,
     *     hero_title: string,
     *     hero_copy: string,
     *     content_title: string,
     *     content_copy: string,
     *     contact_title: string,
     *     contact_copy: string
     * }>
     */
    private const THEME_BRANDS = [
        'base' => [
            'site_name' => 'North Studio',
            'tagline' => 'Roligt udgangspunkt for serioese websites',
            'cta_label' => 'Tag kontakt',
            'hero_title' => 'Et roligt og professionelt udgangspunkt til klassiske hjemmesider.',
            'hero_copy' => 'Base-themeet er bygget til at foeles trygt, tydeligt og anvendeligt for virksomheder, der vil staa skarpt online uden for meget visuel uro.',
            'content_title' => 'Tydelig struktur, rolig rytme og plads til indholdet.',
            'content_copy' => 'Brug Base, naar forsiden skal vaere let at laese, let at saelge og nem at bygge videre paa med services, cases eller kontaktsider.',
            'contact_title' => 'Vil du starte med et klassisk og alsidigt udtryk?',
            'contact_copy' => 'Vi kan tage udgangspunkt i Base og derefter tilpasse farver, indhold og sektioner, saa det matcher virksomheden.',
        ],
        'editorial' => [
            'site_name' => 'Maison Glow',
            'tagline' => 'Soft editorial preview',
            'cta_label' => 'Book en samtale',
            'hero_title' => 'Et blodt editorial-look med varme, luft og mere sanselighed.',
            'hero_copy' => 'Editorial-themeet fungerer godt til brands, hvor stemning, premium-fornemmelse og en mere redaktionel oplevelse skal fylde mere pa forsiden.',
            'content_title' => 'Skabt til brands hvor oplevelse og atmosfaere skal maerkes hurtigt.',
            'content_copy' => 'Layoutet giver plads til billeder, rolige budskaber og sektioner, der foeles mere eksklusive uden at miste struktur.',
            'contact_title' => 'Vil du have et mere luksurioest og varmt udtryk?',
            'contact_copy' => 'Editorial er et staerkt udgangspunkt til beauty, wellness og boutique-betonede forsider med booking eller tydelig kontaktvej.',
        ],
        'minimal' => [
            'site_name' => 'Atelier North',
            'tagline' => 'Calm digital presence',
            'cta_label' => 'Se muligheder',
            'hero_title' => 'Et enkelt nordisk theme med masser af luft og fokus.',
            'hero_copy' => 'Minimal-themeet er til forsider, hvor designet skal foeles afklaret og elegant, og hvor whitespace er en aktiv del af oplevelsen.',
            'content_title' => 'Perfekt til premium-services med behov for ro og præcision.',
            'content_copy' => 'Det er et godt valg til studier, arkitekter og andre brands, der vil kommunikere kvalitet gennem enkelhed frem for mange effekter.',
            'contact_title' => 'Vil du have et mere rent og nordisk udtryk?',
            'contact_copy' => 'Minimal giver en skarp forside med fa, velvalgte elementer og tydelig prioritering i indholdet.',
        ],
        'midnight' => [
            'site_name' => 'Signal Lab',
            'tagline' => 'Performance-led website preview',
            'cta_label' => 'Faa en demo',
            'hero_title' => 'Et moerkt og mere markant udtryk med kontrast og kant.',
            'hero_copy' => 'Midnight-themeet loefter forsiden med en mere selvsikker energi, som passer godt til bureauer, tech og brands med et staerkt digitalt udtryk.',
            'content_title' => 'Byg en forside der foeles skarp, moderne og resultatorienteret.',
            'content_copy' => 'Her er der plads til hoej kontrast, klare CTAer og sektioner, der arbejder mere offensivt med salgsretning og performance.',
            'contact_title' => 'Vil du vise mere kant og kontrast pa forsiden?',
            'contact_copy' => 'Midnight er oplagt, hvis hjemmesiden skal foeles mere modig og digitalt fremadrettet uden at miste struktur.',
        ],
        'spotlight' => [
            'site_name' => 'Campaign Sprint',
            'tagline' => 'Campaign & conversion mode',
            'cta_label' => 'Kom i gang',
            'hero_title' => 'Et energisk theme til kampagner, leads og skarpe budskaber.',
            'hero_copy' => 'Spotlight-themeet er bygget til forsider, hvor der gerne maa vaere mere farve, mere tempo og en tydelig handling fra starten.',
            'content_title' => 'Skabt til konvertering, events og mere iojnefaldende lanceringer.',
            'content_copy' => 'Brug Spotlight, naar forsiden skal arbejde aktivt med opmaerksomhed, budskaber og en CTA, der staar helt tydeligt i layoutet.',
            'contact_title' => 'Vil du have et mere salgsdrevet og synligt theme?',
            'contact_copy' => 'Spotlight passer godt til kampagner, events og andre loesninger, hvor tempo og tydelig handling er vigtigere end klassisk ro.',
        ],
    ];

    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     vibe: string,
     *     description: string,
     *     recommended_for: list<string>,
     *     brand_name: string,
     *     open_url: string,
     *     embed_url: string
     * }>
     */
    public function showcaseThemes(): array
    {
        return collect(SiteThemes::all())
            ->map(function (array $theme, string $key): array {
                $brand = self::THEME_BRANDS[$key] ?? self::THEME_BRANDS['base'];

                return [
                    'key' => $key,
                    'label' => $theme['label'],
                    'vibe' => $theme['vibe'],
                    'description' => $theme['description'],
                    'recommended_for' => $theme['recommended_for'],
                    'brand_name' => $brand['site_name'],
                    'open_url' => route('sales.designs.preview', ['theme' => $key]),
                    'embed_url' => route('sales.designs.preview', ['theme' => $key, 'embed' => 1]),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     vibe: string,
     *     description: string,
     *     palette_label: string,
     *     palette: list<string>
     * }>
     */
    public function themes(): array
    {
        return collect(SiteThemes::all())
            ->map(function (array $theme, string $key): array {
                $palette = SiteColorPalettes::definition(self::THEME_PALETTES[$key] ?? SiteColorPalettes::defaultKey());

                return [
                    'key' => $key,
                    'label' => $theme['label'],
                    'vibe' => $theme['vibe'],
                    'description' => $theme['description'],
                    'palette_label' => $palette['label'],
                    'palette' => $this->paletteSwatches($key),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     theme: string,
     *     site: Site,
     *     page: SitePage,
     *     navigation: Collection<int, SitePage>
     * }
     */
    public function previewTheme(string $theme, bool $embedded = false): array
    {
        $resolvedTheme = trim($theme);

        if (! in_array($resolvedTheme, SiteThemes::keys(), true)) {
            throw new InvalidArgumentException("Unknown design preview theme [{$resolvedTheme}].");
        }

        $previewUrl = route('sales.designs.preview', array_filter([
            'theme' => $resolvedTheme,
            'embed' => $embedded ? 1 : null,
        ], fn (mixed $value): bool => $value !== null));

        $site = $this->buildSite($resolvedTheme, $previewUrl);
        $page = $this->buildHomePage($resolvedTheme, $site, $previewUrl);
        $navigation = collect([$page]);

        return [
            'theme' => $resolvedTheme,
            'site' => $site,
            'page' => $page,
            'navigation' => $navigation,
        ];
    }

    /**
     * @return list<string>
     */
    private function paletteSwatches(string $theme): array
    {
        $colors = SiteColorPalettes::definition(self::THEME_PALETTES[$theme] ?? SiteColorPalettes::defaultKey())['colors'];

        return [
            $colors['primary'],
            $colors['accent'],
            $colors['cream'],
            $colors['ink'],
        ];
    }

    private function buildSite(string $theme, string $previewUrl): Site
    {
        $brand = self::THEME_BRANDS[$theme] ?? self::THEME_BRANDS['base'];

        $site = new Site([
            'id' => 10_000 + crc32($theme),
            'tenant_id' => 20_000 + crc32("tenant:{$theme}"),
            'name' => $brand['site_name'],
            'slug' => "preview-{$theme}",
            'theme' => $theme,
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->preview_home_url = $previewUrl;

        $site->setRelation('tenant', new Tenant([
            'id' => 30_000 + crc32("tenant:{$theme}"),
            'name' => $brand['site_name'],
            'company_email' => 'hello@plateweb.dk',
            'phone' => '+45 12 34 56 78',
            'cvr_number' => '12345678',
            'slug' => "preview-tenant-{$theme}",
            'status' => 'active',
        ]));

        $site->setRelation('headerSettings', new SiteHeaderSetting([
            'site_id' => $site->id,
            'brand_name' => $brand['site_name'],
            'show_brand_name' => true,
            'tagline' => $brand['tagline'],
            'show_tagline' => true,
            'cta_label' => $brand['cta_label'],
            'cta_href' => route('contact'),
            'show_cta' => true,
        ]));

        $site->setRelation('footerSettings', new SiteFooterSetting([
            'site_id' => $site->id,
            'contact_email' => 'hello@plateweb.dk',
            'show_contact_email' => true,
            'contact_phone' => '+45 12 34 56 78',
            'show_contact_phone' => true,
            'contact_address' => "PlateWeb Studio\nKobenhavn K",
            'show_contact_address' => true,
            'contact_cvr' => '12345678',
            'show_contact_cvr' => true,
            'information_links' => [
                ['label' => 'Priser', 'href' => route('templates')],
                ['label' => 'Kontakt', 'href' => route('contact')],
            ],
        ]));

        $site->setRelation('colorSettings', new SiteColorSetting([
            'site_id' => $site->id,
            'palette_key' => self::THEME_PALETTES[$theme] ?? SiteColorPalettes::defaultKey(),
        ]));

        $site->setRelation('bookingSettings', new SiteBookingSetting([
            'site_id' => $site->id,
            'is_enabled' => false,
            'use_on_website' => false,
            'show_in_header' => false,
            'show_in_contact_sections' => false,
            'open_in_new_tab' => false,
        ]));

        return $site;
    }

    private function buildHomePage(string $theme, Site $site, string $previewUrl): SitePage
    {
        $themeDefinition = SiteThemes::definition($theme);
        $brand = self::THEME_BRANDS[$theme] ?? self::THEME_BRANDS['base'];
        $vibes = collect(explode('·', $themeDefinition['vibe']))
            ->map(fn (string $part): string => trim($part))
            ->filter()
            ->values();

        $page = new SitePage([
            'id' => 40_000 + crc32("page:{$theme}"),
            'site_id' => $site->id,
            'name' => 'Forside',
            'slug' => 'home',
            'title' => $brand['site_name'].' forside',
            'template_key' => 'showcase-home',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $page->preview_url = $previewUrl;
        $page->setRelation('site', $site);

        $areas = collect([
            [
                'type' => 'hero',
                'key' => 'showcase-hero',
                'label' => 'Forsideintro',
                'data' => [
                    'eyebrow' => $themeDefinition['label'].' theme',
                    'title' => $brand['hero_title'],
                    'copy' => $brand['hero_copy'],
                    'primary_cta_label' => $brand['cta_label'],
                    'primary_cta_href' => route('contact'),
                    'secondary_cta_label' => 'Se priser',
                    'secondary_cta_href' => route('templates'),
                ],
            ],
            [
                'type' => 'content',
                'key' => 'showcase-content',
                'label' => 'Retning',
                'data' => [
                    'eyebrow' => 'Retning',
                    'title' => $brand['content_title'],
                    'copy' => $brand['content_copy'],
                    'items_style' => 'cards',
                    'section_tone' => 'default',
                    'items' => $themeDefinition['recommended_for'],
                ],
            ],
            [
                'type' => 'stats',
                'key' => 'showcase-vibe',
                'label' => 'Vibe',
                'data' => [
                    'eyebrow' => 'Vibe',
                    'title' => 'Saadan foeles themeet paa forsiden',
                    'copy' => 'Tre ord, der hurtigt forklarer retningen i designet.',
                    'display_style' => 'cards',
                    'section_tone' => 'accent',
                    'items' => $vibes->map(fn (string $vibe): string => "{$vibe} | En tydelig del af retningen")->all(),
                ],
            ],
            [
                'type' => 'contact',
                'key' => 'showcase-contact',
                'label' => 'Afslutning',
                'data' => [
                    'eyebrow' => 'Naeste skridt',
                    'title' => $brand['contact_title'],
                    'copy' => $brand['contact_copy'],
                    'cta_label' => 'Kontakt os',
                    'cta_href' => route('contact'),
                    'map_embed_url' => 'https://www.google.com/maps?q=Copenhagen&output=embed',
                    'section_tone' => 'accent',
                ],
            ],
        ])->map(function (array $areaTemplate, int $index) use ($page, $previewUrl): SitePageArea {
            $area = new SitePageArea([
                'id' => 50_000 + crc32("area:{$page->template_key}:{$areaTemplate['key']}"),
                'site_page_id' => $page->id,
                'area_key' => $areaTemplate['key'],
                'area_type' => $areaTemplate['type'],
                'label' => $areaTemplate['label'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            $area->setRelation('fields', $this->buildFields(
                $this->normalizeAreaData($areaTemplate['data'], $previewUrl)
            ));

            return $area;
        })->values();

        $page->setRelation('areas', $areas);
        $page->setRelation('sections', $areas);

        return $page;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeAreaData(array $data, string $previewUrl): array
    {
        foreach (['primary_cta_href', 'secondary_cta_href', 'cta_href'] as $hrefKey) {
            if (! array_key_exists($hrefKey, $data)) {
                continue;
            }

            $href = trim((string) $data[$hrefKey]);

            if ($href === '' || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            if (str_starts_with($href, '#') || str_starts_with($href, '/')) {
                $data[$hrefKey] = $previewUrl;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, SitePageAreaField>
     */
    private function buildFields(array $data): Collection
    {
        return collect($data)
            ->flatMap(function (mixed $value, string $fieldKey): Collection {
                $values = is_array($value) ? array_values($value) : [$value];

                return collect($values)
                    ->map(fn (mixed $item): string => trim((string) $item))
                    ->filter(fn (string $item): bool => $item !== '')
                    ->values()
                    ->map(fn (string $item, int $index): SitePageAreaField => new SitePageAreaField([
                        'field_key' => $fieldKey,
                        'position' => $index + 1,
                        'value' => $item,
                    ]));
            })
            ->values();
    }
}
