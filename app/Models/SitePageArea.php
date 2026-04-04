<?php

namespace App\Models;

use App\Models\Concerns\HasAreaData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePageArea extends Model
{
    use HasAreaData;
    use HasFactory;

    protected $table = 'site_page_areas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_page_id',
        'area_key',
        'area_type',
        'label',
        'sort_order',
        'is_active',
        // Backwards-compatible aliases.
        'key',
        'type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(SitePageAreaField::class, 'site_page_area_id')
            ->orderBy('field_key')
            ->orderBy('position');
    }
}
