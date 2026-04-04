<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteColorSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'palette_key',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
