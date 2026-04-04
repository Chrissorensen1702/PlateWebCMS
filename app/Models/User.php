<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const DEVELOPER_ACCESS_FULL = 'full_access';
    public const DEVELOPER_ACCESS_CUSTOMER_MANAGER = 'customer_manager';
    public const DEVELOPER_ACCESS_READ_ONLY = 'read_only';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'developer_access',
        'employment_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    /**
     * @return array<string, string>
     */
    public static function developerAccessOptions(): array
    {
        return [
            self::DEVELOPER_ACCESS_FULL => 'Fuld adgang',
            self::DEVELOPER_ACCESS_CUSTOMER_MANAGER => 'Opret og administrerer kunder',
            self::DEVELOPER_ACCESS_READ_ONLY => 'Laeseadgang',
        ];
    }

    public function developerAccessLabel(): string
    {
        return static::developerAccessOptions()[$this->developer_access] ?? $this->developer_access;
    }

    public function displayNameWithEmploymentRole(): string
    {
        if (! $this->isDeveloper() || ! filled($this->employment_role)) {
            return $this->name;
        }

        return "{$this->name} - {$this->employment_role}";
    }

    public function hasFullDeveloperAccess(): bool
    {
        return $this->isDeveloper() && $this->developer_access === self::DEVELOPER_ACCESS_FULL;
    }

    public function canManageCustomers(): bool
    {
        if (! $this->isDeveloper()) {
            return false;
        }

        return in_array($this->developer_access, [
            self::DEVELOPER_ACCESS_FULL,
            self::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
        ], true);
    }

    public function canManageDeveloperAccounts(): bool
    {
        return $this->hasFullDeveloperAccess();
    }

    public function canManagePlans(): bool
    {
        return $this->hasFullDeveloperAccess();
    }

    public function canEditAnySite(): bool
    {
        return $this->canManageCustomers();
    }

    public function belongsToTenant(?Tenant $tenant): bool
    {
        if ($tenant === null) {
            return false;
        }

        return $this->tenants()
            ->whereKey($tenant->id)
            ->exists();
    }

    public function canEditTenant(?Tenant $tenant): bool
    {
        if ($tenant === null) {
            return false;
        }

        return $this->tenants()
            ->whereKey($tenant->id)
            ->wherePivotIn('role', ['owner', 'editor'])
            ->exists();
    }

    public function ownsTenant(?Tenant $tenant): bool
    {
        if ($tenant === null) {
            return false;
        }

        return $this->tenants()
            ->whereKey($tenant->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function hasOwnedTenants(): bool
    {
        return $this->tenants()
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function canManageTenantAccess(?Tenant $tenant = null): bool
    {
        if ($this->isDeveloper()) {
            return $this->canManageCustomers();
        }

        if ($tenant === null) {
            return $this->hasOwnedTenants();
        }

        return $this->ownsTenant($tenant);
    }
}
