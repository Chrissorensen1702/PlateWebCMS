<?php

namespace App\Support\Sites;

use App\Models\CustomerSolution;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SelfServiceWebsiteProvisioner
{
    /**
     * @var list<string>
     */
    private const WEBSITE_PACKAGES = ['launch', 'scale', 'signature'];

    public function provisionForPricingSolution(User $user, CustomerSolution $solution): ?Site
    {
        if ($user->isDeveloper() || ! in_array((string) $solution->package_key, self::WEBSITE_PACKAGES, true)) {
            return null;
        }

        return DB::transaction(function () use ($user, $solution): Site {
            $tenant = $this->resolveTenant($user);
            $site = $tenant->sites()->orderBy('id')->first();

            if (! $site) {
                $site = $tenant->sites()->create([
                    'plan_id' => $solution->plan_id,
                    'name' => $tenant->name,
                    'slug' => $this->uniqueSiteSlug($tenant->name),
                    'theme' => 'base',
                    'status' => 'draft',
                    'is_online' => false,
                ]);

                $this->seedDefaultPages($site);
                SiteDraftManager::refreshDraftsFromLive($site);

                return $site->fresh(['tenant', 'plan']);
            }

            if ($site->plan_id !== $solution->plan_id) {
                $site->forceFill([
                    'plan_id' => $solution->plan_id,
                ])->save();
            }

            return $site->fresh(['tenant', 'plan']);
        });
    }

    private function resolveTenant(User $user): Tenant
    {
        $tenant = $user->tenants()
            ->wherePivot('role', 'owner')
            ->orderBy('tenants.id')
            ->first();

        if ($tenant instanceof Tenant) {
            $tenantUpdates = [];

            if (! filled($tenant->company_email) && filled($user->email)) {
                $tenantUpdates['company_email'] = $user->email;
            }

            if (! filled($tenant->phone) && filled($user->phone)) {
                $tenantUpdates['phone'] = $user->phone;
            }

            if (! filled($tenant->cvr_number) && filled($user->cvr_number)) {
                $tenantUpdates['cvr_number'] = $user->cvr_number;
            }

            if (! filled($tenant->notes) && filled($user->registration_note)) {
                $tenantUpdates['notes'] = $this->buildTenantNotes($user);
            }

            if ($tenantUpdates !== []) {
                $tenant->forceFill($tenantUpdates)->save();
            }

            return $tenant;
        }

        $tenant = Tenant::query()->create([
            'name' => trim($user->name) !== '' ? trim($user->name) : 'Ny kunde',
            'slug' => $this->uniqueTenantSlug($user->name),
            'status' => 'active',
            'company_email' => $user->email,
            'phone' => $user->phone,
            'cvr_number' => $user->cvr_number,
            'notes' => $this->buildTenantNotes($user),
        ]);

        $tenant->users()->attach($user->id, ['role' => 'owner']);

        return $tenant;
    }

    private function seedDefaultPages(Site $site): void
    {
        $landingTemplate = $this->preferredTemplate($site->theme, 'landing');
        $contactTemplate = $this->preferredTemplate($site->theme, 'contact');

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => $site->name,
            'template_key' => $landingTemplate,
            'meta_description' => null,
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        SitePageTemplates::createForPage($homePage, $landingTemplate);

        if ($contactTemplate === null) {
            return;
        }

        $contactPage = $site->pages()->create([
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'title' => 'Kontakt',
            'template_key' => $contactTemplate,
            'meta_description' => null,
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        SitePageTemplates::createForPage($contactPage, $contactTemplate);
    }

    private function preferredTemplate(string $theme, string $preferredKey): ?string
    {
        $availableTemplates = SitePageTemplates::availableForTheme($theme);

        if ($availableTemplates === []) {
            return null;
        }

        if (array_key_exists($preferredKey, $availableTemplates)) {
            return $preferredKey;
        }

        return array_key_first($availableTemplates);
    }

    private function uniqueTenantSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'kunde';
        $slug = $baseSlug;
        $suffix = 2;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function uniqueSiteSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'nyt-site';
        $slug = $baseSlug;
        $suffix = 2;

        while (Site::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function buildTenantNotes(User $user): ?string
    {
        $notes = collect([
            filled($user->registration_note) ? trim((string) $user->registration_note) : null,
            $user->wants_callback ? 'Kunden ønsker et opkald med gennemgang.' : null,
        ])->filter()->implode("\n\n");

        return $notes !== '' ? $notes : null;
    }
}
