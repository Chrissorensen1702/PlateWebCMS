<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Support\Http\LocalRedirect;
use App\Support\Http\PublicSiteUrl as PublicSiteUrlSanitizer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SiteBookingController extends Controller
{
    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $site->loadMissing('tenant.users', 'bookingSettings');

        $submitAction = $request->string('submit_action')->trim()->lower()->value();
        $isProvisionAction = $submitAction === 'provision';
        $isCreateMode = $request->string('connection_mode')->trim()->lower()->value() === 'create';
        $isEnabled = $request->boolean('is_enabled');

        $validated = $request->validateWithBag('updateSiteBooking', [
            'is_enabled' => ['nullable', 'boolean'],
            'connection_mode' => ['required', 'string', 'in:create,existing'],
            'booking_reference' => [
                Rule::requiredIf($isEnabled && ! $isCreateMode),
                'nullable',
                'string',
                'max:255',
            ],
            'submit_action' => ['nullable', 'string', 'in:save,provision'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $settings = $site->bookingSettings()->firstOrNew();
        $isEnabled = (bool) ($validated['is_enabled'] ?? false);
        $resolvedConnectionMode = $validated['connection_mode'];
        $bookingReference = $this->nullableText($validated['booking_reference'] ?? null) ?? $this->nullableText($settings->booking_reference);
        $bookingUrl = PublicSiteUrlSanitizer::sanitize($settings->booking_url);
        $dashboardUrl = $this->resolveDashboardUrl(fallback: $settings->dashboard_url);
        $ownerName = $this->nullableText($settings->owner_name);
        $ownerEmail = $this->nullableText($settings->owner_email);
        $provisionedAt = $settings->provisioned_at;

        if ($isEnabled && $isProvisionAction && $isCreateMode) {
            $provisionOwner = $this->resolveProvisionOwner($request, $site);
            $provisionedAccount = $this->provisionBookingAccount($site, $provisionOwner);

            $resolvedConnectionMode = 'existing';
            $bookingReference = $provisionedAccount['tenant_slug'] ?? $bookingReference;
            $bookingUrl = PublicSiteUrlSanitizer::sanitize($provisionedAccount['booking_url'] ?? null) ?? $bookingUrl;
            $dashboardUrl = $this->resolveDashboardUrl(
                preferred: $provisionedAccount['dashboard_url'] ?? null,
                fallback: $dashboardUrl,
            );
            $ownerName = $this->nullableText($provisionOwner->name) ?? $ownerName;
            $ownerEmail = $this->nullableText($provisionedAccount['owner_email'] ?? null)
                ?? $this->nullableText($provisionOwner->email)
                ?? $ownerEmail;
            $provisionedAt = now();
        }

        $settings->fill([
            'is_enabled' => $isEnabled,
            'connection_mode' => $resolvedConnectionMode,
            'booking_reference' => $bookingReference,
            'booking_url' => $bookingUrl,
            'dashboard_url' => $dashboardUrl,
            'owner_name' => $ownerName,
            'owner_email' => $ownerEmail,
            'provisioned_at' => $provisionedAt,
            // Future website placement stays outside this integration screen.
            'cta_label' => $this->nullableText($settings->cta_label),
            'use_on_website' => (bool) ($settings->use_on_website ?? false),
            'show_in_header' => (bool) ($settings->show_in_header ?? false),
            'show_in_contact_sections' => (bool) ($settings->show_in_contact_sections ?? false),
            'open_in_new_tab' => (bool) ($settings->open_in_new_tab ?? false),
        ]);

        $site->bookingSettings()->save($settings);

        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.global.section', [$site, 'booking']))
            ->with('status', $isEnabled && $isProvisionAction && $isCreateMode
                ? 'Bookingsystemet er aktiveret og koblet til sitet.'
                : 'Bookingsystem-koblingen er opdateret.');
    }

    private function nullableText(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function resolveProvisionOwner(Request $request, Site $site): User
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser instanceof User
            && ! $authenticatedUser->isDeveloper()
            && $authenticatedUser->belongsToTenant($site->tenant)) {
            return $authenticatedUser;
        }

        $tenantOwner = $site->tenant?->primary_contact;

        if ($tenantOwner instanceof User) {
            return $tenantOwner;
        }

        throw ValidationException::withMessages([
            'is_enabled' => 'CMS kunne ikke finde en gyldig tenant-bruger at aktivere bookingsystemet med.',
        ])->errorBag('updateSiteBooking');
    }

    private function resolveDashboardUrl(mixed $preferred = null, mixed $fallback = null): ?string
    {
        $configuredBaseUrl = trim((string) config('services.bookingsystem.base_url', ''));
        $defaultUrl = $configuredBaseUrl !== ''
            ? rtrim($configuredBaseUrl, '/').'/login'
            : null;

        return PublicSiteUrlSanitizer::sanitize($preferred)
            ?? PublicSiteUrlSanitizer::sanitize($defaultUrl)
            ?? PublicSiteUrlSanitizer::sanitize($fallback);
    }

    /**
     * @return array<string, mixed>
     */
    private function provisionBookingAccount(Site $site, User $owner): array
    {
        $baseUrl = rtrim(trim((string) config('services.bookingsystem.base_url', '')), '/');
        $token = trim((string) config('services.bookingsystem.integration_token', ''));
        $endpoint = '/'.ltrim(trim((string) config('services.bookingsystem.provision_endpoint', '/integrations/cms/booking-accounts')), '/');
        $passwordHash = $this->nullableText($owner->getAuthPassword());

        if ($baseUrl === '' || $token === '') {
            throw ValidationException::withMessages([
                'is_enabled' => 'Bookingsystem-integrationen mangler BOOKINGSYSTEM_URL eller BOOKINGSYSTEM_CMS_TOKEN i CMS-konfigurationen.',
            ])->errorBag('updateSiteBooking');
        }

        if ($this->nullableText($owner->email) === null || $passwordHash === null) {
            throw ValidationException::withMessages([
                'is_enabled' => 'Den valgte CMS-bruger mangler e-mail eller password og kan derfor ikke bruges som booking-login endnu.',
            ])->errorBag('updateSiteBooking');
        }

        $tenant = $site->tenant;

        try {
            $response = Http::acceptJson()
                ->baseUrl($baseUrl)
                ->withHeaders([
                    'X-CMS-INTEGRATION-TOKEN' => $token,
                ])
                ->post($endpoint, [
                    'tenant_name' => $tenant?->name ?: $site->name,
                    'tenant_slug' => $tenant?->slug ?: $site->slug,
                    'company_email' => $tenant?->company_email,
                    'phone' => $tenant?->phone,
                    'site_name' => $site->name,
                    'owner_name' => $owner->name,
                    'owner_email' => $owner->email,
                    'owner_password_hash' => $passwordHash,
                ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'is_enabled' => 'CMS kunne ikke kontakte bookingsystemet. Tjek BOOKINGSYSTEM_URL og at bookingsystemet korer.',
            ])->errorBag('updateSiteBooking');
        }

        if ($response->successful()) {
            /** @var array<string, mixed> $payload */
            $payload = $response->json();

            return $payload;
        }

        if ($response->status() === 422) {
            /** @var array<string, list<string>> $errors */
            $errors = $response->json('errors', []);
            $mappedErrors = [];

            foreach ($errors as $field => $messages) {
                $mappedField = match ($field) {
                    'owner_name', 'owner_email', 'owner_password', 'owner_password_hash' => 'is_enabled',
                    default => 'booking_reference',
                };

                $mappedErrors[$mappedField] = $messages[0] ?? 'Bookingsystemet afviste aktiveringen.';
            }

            throw ValidationException::withMessages($mappedErrors)->errorBag('updateSiteBooking');
        }

        $message = trim((string) ($response->json('message') ?? ''));

        throw ValidationException::withMessages([
            'is_enabled' => $message !== ''
                ? $message
                : 'Bookingsystemet svarede med en fejl under aktiveringen.',
        ])->errorBag('updateSiteBooking');
    }
}
