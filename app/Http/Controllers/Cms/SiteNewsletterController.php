<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Support\Http\LocalRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteNewsletterController extends Controller
{
    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $validated = $request->validateWithBag('updateSiteNewsletter', [
            'is_enabled' => ['nullable', 'boolean'],
            'headline' => ['nullable', 'string', 'max:255'],
            'copy' => ['nullable', 'string', 'max:2000'],
            'button_label' => ['nullable', 'string', 'max:255'],
            'placement' => ['required', 'string', 'in:footer,section,both'],
            'delivery_mode' => ['required', 'string', 'in:cms,external'],
            'consent_text' => ['nullable', 'string', 'max:2000'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $settings = $site->newsletterSettings()->firstOrNew();
        $settings->fill([
            'is_enabled' => (bool) ($validated['is_enabled'] ?? false),
            'headline' => $this->nullableText($validated['headline'] ?? null),
            'copy' => $this->nullableText($validated['copy'] ?? null),
            'button_label' => $this->nullableText($validated['button_label'] ?? null),
            'placement' => $validated['placement'],
            'delivery_mode' => $validated['delivery_mode'],
            'consent_text' => $this->nullableText($validated['consent_text'] ?? null),
        ]);

        $site->newsletterSettings()->save($settings);

        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.global.section', [$site, 'newsletter']))
            ->with('status', 'Nyhedsbrev er opdateret.');
    }

    private function nullableText(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
