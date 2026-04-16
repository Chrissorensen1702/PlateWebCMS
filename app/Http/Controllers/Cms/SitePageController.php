<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SitePageDraft;
use App\Models\SitePageDraftArea;
use App\Rules\PublicSiteUrl;
use App\Support\Cms\SiteFeatureGate;
use App\Support\Sites\SiteDraftManager;
use App\Support\Sites\SitePageAreaBlueprints;
use App\Support\Sites\SitePageLayoutModes;
use App\Support\Sites\SitePageTemplates;
use App\Support\Sites\SiteThemes;
use App\Support\Http\PublicSiteUrl as PublicSiteUrlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SitePageController extends Controller
{
    public function __construct(
        private readonly SiteFeatureGate $siteFeatureGate,
    ) {}

    public function show(Request $request, Site $site, SitePageDraft $page): View
    {
        $this->authorize('view', $site);
        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CONTENT, $request->user());
        SiteDraftManager::ensureDraftsForSite($site);
        abort_unless($page->site_id === $site->id, 404);

        $site->load([
            'tenant.users',
            'plan',
        ]);

        $page->load([
            'areas' => fn ($query) => $query->ordered(),
        ]);

        $previewEditorPageMap = $site->draftPages()
            ->ordered()
            ->get()
            ->map(function (SitePageDraft $draftPage) use ($site): array {
                return [
                    'public_url' => $draftPage->is_home
                        ? route('sites.show', $site)
                        : route('sites.page', [$site, $draftPage->slug]),
                    'editor_url' => route('cms.pages.show', [$site, $draftPage]),
                ];
            })
            ->values()
            ->all();

        return view('cms.pages.sites.page', [
            'site' => $site,
            'page' => $page,
            'canUpdateSite' => $request->user()->can('update', $site),
            'canUseCustomCode' => $this->siteFeatureGate->canUseCustomCode($site, $request->user()),
            'availablePageTemplates' => SitePageTemplates::availableForTheme($site->theme),
            'availableSectionCategories' => SitePageAreaBlueprints::groupedForTheme($site->theme),
            'previewEditorPageMap' => $previewEditorPageMap,
        ]);
    }

    public function settings(Request $request, Site $site, SitePageDraft $page): View
    {
        $this->authorize('view', $site);
        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CONTENT, $request->user());
        SiteDraftManager::ensureDraftsForSite($site);
        abort_unless($page->site_id === $site->id, 404);

        $site->load([
            'tenant.users',
            'plan',
        ]);

        $page->load([
            'areas' => fn ($query) => $query->ordered(),
        ]);

        return view('cms.pages.sites.page-settings', [
            'site' => $site,
            'page' => $page,
            'canUpdateSite' => $request->user()->can('update', $site),
            'canUseCustomCode' => $this->siteFeatureGate->canUseCustomCode($site, $request->user()),
        ]);
    }

    public function customCode(Request $request, Site $site, SitePageDraft $page): View
    {
        $this->authorize('view', $site);
        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CUSTOM_CODE, $request->user());
        SiteDraftManager::ensureDraftsForSite($site);
        abort_unless($page->site_id === $site->id, 404);

        $site->load([
            'tenant.users',
            'plan',
        ]);

        $page->load([
            'areas' => fn ($query) => $query->ordered(),
        ]);

        return view('cms.pages.sites.page-custom-code', [
            'site' => $site,
            'page' => $page,
            'canUpdateSite' => $request->user()->can('update', $site),
        ]);
    }

    public function preview(Request $request, Site $site, SitePageDraft $page): View
    {
        $this->authorize('view', $site);
        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CONTENT, $request->user());
        SiteDraftManager::ensureDraftsForSite($site);
        abort_unless($page->site_id === $site->id, 404);

        $site->loadMissing(['headerSettings', 'footerSettings', 'colorSettings', 'tenant']);

        $navigation = $site->draftPages()
            ->where('is_published', true)
            ->ordered()
            ->get();

        if ($navigation->isEmpty()) {
            $navigation = $site->draftPages()
                ->ordered()
                ->get();
        }

        $page->load([
            'areas' => fn ($query) => $query->where('is_active', true)->ordered(),
        ]);

        $theme = $site->theme ?: 'base';

        if (! in_array($theme, SiteThemes::keys(), true) || ! view()->exists("sites.themes.{$theme}.page")) {
            $theme = 'base';
        }

        return view("sites.themes.{$theme}.page", [
            'site' => $site,
            'page' => $page,
            'navigation' => $navigation,
            'theme' => $theme,
        ]);
    }

    public function store(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);
        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CONTENT, $request->user());
        SiteDraftManager::ensureDraftsForSite($site);

        $validated = $request->validateWithBag('createPage', $this->rules($site, null, true), [
            'page_template.required' => 'Vaelg hvilken sidetype du vil oprette.',
            'page_template.in' => 'Den valgte sidetype findes ikke i det aktive theme.',
        ]);
        $pageData = $this->pagePayload($site, $validated);
        $this->ensureUniqueSlug($site, $pageData['slug'], 'createPage');
        $pageTemplate = (string) $validated['page_template'];

        DB::transaction(function () use ($site, $pageData, $pageTemplate): void {
            $pageData['is_home'] = $this->resolveHomeState($site, null, $pageData['is_home']);
            $pageData['template_key'] = $pageTemplate;

            if ($pageData['is_home']) {
                $site->draftPages()->update(['is_home' => false]);
            }

            $page = $site->draftPages()->create($pageData);
            SitePageTemplates::createForPage($page, $pageTemplate);
        });

        $page = $site->draftPages()->where('slug', $pageData['slug'])->firstOrFail();

        return redirect()
            ->route('cms.sites.show', ['site' => $site, 'page' => $page->id])
            ->with('status', "Siden '{$pageData['name']}' er oprettet i kladden.");
    }

    public function update(Request $request, Site $site, SitePageDraft $page): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $site);
        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CONTENT, $request->user());
        SiteDraftManager::ensureDraftsForSite($site);
        abort_unless($page->site_id === $site->id, 404);
        $this->ensureCustomCodeAccessIfRequested($request, $site);
        $publishAfterSave = $request->boolean('publish_after_save');
        $returnTo = in_array($request->string('return_to')->toString(), ['design', 'settings', 'dashboard', 'custom-code'], true)
            ? $request->string('return_to')->toString()
            : 'design';

        $page->load([
            'areas' => fn ($query) => $query->ordered(),
        ]);

        $validated = $this->validateDraftUpdate($request, $site, $page);
        $pageData = $this->pagePayload($site, $validated, $page);
        $this->ensureUniqueSlug($site, $pageData['slug'], 'updatePage', $page);
        $shouldSyncAreas = array_key_exists('areas', $validated) && is_array($validated['areas']);

        DB::transaction(function () use ($request, $site, $page, $pageData, $validated, $shouldSyncAreas): void {
            $pageData['is_home'] = $this->resolveHomeState($site, $page, $pageData['is_home']);

            if ($pageData['is_home']) {
                $site->draftPages()
                    ->whereKeyNot($page->id)
                    ->update(['is_home' => false]);
            }

            $page->update($pageData);

            if ($shouldSyncAreas) {
                foreach ($page->areas as $area) {
                    $areaPayload = $validated['areas'][$area->id] ?? [];
                    $areaPayload = $this->withStoredUploads($request, $site, $page, $area, $areaPayload);

                    $area->update([
                        'is_active' => (bool) ($areaPayload['is_active'] ?? false),
                    ]);

                    $area->syncData($this->payloadForArea($area, $areaPayload));
                }
            }
        });

        if ($publishAfterSave) {
            SiteDraftManager::publishSite($site);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'published',
                    'message' => "Siden '{$page->name}' er gemt, og hele sitet er publiceret.",
                    'preview_url' => route('cms.pages.preview', [$site, $page]),
                ]);
            }

            return redirect()
                ->to($this->returnUrlFor($site, $page, $returnTo))
                ->with('status', "Siden '{$page->name}' er gemt, og hele sitet er publiceret.");
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'saved',
                'message' => "Siden '{$page->name}' er gemt i kladden.",
                'preview_url' => route('cms.pages.preview', [$site, $page]),
            ]);
        }

        return redirect()
            ->to($this->returnUrlFor($site, $page, $returnTo))
            ->with('status', "Siden '{$page->name}' er gemt i kladden.");
    }

    public function destroy(Site $site, SitePageDraft $page): RedirectResponse
    {
        $this->authorize('update', $site);
        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CONTENT, auth()->user());
        SiteDraftManager::ensureDraftsForSite($site);
        abort_unless($page->site_id === $site->id, 404);

        $pageName = $page->name;
        $redirectPage = null;

        DB::transaction(function () use ($site, $page, &$redirectPage): void {
            $page->delete();

            if ($site->draftPages()->exists() && ! $site->draftPages()->where('is_home', true)->exists()) {
                $site->draftPages()
                    ->ordered()
                    ->first()
                    ?->update(['is_home' => true]);
            }

            $redirectPage = $site->draftPages()->ordered()->first();
        });

        return $redirectPage
            ? redirect()
                ->route('cms.sites.show', ['site' => $site, 'page' => $redirectPage->id])
                ->with('status', "Siden '{$pageName}' er slettet fra kladden.")
            : redirect()
                ->route('cms.sites.show', $site)
                ->with('status', "Siden '{$pageName}' er slettet fra kladden.");
    }

    private function returnUrlFor(Site $site, SitePageDraft $page, string $returnTo): string
    {
        if ($returnTo === 'dashboard') {
            return route('cms.sites.show', ['site' => $site, 'page' => $page->id]);
        }

        if ($returnTo === 'settings') {
            return route('cms.pages.settings.show', [$site, $page]);
        }

        if ($returnTo === 'custom-code') {
            return route('cms.pages.custom-code.show', [$site, $page]);
        }

        return route('cms.pages.show', [$site, $page]);
    }

    private function ensureCustomCodeAccessIfRequested(Request $request, Site $site): void
    {
        $requestTouchesCustomCode = $request->exists('layout_mode')
            || $request->exists('custom_html')
            || $request->exists('custom_css')
            || $request->string('return_to')->toString() === 'custom-code';

        if (! $requestTouchesCustomCode) {
            return;
        }

        $this->siteFeatureGate->ensureAllowed($site, SiteFeatureGate::FEATURE_CUSTOM_CODE, $request->user());
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDraftUpdate(Request $request, Site $site, SitePageDraft $page): array
    {
        $rules = $this->rules($site, $page);

        foreach ($page->areas as $area) {
            $prefix = "areas.{$area->id}";

            $rules["{$prefix}.is_active"] = ['nullable', 'boolean'];

            foreach ($this->areaRules($area->area_type) as $field => $fieldRules) {
                $rules["{$prefix}.{$field}"] = $fieldRules;
            }
        }

        return Validator::make(
            array_replace_recursive($request->all(), $request->allFiles()),
            $rules,
        )->validateWithBag('updatePage');
    }

    /**
     * @return array<string, mixed>
     */
    private function pagePayload(Site $site, array $validated, ?SitePageDraft $page = null): array
    {
        $name = trim((string) $validated['name']);
        $title = trim((string) $validated['title']);
        $metaDescription = trim((string) ($validated['meta_description'] ?? ''));
        $requestedSlug = trim((string) ($validated['slug'] ?? ''));
        $slugSource = $requestedSlug !== '' ? $requestedSlug : $name;
        $slug = Str::slug($slugSource);

        if ($slug === '') {
            $slug = $page?->slug ?? 'page-'.$this->nextSortOrder($site);
        }

        return [
            'name' => $name,
            'title' => $title,
            'slug' => $slug,
            'layout_mode' => SitePageLayoutModes::normalize((string) ($validated['layout_mode'] ?? ($page?->layout_mode ?? SitePageLayoutModes::STRUCTURED))),
            'custom_html' => array_key_exists('custom_html', $validated)
                ? ($validated['custom_html'] !== null && trim((string) $validated['custom_html']) !== '' ? (string) $validated['custom_html'] : null)
                : $page?->custom_html,
            'custom_css' => array_key_exists('custom_css', $validated)
                ? ($validated['custom_css'] !== null && trim((string) $validated['custom_css']) !== '' ? (string) $validated['custom_css'] : null)
                : $page?->custom_css,
            'meta_description' => $metaDescription !== '' ? $metaDescription : null,
            'is_home' => (bool) ($validated['is_home'] ?? false),
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'sort_order' => isset($validated['sort_order']) && $validated['sort_order'] !== null && $validated['sort_order'] !== ''
                ? (int) $validated['sort_order']
                : ($page?->sort_order ?? $this->nextSortOrder($site)),
        ];
    }

    private function resolveHomeState(Site $site, ?SitePageDraft $page, bool $requestedHome): bool
    {
        if ($requestedHome) {
            return true;
        }

        $otherPages = $site->draftPages()
            ->when($page, fn ($query) => $query->whereKeyNot($page->id));

        return ! $otherPages->where('is_home', true)->exists();
    }

    private function nextSortOrder(Site $site): int
    {
        return ((int) $site->draftPages()->max('sort_order')) + 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Site $site, ?SitePageDraft $page = null, bool $creating = false): array
    {
        $availableTemplates = array_keys(SitePageTemplates::availableForTheme($site->theme));

        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'layout_mode' => ['nullable', 'string', Rule::in(SitePageLayoutModes::validationModes())],
            'custom_html' => ['nullable', 'string'],
            'custom_css' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_home' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'page_template' => [$creating ? 'required' : 'nullable', 'string', Rule::in($availableTemplates)],
            'form_target' => ['nullable', 'string', 'max:255'],
            'areas' => ['nullable', 'array'],
        ];
    }

    private function ensureUniqueSlug(Site $site, string $slug, string $bag, ?SitePageDraft $page = null): void
    {
        Validator::make(
            ['slug' => $slug],
            [
                'slug' => [
                    Rule::unique('site_page_drafts', 'slug')
                        ->where(fn ($query) => $query->where('site_id', $site->id))
                        ->ignore($page?->id),
                ],
            ],
            [
                'slug.unique' => 'Den URL-del er allerede i brug paa dette site.',
            ],
        )->validateWithBag($bag);
    }

    /**
     * @return array<string, list<string|int>>
     */
    private function areaRules(string $areaType): array
    {
        return match ($areaType) {
            'hero' => [
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
            ],
            'contact' => [
                'eyebrow' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'copy' => ['nullable', 'string', 'max:4000'],
                'layout_style' => ['nullable', 'string', Rule::in(['split', 'center', 'map'])],
                'section_tone' => ['nullable', 'string', Rule::in(['default', 'accent'])],
                'show_phone' => ['nullable', 'boolean'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'cta_label' => ['nullable', 'string', 'max:255'],
                'cta_href' => ['nullable', 'string', 'max:255', new PublicSiteUrl()],
                'map_embed_url' => ['nullable', 'string', 'max:4000'],
            ],
            'stats' => [
                'eyebrow' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'copy' => ['nullable', 'string', 'max:4000'],
                'display_style' => ['nullable', 'string', Rule::in(['cards', 'strip'])],
                'section_tone' => ['nullable', 'string', Rule::in(['default', 'accent'])],
                'items' => ['nullable', 'string', 'max:4000'],
            ],
            'quote' => [
                'eyebrow' => ['nullable', 'string', 'max:255'],
                'quote_text' => ['nullable', 'string', 'max:4000'],
                'quote_author' => ['nullable', 'string', 'max:255'],
                'quote_role' => ['nullable', 'string', 'max:255'],
                'text_align' => ['nullable', 'string', Rule::in(['left', 'center'])],
                'section_tone' => ['nullable', 'string', Rule::in(['default', 'accent'])],
            ],
            'faq' => [
                'eyebrow' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'copy' => ['nullable', 'string', 'max:4000'],
                'layout_style' => ['nullable', 'string', Rule::in(['stacked', 'cards'])],
                'section_tone' => ['nullable', 'string', Rule::in(['default', 'accent'])],
                'items' => ['nullable', 'string', 'max:4000'],
            ],
            'content' => [
                'eyebrow' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'copy' => ['nullable', 'string', 'max:4000'],
                'text_align' => ['nullable', 'string', Rule::in(['left', 'center'])],
                'items_style' => ['nullable', 'string', Rule::in(['list', 'cards'])],
                'section_tone' => ['nullable', 'string', Rule::in(['default', 'accent'])],
                'items' => ['nullable', 'string', 'max:4000'],
                'service_prices' => ['nullable', 'string', 'max:4000'],
            ],
            default => [
                'eyebrow' => ['nullable', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:255'],
                'copy' => ['nullable', 'string', 'max:4000'],
            ],
        };
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function payloadForArea(SitePageDraftArea $area, array $validated): array
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
        SitePageDraft $page,
        SitePageDraftArea $area,
        array $payload,
    ): array {
        if ($area->area_type !== 'hero') {
            return $payload;
        }

        if ((bool) ($payload['remove_image'] ?? false)) {
            $payload['image_url'] = '';
            $payload['image_alt'] = '';
        }

        $uploadedImage = data_get($request->allFiles(), "areas.{$area->id}.image_upload");

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
