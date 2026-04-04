<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageDraftAreaField extends Model
{
    use HasFactory;

    protected $table = 'site_page_draft_area_fields';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_page_draft_area_id',
        'field_key',
        'position',
        'value',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(SitePageDraftArea::class, 'site_page_draft_area_id');
    }
}
