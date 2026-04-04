<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'plan_id',
        'tenant_id',
        'name',
        'slug',
        'theme',
        'status',
        'is_online',
        'notes',
        'launched_at',
        'draft_initialized_at',
        'last_published_at',
    ];

    /**
     * The model's attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'launched_at' => 'datetime',
            'draft_initialized_at' => 'datetime',
            'last_published_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(SitePage::class);
    }

    public function draftPages(): HasMany
    {
        return $this->hasMany(SitePageDraft::class)->orderByDesc('is_home')->orderBy('sort_order')->orderBy('name');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(SiteDomain::class)->orderByDesc('is_primary')->orderBy('domain');
    }

    public function headerSettings(): HasOne
    {
        return $this->hasOne(SiteHeaderSetting::class);
    }

    public function colorSettings(): HasOne
    {
        return $this->hasOne(SiteColorSetting::class);
    }

    public function footerSettings(): HasOne
    {
        return $this->hasOne(SiteFooterSetting::class);
    }

    public function newsletterSettings(): HasOne
    {
        return $this->hasOne(SiteNewsletterSetting::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if (! $user->isDeveloper()) {
            $query->whereHas('tenant.users', fn (Builder $tenantUsers): Builder => $tenantUsers->whereKey($user->id));
        }
    }

    public function getPrimaryDomainAttribute(): ?string
    {
        $domains = $this->relationLoaded('domains')
            ? $this->domains
            : $this->domains()->get();

        $primaryDomain = $domains->firstWhere('is_primary', true);

        return $primaryDomain?->domain ?? $domains->first()?->domain;
    }

    public function syncPrimaryDomain(?string $domain): void
    {
        $normalizedDomain = trim((string) $domain);

        $this->domains()->delete();

        if ($normalizedDomain === '') {
            $this->unsetRelation('domains');

            return;
        }

        $this->domains()->create([
            'domain' => $normalizedDomain,
            'is_primary' => true,
        ]);

        $this->unsetRelation('domains');
    }
}
