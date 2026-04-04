<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasAreaData
{
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('area_key');
    }

    /**
     * @return array<string, string|list<string>>
     */
    public function getDataAttribute(): array
    {
        $fields = $this->relationLoaded('fields')
            ? $this->fields
            : $this->fields()->get();

        return $fields
            ->groupBy('field_key')
            ->map(function (Collection $group): string|array {
                $values = $group
                    ->sortBy('position')
                    ->pluck('value')
                    ->values()
                    ->all();

                return count($values) === 1 ? $values[0] : $values;
            })
            ->all();
    }

    public function getKeyAttribute(): string
    {
        return (string) $this->area_key;
    }

    public function setKeyAttribute(string $value): void
    {
        $this->attributes['area_key'] = $value;
    }

    public function getTypeAttribute(): string
    {
        return (string) $this->area_type;
    }

    public function setTypeAttribute(string $value): void
    {
        $this->attributes['area_type'] = $value;
    }

    public function field(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function syncData(array $payload): void
    {
        $normalizedPayload = Collection::make($payload)
            ->map(function (mixed $value): string|array {
                if (is_array($value)) {
                    return Collection::make($value)
                        ->map(fn (mixed $item): string => trim((string) $item))
                        ->filter(fn (string $item): bool => $item !== '')
                        ->values()
                        ->all();
                }

                return trim((string) $value);
            })
            ->filter(fn (mixed $value): bool => is_array($value) ? $value !== [] : $value !== '')
            ->all();

        $this->fields()->delete();

        foreach ($normalizedPayload as $fieldKey => $value) {
            $values = is_array($value) ? array_values($value) : [$value];

            foreach ($values as $index => $item) {
                $this->fields()->create([
                    'field_key' => $fieldKey,
                    'position' => $index + 1,
                    'value' => $item,
                ]);
            }
        }

        $this->unsetRelation('fields');
    }
}
