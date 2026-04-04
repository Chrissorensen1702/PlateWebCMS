<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'kind',
        'headline',
        'summary',
        'price_from',
        'build_time',
        'is_active',
        'sort_order',
    ];

    /**
     * The model's attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function featureItems(): HasMany
    {
        return $this->hasMany(PlanFeature::class)->orderBy('sort_order')->orderBy('id');
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('price_from');
    }

    public function getFeaturesAttribute(): array
    {
        $features = $this->relationLoaded('featureItems')
            ? $this->featureItems
            : $this->featureItems()->get();

        return $features
            ->pluck('label')
            ->filter()
            ->values()
            ->all();
    }

    public function getIsCustomAttribute(): bool
    {
        return $this->kind === 'custom';
    }

    /**
     * @param list<string> $features
     */
    public function syncFeatures(array $features): void
    {
        $normalizedFeatures = Collection::make($features)
            ->map(fn (string $feature): string => trim($feature))
            ->filter()
            ->values();

        $this->featureItems()->delete();

        if ($normalizedFeatures->isNotEmpty()) {
            $this->featureItems()->createMany(
                $normalizedFeatures
                    ->map(fn (string $feature, int $index): array => [
                        'label' => $feature,
                        'sort_order' => $index + 1,
                    ])
                    ->all(),
            );
        }

        $this->unsetRelation('featureItems');
    }
}
