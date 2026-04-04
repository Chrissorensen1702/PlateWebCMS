<?php

namespace App\Models;

use App\Models\Concerns\HasAreaData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePageDraftArea extends Model
{
    use HasAreaData;
    use HasFactory;

    protected $table = 'site_page_draft_areas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_page_draft_id',
        'source_area_id',
        'area_key',
        'area_type',
        'label',
        'sort_order',
        'is_active',
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
        return $this->belongsTo(SitePageDraft::class, 'site_page_draft_id');
    }

    public function sourceArea(): BelongsTo
    {
        return $this->belongsTo(SitePageArea::class, 'source_area_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(SitePageDraftAreaField::class, 'site_page_draft_area_id')
            ->orderBy('field_key')
            ->orderBy('position');
    }
}
