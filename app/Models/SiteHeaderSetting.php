<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SiteHeaderSetting extends Model
{
    public const BACKGROUND_AUTO = 'auto';
    public const BACKGROUND_LIGHT = 'light';
    public const BACKGROUND_DARK = 'dark';
    public const BACKGROUND_TRANSPARENT = 'transparent';

    public const SHADOW_AUTO = 'auto';
    public const SHADOW_NONE = 'none';
    public const SHADOW_SOFT = 'soft';
    public const SHADOW_STRONG = 'strong';

    public const STICKY_AUTO = 'auto';
    public const STICKY_STICKY = 'sticky';
    public const STICKY_STATIC = 'static';

    public const TEXT_AUTO = 'auto';
    public const TEXT_DARK = 'dark';
    public const TEXT_LIGHT = 'light';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'brand_name',
        'show_brand_name',
        'tagline',
        'show_tagline',
        'logo_path',
        'logo_alt',
        'cta_label',
        'cta_href',
        'show_cta',
        'background_style',
        'text_color_style',
        'shadow_style',
        'sticky_mode',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_brand_name' => 'boolean',
            'show_tagline' => 'boolean',
            'show_cta' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return array<string, string>
     */
    public static function backgroundOptions(): array
    {
        return [
            self::BACKGROUND_AUTO => 'Tema-standard',
            self::BACKGROUND_LIGHT => 'Lys',
            self::BACKGROUND_DARK => 'Mørk',
            self::BACKGROUND_TRANSPARENT => 'Transparent',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function shadowOptions(): array
    {
        return [
            self::SHADOW_AUTO => 'Tema-standard',
            self::SHADOW_NONE => 'Ingen',
            self::SHADOW_SOFT => 'Blød',
            self::SHADOW_STRONG => 'Tydelig',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function stickyOptions(): array
    {
        return [
            self::STICKY_AUTO => 'Tema-standard',
            self::STICKY_STICKY => 'Fast i toppen',
            self::STICKY_STATIC => 'Scroller med siden',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function textColorOptions(): array
    {
        return [
            self::TEXT_AUTO => 'Tema-standard',
            self::TEXT_DARK => 'Mørk tekst',
            self::TEXT_LIGHT => 'Lys tekst',
        ];
    }

    public static function normalizeBackgroundStyle(mixed $value): string
    {
        $normalized = trim((string) $value);

        return array_key_exists($normalized, self::backgroundOptions())
            ? $normalized
            : self::BACKGROUND_AUTO;
    }

    public static function normalizeShadowStyle(mixed $value): string
    {
        $normalized = trim((string) $value);

        return array_key_exists($normalized, self::shadowOptions())
            ? $normalized
            : self::SHADOW_AUTO;
    }

    public static function normalizeStickyMode(mixed $value): string
    {
        $normalized = trim((string) $value);

        return array_key_exists($normalized, self::stickyOptions())
            ? $normalized
            : self::STICKY_AUTO;
    }

    public static function normalizeTextColorStyle(mixed $value): string
    {
        $normalized = trim((string) $value);

        return array_key_exists($normalized, self::textColorOptions())
            ? $normalized
            : self::TEXT_AUTO;
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk((string) config('filesystems.site_media_disk', 'public'))
            ->url($this->logo_path);
    }
}
