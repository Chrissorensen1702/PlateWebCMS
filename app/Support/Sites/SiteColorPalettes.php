<?php

namespace App\Support\Sites;

class SiteColorPalettes
{
    /**
     * @return array<string, array{
     *     label: string,
     *     description: string,
     *     vibe: string,
     *     recommended_for: list<string>,
     *     colors: array{
     *         primary: string,
     *         primary_hover: string,
     *         accent: string,
     *         accent_deep: string,
     *         cream: string,
     *         stone: string,
     *         surface_alt: string,
     *         ink: string,
     *         muted: string,
     *         border: string,
     *         header_ink: string,
     *         header_accent: string,
     *         header_surface: string
     *     }
     * }>
     */
    public static function all(): array
    {
        return [
            'harbor' => [
                'label' => 'Harbor',
                'description' => 'Klassisk blå/guld-palette med roligt, professionelt udtryk.',
                'vibe' => 'Klassisk · Troværdig · Alsidig',
                'recommended_for' => ['Rådgivning', 'B2B-services', 'Corporate websites'],
                'colors' => [
                    'primary' => '#5c80bc',
                    'primary_hover' => '#4b6fa7',
                    'accent' => '#e8c547',
                    'accent_deep' => '#d8b53e',
                    'cream' => '#f3f5f0',
                    'stone' => '#e7eae2',
                    'surface_alt' => '#e7eae2',
                    'ink' => '#30323d',
                    'muted' => '#4d5061',
                    'border' => '#cdd1c4',
                    'header_ink' => '#1f2f45',
                    'header_accent' => '#36506f',
                    'header_surface' => '#dbe3ee',
                ],
            ],
            'forest' => [
                'label' => 'Forest',
                'description' => 'Dyb grøn palette med varm accent og mere jordnær tillid.',
                'vibe' => 'Jordnær · Eksklusiv · Rolig',
                'recommended_for' => ['Klinikker', 'Wellness', 'Lokale servicebrands'],
                'colors' => [
                    'primary' => '#2f6f5a',
                    'primary_hover' => '#275c4b',
                    'accent' => '#d9a441',
                    'accent_deep' => '#c08d2f',
                    'cream' => '#f5f3ed',
                    'stone' => '#e5e1d5',
                    'surface_alt' => '#ece7dc',
                    'ink' => '#23332d',
                    'muted' => '#4c5f56',
                    'border' => '#c9c6bb',
                    'header_ink' => '#17332b',
                    'header_accent' => '#2f6f5a',
                    'header_surface' => '#dde8e2',
                ],
            ],
            'terracotta' => [
                'label' => 'Terracotta',
                'description' => 'Varm palette med støvede rødlige toner og blød kontrast.',
                'vibe' => 'Varm · Menneskelig · Sanselig',
                'recommended_for' => ['Salon', 'Restaurant', 'Personlige brands'],
                'colors' => [
                    'primary' => '#bf6f5a',
                    'primary_hover' => '#a85e4a',
                    'accent' => '#edc16d',
                    'accent_deep' => '#daa955',
                    'cream' => '#faf2eb',
                    'stone' => '#eedfd3',
                    'surface_alt' => '#f3e5da',
                    'ink' => '#3f2f2f',
                    'muted' => '#6b5650',
                    'border' => '#d7c2b7',
                    'header_ink' => '#4f342d',
                    'header_accent' => '#91523f',
                    'header_surface' => '#f3dfd6',
                ],
            ],
            'midnight' => [
                'label' => 'Midnight',
                'description' => 'Mørkere palette med kølig kontrast og mere moderne kant.',
                'vibe' => 'Skarp · Moderne · Kontrastfuld',
                'recommended_for' => ['Bureauer', 'Tech', 'Premium services'],
                'colors' => [
                    'primary' => '#4a68a8',
                    'primary_hover' => '#3d568a',
                    'accent' => '#79d1d6',
                    'accent_deep' => '#53b8be',
                    'cream' => '#eef1f6',
                    'stone' => '#d9e0ea',
                    'surface_alt' => '#d7dfeb',
                    'ink' => '#1c2333',
                    'muted' => '#44506a',
                    'border' => '#bcc8d8',
                    'header_ink' => '#111a29',
                    'header_accent' => '#324764',
                    'header_surface' => '#d8e0ef',
                ],
            ],
            'rose' => [
                'label' => 'Rose',
                'description' => 'Lys, feminin palette med blush- og rosa-toner.',
                'vibe' => 'Blød · Elegant · Lifestyle',
                'recommended_for' => ['Beauty', 'Boutique', 'Livsstilsbrands'],
                'colors' => [
                    'primary' => '#b9718d',
                    'primary_hover' => '#a15e79',
                    'accent' => '#efc3cf',
                    'accent_deep' => '#e5aab9',
                    'cream' => '#fdf6f8',
                    'stone' => '#f3e3e8',
                    'surface_alt' => '#f6e8ec',
                    'ink' => '#3e2f38',
                    'muted' => '#6b5964',
                    'border' => '#dcc7cf',
                    'header_ink' => '#4b3340',
                    'header_accent' => '#9c6278',
                    'header_surface' => '#f5dfe6',
                ],
            ],
            'sunset' => [
                'label' => 'Sunset',
                'description' => 'Mere energisk palette med varme koral- og orange-toner.',
                'vibe' => 'Livlig · Salgsdrevet · Lys',
                'recommended_for' => ['Konverteringssider', 'Events', 'Kreative kampagner'],
                'colors' => [
                    'primary' => '#f07b5a',
                    'primary_hover' => '#db6646',
                    'accent' => '#f3c95f',
                    'accent_deep' => '#dfb44a',
                    'cream' => '#fff6ef',
                    'stone' => '#f3e3d3',
                    'surface_alt' => '#f8e7d7',
                    'ink' => '#3a2d28',
                    'muted' => '#68534a',
                    'border' => '#ddcab9',
                    'header_ink' => '#4f3228',
                    'header_accent' => '#b55739',
                    'header_surface' => '#fde6da',
                ],
            ],
        ];
    }

    public static function defaultKey(): string
    {
        return 'harbor';
    }

    /**
     * @return array{label: string, description: string, vibe: string, recommended_for: list<string>, colors: array<string, string>}
     */
    public static function definition(?string $key): array
    {
        return self::all()[$key ?: self::defaultKey()] ?? self::all()[self::defaultKey()];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array<string, string>
     */
    public static function cssVariables(?string $key): array
    {
        $colors = self::definition($key)['colors'];

        return [
            '--color-primary' => $colors['primary'],
            '--color-primary-hover' => $colors['primary_hover'],
            '--color-primary-rgb' => self::hexToRgb($colors['primary']),
            '--color-accent' => $colors['accent'],
            '--color-accent-deep' => $colors['accent_deep'],
            '--color-accent-rgb' => self::hexToRgb($colors['accent']),
            '--color-cream' => $colors['cream'],
            '--color-stone' => $colors['stone'],
            '--color-surface-alt' => $colors['surface_alt'],
            '--color-surface-alt-rgb' => self::hexToRgb($colors['surface_alt']),
            '--color-ink' => $colors['ink'],
            '--color-muted' => $colors['muted'],
            '--color-forest' => $colors['primary'],
            '--color-text-rgb' => self::hexToRgb($colors['ink']),
            '--color-border' => $colors['border'],
            '--color-border-rgb' => self::hexToRgb($colors['border']),
            '--color-header-ink-rgb' => self::hexToRgb($colors['header_ink']),
            '--color-header-accent-rgb' => self::hexToRgb($colors['header_accent']),
            '--color-header-surface-rgb' => self::hexToRgb($colors['header_surface']),
        ];
    }

    /**
     * @return list<string>
     */
    public static function usageAreas(): array
    {
        return [
            'Header og navigation',
            'Primære knapper',
            'Hero og mørke flader',
            'Fremhævede containere',
            'Links og accenter',
        ];
    }

    /**
     * @return array{label: string, copy: string, ratio: string}
     */
    public static function readabilitySummary(?string $key): array
    {
        $colors = self::definition($key)['colors'];
        $ratio = self::contrastRatio($colors['ink'], $colors['cream']);

        if ($ratio >= 7) {
            return [
                'label' => 'Høj kontrast',
                'copy' => 'Tekst og lyse flader holder sig meget læsbare på tværs af themeet.',
                'ratio' => number_format($ratio, 1, ',', '.').':1',
            ];
        }

        if ($ratio >= 4.5) {
            return [
                'label' => 'God kontrast',
                'copy' => 'Paletten giver et roligt udtryk, men bevarer stadig tydelig læsbarhed.',
                'ratio' => number_format($ratio, 1, ',', '.').':1',
            ];
        }

        return [
            'label' => 'Blød kontrast',
            'copy' => 'Udtrykket er mere blødt, så temaet bør bruge større tekst og tydelige knapper.',
            'ratio' => number_format($ratio, 1, ',', '.').':1',
        ];
    }

    private static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex) ?? $hex;
        }

        return sprintf(
            '%d %d %d',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }

    private static function contrastRatio(string $hexA, string $hexB): float
    {
        $luminanceA = self::relativeLuminance($hexA);
        $luminanceB = self::relativeLuminance($hexB);

        $lightest = max($luminanceA, $luminanceB);
        $darkest = min($luminanceA, $luminanceB);

        return ($lightest + 0.05) / ($darkest + 0.05);
    }

    private static function relativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex) ?? $hex;
        }

        $channels = [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];

        $channels = array_map(static function (float $channel): float {
            return $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;
        }, $channels);

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }
}
