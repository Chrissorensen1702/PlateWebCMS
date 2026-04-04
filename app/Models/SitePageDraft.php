<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePageDraft extends Model
{
    use HasFactory;

    protected $table = 'site_page_drafts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_id',
        'source_page_id',
        'name',
        'slug',
        'title',
        'template_key',
        'layout_mode',
        'custom_html',
        'custom_css',
        'meta_description',
        'is_home',
        'is_published',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_home' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function sourcePage(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'source_page_id');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(SitePageDraftArea::class, 'site_page_draft_id')->with('fields')->ordered();
    }

    public function sections(): HasMany
    {
        return $this->areas();
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('is_home')->orderBy('sort_order')->orderBy('name');
    }

    public function scopeHome(Builder $query): void
    {
        $query->where('is_home', true);
    }
}
