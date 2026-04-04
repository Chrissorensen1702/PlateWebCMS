<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SiteHeaderSetting extends Model
{
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

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk((string) config('filesystems.site_media_disk', 'public'))
            ->url($this->logo_path);
    }
}
