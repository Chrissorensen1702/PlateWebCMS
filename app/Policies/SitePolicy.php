<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Site $site): bool
    {
        return $user->isDeveloper() || $user->belongsToTenant($site->tenant);
    }

    public function update(User $user, Site $site): bool
    {
        return $user->canEditAnySite() || $user->canEditTenant($site->tenant);
    }
}
