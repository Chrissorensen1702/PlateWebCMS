<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteNewsletterSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'is_enabled',
        'headline',
        'copy',
        'button_label',
        'placement',
        'delivery_mode',
        'consent_text',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
