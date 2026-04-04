<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'company_email',
        'billing_email',
        'phone',
        'cvr_number',
        'website_url',
        'slug',
        'status',
        'notes',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function getPrimaryContactAttribute(): ?User
    {
        $users = $this->relationLoaded('users')
            ? $this->users
            : $this->users()->get();

        return $users
            ->sortBy(fn (User $user): int => match ($user->pivot?->role) {
                'owner' => 0,
                'editor' => 1,
                default => 2,
            })
            ->first();
    }

    public function getDisplayEmailAttribute(): ?string
    {
        return $this->company_email ?: $this->primary_contact?->email;
    }
}
