<?php

namespace App\Support\Sites;

class SiteThemes
{
    /**
     * @return array<string, array{label: string, description: string, vibe: string, recommended_for: list<string>}>
     */
    public static function all(): array
    {
        return [
            'base' => [
                'label' => 'Base',
                'description' => 'Rent og alsidigt standardtheme med klassisk hjemmeside-udtryk.',
                'vibe' => 'Roligt · Alsidigt · Tidløst',
                'recommended_for' => ['Virksomhedssider', 'Lokale services', 'Alsidige websites'],
            ],
            'editorial' => [
                'label' => 'Editorial',
                'description' => 'Blødt og eksklusivt lifestyle-theme med mere redaktionel stemning.',
                'vibe' => 'Luksuriøst · Varmt · Sanseligt',
                'recommended_for' => ['Beauty', 'Wellness', 'Boutique brands'],
            ],
            'minimal' => [
                'label' => 'Minimal',
                'description' => 'Luftigt og roligt theme med masser af whitespace og et mere nordisk udtryk.',
                'vibe' => 'Enkelt · Nordisk · Elegant',
                'recommended_for' => ['Designstudier', 'Arkitekter', 'High-end services'],
            ],
            'midnight' => [
                'label' => 'Midnight',
                'description' => 'Mørkt og markant theme med høj kontrast og mere bureau- eller tech-energi.',
                'vibe' => 'Mørkt · Modigt · Kontrastfuldt',
                'recommended_for' => ['Bureauer', 'Tech', 'Performance-sider'],
            ],
            'spotlight' => [
                'label' => 'Spotlight',
                'description' => 'Farverigt og energisk theme til kampagner, events eller stærke konverteringssider.',
                'vibe' => 'Livligt · Salgsdrevet · Iøjnefaldende',
                'recommended_for' => ['Kampagner', 'Events', 'Leadgenerering'],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array{label: string, description: string, vibe: string, recommended_for: list<string>}
     */
    public static function definition(string $theme): array
    {
        return self::all()[$theme] ?? self::all()['base'];
    }
}
