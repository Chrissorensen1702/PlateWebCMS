<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Support\Http\LocalRedirect;
use App\Support\Http\PublicSiteUrl as PublicSiteUrlSanitizer;
use App\Support\Sites\SiteFooterSocialPlatforms;
use App\Rules\PublicSiteUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteFooterController extends Controller
{
    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $rules = [
            'navigation_links' => ['nullable', 'array', 'max:8'],
            'navigation_links.*.label' => ['nullable', 'string', 'max:120'],
            'navigation_links.*.href' => ['nullable', 'string', 'max:255', new PublicSiteUrl()],
            'information_links' => ['nullable', 'array', 'max:8'],
            'information_links.*.label' => ['nullable', 'string', 'max:120'],
            'information_links.*.href' => ['nullable', 'string', 'max:255', new PublicSiteUrl()],
            'social_links' => ['nullable', 'array'],
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'show_contact_email' => ['nullable', 'boolean'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'show_contact_phone' => ['nullable', 'boolean'],
            'contact_address' => ['nullable', 'string', 'max:2000'],
            'show_contact_address' => ['nullable', 'boolean'],
            'contact_cvr' => ['nullable', 'string', 'max:255'],
            'show_contact_cvr' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'string'],
        ];

        foreach (SiteFooterSocialPlatforms::definitions() as $platform => $definition) {
            $rules["social_links.$platform"] = ['nullable', 'array'];
            $rules["social_links.$platform.enabled"] = ['nullable', 'boolean'];
            $rules["social_links.$platform.href"] = ['nullable', 'string', 'max:255', new PublicSiteUrl()];
        }

        $validated = $request->validateWithBag('updateSiteFooter', $rules);

        $settings = $site->footerSettings()->firstOrNew();
        $settings->fill([
            'navigation_links' => $this->sanitizeLinkItems($validated['navigation_links'] ?? [], 'label'),
            'information_links' => $this->sanitizeLinkItems($validated['information_links'] ?? [], 'label'),
            'social_links' => SiteFooterSocialPlatforms::sanitizeForStorage($validated['social_links'] ?? []),
            'contact_email' => $this->nullableText($validated['contact_email'] ?? null),
            'show_contact_email' => (bool) ($validated['show_contact_email'] ?? false),
            'contact_phone' => $this->nullableText($validated['contact_phone'] ?? null),
            'show_contact_phone' => (bool) ($validated['show_contact_phone'] ?? false),
            'contact_address' => $this->nullableText($validated['contact_address'] ?? null),
            'show_contact_address' => (bool) ($validated['show_contact_address'] ?? false),
            'contact_cvr' => $this->nullableText($validated['contact_cvr'] ?? null),
            'show_contact_cvr' => (bool) ($validated['show_contact_cvr'] ?? false),
        ]);

        $site->footerSettings()->save($settings);

        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.global.section', [$site, 'header']))
            ->with('status', 'Footeren er opdateret.');
    }

    private function nullableText(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  mixed  $items
     * @return list<array{label?: string, platform?: string, href: string}>
     */
    private function sanitizeLinkItems(mixed $items, string $labelKey): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = $this->nullableText($item[$labelKey] ?? null);
            $href = PublicSiteUrlSanitizer::sanitize($item['href'] ?? null);

            if ($label === null || $href === null) {
                continue;
            }

            $normalized[] = [
                $labelKey => $label,
                'href' => $href,
            ];
        }

        return $normalized;
    }
}
