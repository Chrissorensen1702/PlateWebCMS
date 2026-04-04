<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageAreaField extends Model
{
    use HasFactory;

    protected $table = 'site_page_area_fields';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_page_area_id',
        'field_key',
        'position',
        'value',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(SitePageArea::class, 'site_page_area_id');
    }

    public function section(): BelongsTo
    {
        return $this->area();
    }
}
