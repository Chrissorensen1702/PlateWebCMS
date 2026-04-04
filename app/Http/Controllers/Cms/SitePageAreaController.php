<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SitePage;
use App\Models\SitePageArea;
use App\Rules\PublicSiteUrl;
use App\Support\Http\PublicSiteUrl as PublicSiteUrlSanitizer;
use App\Support\Sites\SitePageAreaBlueprints;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SitePageAreaController extends Controller
{
    public function update(Request $request, Site $site, SitePage $page, SitePageArea $area): RedirectResponse
    {
        $this->authorize('update', $site);
        abort_unless($page->site_id === $site->id && $area->site_page_id === $page->id, 404);

        $validated = validator(array_replace_recursive($request->all(), $request->allFiles()), [
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'copy' => ['nullable', 'string', 'max:4000'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'image_focus' => ['nullable', 'string', Rule::in(['left', 'center', 'right', 'top', 'bottom'])],
            'image_upload' => ['nullable', 'image', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'heading_size' => ['nullable', 'string', Rule::in(['standard', 'large'])],
            'text_align' => ['nullable', 'string', Rule::in(['left', 'center'])],
            'button_align' => ['nullable', 'string', Rule::in(['left', 'center', 'right'])],
            'secondary_cta_mode' => ['nullable', 'string', Rule::in(['show', 'hide'])],
            'primary_cta_label' => ['nullable', 'string', 'max:255'],
            'primary_cta_href' => ['nullable', 'string', 'max:255', new PublicSiteUrl()],
            'secondary_cta_label' => ['nullable', 'string', 'max:255'],
            'secondary_cta_href' => ['nullable', 'string', 'max:255', new PublicSiteUrl()],
            'items_style' => ['nullable', 'string', Rule::in(['list', 'cards'])],
            'section_tone' => ['nullable', 'string', Rule::in(['default', 'accent'])],
            'items' => ['nullable', 'string', 'max:4000'],
            'service_prices' => ['nullable', 'string', 'max:4000'],
            'display_style' => ['nullable', 'string', Rule::in(['cards', 'strip'])],
            'quote_text' => ['nullable', 'string', 'max:4000'],
            'quote_author' => ['nullable', 'string', 'max:255'],
            'quote_role' => ['nullable', 'string', 'max:255'],
            'layout_style' => ['nullable', 'string', Rule::in(['split', 'center', 'map', 'stacked', 'cards'])],
            'show_phone' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_href' => ['nullable', 'string', 'max:255', new PublicSiteUrl()],
            'map_embed_url' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['nullable', 'boolean'],
        ])->validate();

        $validated = $this->withStoredUploads($request, $site, $page, $area, $validated);

        DB::transaction(function () use ($area, $validated): void {
            $area->update([
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $area->syncData($this->payloadForArea($area, $validated));
        });

        $label = SitePageAreaBlueprints::displayLabel($area->area_type, $area->label, $area->area_key);

        return redirect()
            ->route('cms.pages.show', [$site, $page])
            ->with('status', "Afsnittet '{$label}' er opdateret.");
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function payloadForArea(SitePageArea $area, array $validated): array
    {
        return match ($area->area_type) {
            'hero' => $this->sanitizePublicLinks($this->onlyFilled($validated, [
                'eyebrow',
                'title',
                'copy',
                'image_url',
                'image_alt',
                'image_focus',
                'heading_size',
                'text_align',
                'button_align',
                'secondary_cta_mode',
                'primary_cta_label',
                'primary_cta_href',
                'secondary_cta_label',
                'secondary_cta_href',
            ]), ['primary_cta_href', 'secondary_cta_href']),
            'contact' => $this->sanitizePublicLinks($this->onlyFilled($validated, [
                'eyebrow',
                'title',
                'copy',
                'layout_style',
                'section_tone',
                'show_phone',
                'email',
                'phone',
                'cta_label',
                'cta_href',
                'map_embed_url',
            ]), ['cta_href']),
            'stats' => array_merge(
                $this->onlyFilled($validated, ['eyebrow', 'title', 'copy', 'display_style', 'section_tone']),
                [
                    'items' => $this->normalizeItems($validated['items'] ?? ''),
                ],
            ),
            'quote' => $this->onlyFilled($validated, [
                'eyebrow',
                'quote_text',
                'quote_author',
                'quote_role',
                'text_align',
                'section_tone',
            ]),
            'faq' => array_merge(
                $this->onlyFilled($validated, ['eyebrow', 'title', 'copy', 'layout_style', 'section_tone']),
                [
                    'items' => $this->normalizeItems($validated['items'] ?? ''),
                ],
            ),
            'content' => array_merge(
                $this->onlyFilled($validated, ['eyebrow', 'title', 'copy', 'text_align', 'items_style', 'section_tone']),
                [
                    'items' => $this->normalizeItems($validated['items'] ?? ''),
                    'service_prices' => $this->normalizeItems($validated['service_prices'] ?? ''),
                ],
            ),
            default => $this->onlyFilled($validated, ['eyebrow', 'title', 'copy']),
        };
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function withStoredUploads(
        Request $request,
        Site $site,
        SitePage $page,
        SitePageArea $area,
        array $payload,
    ): array {
        if ($area->area_type !== 'hero') {
            return $payload;
        }

        if ((bool) ($payload['remove_image'] ?? false)) {
            $payload['image_url'] = '';
            $payload['image_alt'] = '';
        }

        $uploadedImage = $request->file('image_upload');

        if ($uploadedImage instanceof UploadedFile) {
            $payload['image_url'] = $uploadedImage->storePublicly(
                "site-media/{$site->slug}/{$page->slug}/{$area->area_key}",
                $this->siteMediaDisk(),
            );
        }

        unset($payload['image_upload'], $payload['remove_image']);

        return $payload;
    }

    private function siteMediaDisk(): string
    {
        return (string) config('filesystems.site_media_disk', 'public');
    }

    /**
     * @param array<string, mixed> $validated
     * @param list<string> $keys
     * @return array<string, mixed>
     */
    private function onlyFilled(array $validated, array $keys): array
    {
        return Collection::make($keys)
            ->mapWithKeys(fn (string $key): array => [$key => trim((string) ($validated[$key] ?? ''))])
            ->filter(fn (mixed $value): bool => $value !== '')
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $keys
     * @return array<string, mixed>
     */
    private function sanitizePublicLinks(array $payload, array $keys): array
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }

            $sanitized = PublicSiteUrlSanitizer::sanitize($payload[$key]);

            if ($sanitized === null) {
                unset($payload[$key]);
                continue;
            }

            $payload[$key] = $sanitized;
        }

        return $payload;
    }

    /**
     * @return list<string>
     */
    private function normalizeItems(string $items): array
    {
        return Collection::make(preg_split("/\r\n|\r|\n/", $items) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
