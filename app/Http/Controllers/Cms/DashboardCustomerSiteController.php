<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Sites\SiteDraftManager;
use App\Support\Sites\SitePageTemplates;
use App\Support\Sites\SiteThemes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class DashboardCustomerSiteController extends Controller
{
    public function create(Request $request): View
    {
        abort_unless($request->user()?->canManageCustomers(), 403);

        return view('cms.pages.customer-sites.create', [
            'plans' => Plan::query()->active()->ordered()->get(),
            'availableThemes' => SiteThemes::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageCustomers(), 403);

        $contactEmail = Str::lower(trim((string) $request->input('contact_email')));
        $existingUser = $contactEmail !== '' ? User::query()->firstWhere('email', $contactEmail) : null;

        if ($existingUser?->isDeveloper()) {
            return back()
                ->withErrors([
                    'contact_email' => 'Den valgte e-mail tilhører allerede en developer-bruger. Brug en kunde-e-mail i stedet.',
                ], 'createCustomerSite')
                ->withInput();
        }

        $validated = $request->validateWithBag('createCustomerSite', [
            'customer_type' => ['required', Rule::in(['company', 'private'])],
            'tenant_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email:rfc', 'max:255'],
            'cvr_number' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:50'],
            'site_name' => ['required', 'string', 'max:255'],
            'site_slug' => ['nullable', 'string', 'max:255'],
            'contact_name' => [$existingUser ? 'nullable' : 'required', 'string', 'max:255'],
            'contact_email' => ['required', 'email:rfc', 'max:255'],
            'contact_password' => [$existingUser ? 'nullable' : 'required', Password::defaults()],
            'theme' => ['required', 'string', Rule::in(SiteThemes::keys())],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        $site = DB::transaction(function () use ($validated, $existingUser, $contactEmail): Site {
            $tenant = Tenant::query()->create([
                'name' => trim($validated['tenant_name']),
                'slug' => $this->uniqueTenantSlug($validated['tenant_name']),
                'status' => 'active',
                'company_email' => $this->nullIfBlank($validated['company_email'] ?? $contactEmail),
                'cvr_number' => $validated['customer_type'] === 'company'
                    ? $this->nullIfBlank($validated['cvr_number'] ?? null)
                    : null,
                'phone' => $this->nullIfBlank($validated['phone'] ?? null),
            ]);

            $customer = $existingUser;

            if (! $customer) {
                $customer = User::query()->create([
                    'name' => trim((string) $validated['contact_name']),
                    'email' => $contactEmail,
                    'password' => $validated['contact_password'],
                    'role' => 'client',
                    'email_verified_at' => now(),
                ]);
            }

            $tenant->users()->attach($customer->id, ['role' => 'owner']);

            $site = Site::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $validated['plan_id'] ?? null,
                'name' => trim($validated['site_name']),
                'slug' => $this->uniqueSiteSlug($validated['site_slug'] ?: $validated['site_name']),
                'theme' => $validated['theme'],
                'status' => 'draft',
                'is_online' => false,
            ]);

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

            if ($contactTemplate !== null) {
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

            SiteDraftManager::refreshDraftsFromLive($site);

            return $site;
        });

        return redirect()
            ->route('cms.sites.show', $site)
            ->with('status', "Kunden '{$site->tenant->name}', login-brugeren og sitet '{$site->name}' er oprettet.");
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

    private function nullIfBlank(?string $value): ?string
    {
        $candidate = trim((string) $value);

        return $candidate !== '' ? $candidate : null;
    }
}
