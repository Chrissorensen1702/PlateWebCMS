<?php

namespace App\Support\Cms;

use App\Models\Plan;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Str;

class SiteFeatureGate
{
    public const FEATURE_CONTENT = 'cms.content';
    public const FEATURE_GLOBAL_BRANDING = 'site.global_branding';
    public const FEATURE_THEME_TEMPLATES = 'site.theme_templates';
    public const FEATURE_COLOR_PALETTES = 'site.color_palettes';
    public const FEATURE_DOMAIN_DNS = 'site.domain_dns';
    public const FEATURE_SEO = 'site.seo';
    public const FEATURE_NEWSLETTER = 'marketing.newsletter';
    public const FEATURE_BOOKING = 'booking.integration';
    public const FEATURE_CUSTOM_CODE = 'cms.custom_code';

    /**
     * Default old/no-plan sites to the website+booking track so existing CMS
     * flows keep working until every site is connected to an explicit package.
     */
    private const DEFAULT_PACKAGE_KEY = 'scale';

    /**
     * @var array<string, list<string>>
     */
    private const PACKAGE_FEATURES = [
        'launch' => [
            self::FEATURE_CONTENT,
            self::FEATURE_GLOBAL_BRANDING,
            self::FEATURE_THEME_TEMPLATES,
            self::FEATURE_COLOR_PALETTES,
            self::FEATURE_DOMAIN_DNS,
            self::FEATURE_SEO,
            self::FEATURE_NEWSLETTER,
        ],
        'scale' => [
            self::FEATURE_CONTENT,
            self::FEATURE_GLOBAL_BRANDING,
            self::FEATURE_THEME_TEMPLATES,
            self::FEATURE_COLOR_PALETTES,
            self::FEATURE_DOMAIN_DNS,
            self::FEATURE_SEO,
            self::FEATURE_NEWSLETTER,
            self::FEATURE_BOOKING,
        ],
        'signature' => [
            self::FEATURE_CONTENT,
            self::FEATURE_GLOBAL_BRANDING,
            self::FEATURE_THEME_TEMPLATES,
            self::FEATURE_COLOR_PALETTES,
            self::FEATURE_DOMAIN_DNS,
            self::FEATURE_SEO,
            self::FEATURE_NEWSLETTER,
            self::FEATURE_BOOKING,
            self::FEATURE_CUSTOM_CODE,
        ],
        'platebook' => [
            self::FEATURE_BOOKING,
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const GLOBAL_SECTION_FEATURES = [
        'header' => self::FEATURE_GLOBAL_BRANDING,
        'footer' => self::FEATURE_GLOBAL_BRANDING,
        'colors' => self::FEATURE_COLOR_PALETTES,
        'theme' => self::FEATURE_THEME_TEMPLATES,
        'domain' => self::FEATURE_DOMAIN_DNS,
        'booking' => self::FEATURE_BOOKING,
        'newsletter' => self::FEATURE_NEWSLETTER,
        'seo' => self::FEATURE_SEO,
    ];

    public function allows(Site $site, string $feature, ?User $user = null): bool
    {
        if ($user?->isDeveloper()) {
            return true;
        }

        return in_array($feature, $this->featuresForSite($site), true);
    }

    public function ensureAllowed(Site $site, string $feature, ?User $user = null): void
    {
        abort_unless($this->allows($site, $feature, $user), 403);
    }

    public function canManageContent(Site $site, ?User $user = null): bool
    {
        return $this->allows($site, self::FEATURE_CONTENT, $user);
    }

    public function canUseCustomCode(Site $site, ?User $user = null): bool
    {
        return $this->allows($site, self::FEATURE_CUSTOM_CODE, $user);
    }

    public function allowsGlobalSection(Site $site, string $section, ?User $user = null): bool
    {
        $feature = self::GLOBAL_SECTION_FEATURES[$section] ?? null;

        return $feature === null
            ? true
            : $this->allows($site, $feature, $user);
    }

    /**
     * @param  array<string, array<string, mixed>>  $sections
     * @return array<string, array<string, mixed>>
     */
    public function filterGlobalSections(Site $site, array $sections, ?User $user = null): array
    {
        return array_filter(
            $sections,
            fn (array $definition, string $key): bool => $this->allowsGlobalSection($site, $key, $user),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @return list<string>
     */
    public function featuresForSite(Site $site, ?User $user = null): array
    {
        if ($user?->isDeveloper()) {
            return $this->allFeatureKeys();
        }

        return self::PACKAGE_FEATURES[$this->packageKeyForSite($site)] ?? [];
    }

    public function packageKeyForSite(Site $site): string
    {
        return $this->packageKeyForPlan($site->plan);
    }

    public function packageKeyForPlan(?Plan $plan): string
    {
        if ($plan === null) {
            return self::DEFAULT_PACKAGE_KEY;
        }

        $kind = Str::lower((string) $plan->kind);
        $slug = Str::lower((string) $plan->slug);
        $name = Str::lower((string) $plan->name);

        if ($kind === 'custom' || Str::contains($slug, ['signature', 'custom']) || Str::contains($name, ['signature', 'custom'])) {
            return 'signature';
        }

        if (Str::contains($slug, ['chairflow', 'platebook', 'booking']) || Str::contains($name, ['chairflow', 'platebook', 'booking'])) {
            return 'platebook';
        }

        if (Str::contains($slug, ['atelier', 'template-start', 'launch', 'starter']) || Str::contains($name, ['atelier', 'launch', 'starter', 'template start'])) {
            return 'launch';
        }

        if (Str::contains($slug, ['studio', 'template-pro', 'scale', 'pro']) || Str::contains($name, ['studio', 'scale', 'template pro'])) {
            return 'scale';
        }

        if ($kind === 'template') {
            return ((int) $plan->sort_order) <= 1 ? 'launch' : 'scale';
        }

        return self::DEFAULT_PACKAGE_KEY;
    }

    /**
     * @return list<string>
     */
    private function allFeatureKeys(): array
    {
        return collect(self::PACKAGE_FEATURES)
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }
}
