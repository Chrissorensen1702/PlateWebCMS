<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteFooterSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'navigation_links',
        'information_links',
        'social_links',
        'contact_email',
        'show_contact_email',
        'contact_phone',
        'show_contact_phone',
        'contact_address',
        'show_contact_address',
        'contact_cvr',
        'show_contact_cvr',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'navigation_links' => 'array',
            'information_links' => 'array',
            'social_links' => 'array',
            'show_contact_email' => 'boolean',
            'show_contact_phone' => 'boolean',
            'show_contact_address' => 'boolean',
            'show_contact_cvr' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
