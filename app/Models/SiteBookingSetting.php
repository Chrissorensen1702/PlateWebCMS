<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteBookingSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'is_enabled',
        'connection_mode',
        'booking_reference',
        'booking_url',
        'dashboard_url',
        'owner_name',
        'owner_email',
        'provisioned_at',
        'cta_label',
        'use_on_website',
        'show_in_header',
        'show_in_contact_sections',
        'open_in_new_tab',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'use_on_website' => 'boolean',
            'show_in_header' => 'boolean',
            'show_in_contact_sections' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'provisioned_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
