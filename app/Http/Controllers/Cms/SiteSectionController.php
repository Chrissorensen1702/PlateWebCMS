<?php

namespace App\Http\Controllers\Cms;

use App\Models\Site;
use App\Models\SitePageDraft;
use App\Models\SitePageDraftArea;
use App\Support\Sites\SitePageAreaBlueprints;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SiteSectionController extends SitePageAreaController
{
    public function store(Request $request, Site $site, SitePageDraft $page): RedirectResponse
    {
        $this->authorize('update', $site);
        abort_unless($page->site_id === $site->id, 404);

        $validated = $request->validateWithBag('createSection', [
            'area_type' => ['required', 'string', Rule::in(array_keys(SitePageAreaBlueprints::availableForTheme($site->theme)))],
        ]);

        $area = null;

        DB::transaction(function () use ($page, $validated, &$area): void {
            $area = SitePageAreaBlueprints::createForPage($page, $validated['area_type']);
        });

        $label = SitePageAreaBlueprints::displayLabel($area->area_type, $area->label, $area->area_key);

        return redirect()
            ->to(route('cms.pages.show', [$site, $page])."#area-{$area->id}")
            ->with('status', "Afsnittet '{$label}' er tilfoejet til siden.");
    }

    public function reorder(Request $request, Site $site, SitePageDraft $page): RedirectResponse
    {
        $this->authorize('update', $site);
        abort_unless($page->site_id === $site->id, 404);

        $validated = $request->validate([
            'section_ids' => ['required', 'array', 'min:1'],
            'section_ids.*' => ['required', 'integer'],
            'focus_section_id' => ['nullable', 'integer'],
        ]);

        $submittedIds = collect($validated['section_ids'])
            ->map(fn (mixed $id): int => (int) $id)
            ->values();

        $existingIds = $this->orderedSections($page)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values();

        abort_unless(
            $submittedIds->duplicates()->isEmpty()
            && $submittedIds->count() === $existingIds->count()
            && $submittedIds->sort()->values()->all() === $existingIds->sort()->values()->all(),
            422,
        );

        DB::transaction(function () use ($page, $submittedIds): void {
            foreach ($submittedIds->values() as $index => $sectionId) {
                $page->areas()
                    ->whereKey($sectionId)
                    ->update([
                        'sort_order' => $index + 1,
                    ]);
            }
        });

        $focusSectionId = isset($validated['focus_section_id']) ? (int) $validated['focus_section_id'] : null;

        return $this->redirectToEditor($site, $page, $focusSectionId, 'Sektionsraekkefolgen er opdateret.');
    }

    public function visibility(Request $request, Site $site, SitePageDraft $page, SitePageDraftArea $section): RedirectResponse
    {
        $this->authorizeSection($site, $page, $section);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $section->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        $message = $section->is_active
            ? "Afsnittet '{$this->labelFor($section)}' vises nu paa siden."
            : "Afsnittet '{$this->labelFor($section)}' er skjult paa siden.";

        return $this->redirectToEditor($site, $page, $section->id, $message);
    }

    public function destroy(Request $request, Site $site, SitePageDraft $page, SitePageDraftArea $section): RedirectResponse|JsonResponse
    {
        $this->authorizeSection($site, $page, $section);

        $sections = $this->orderedSections($page);
        $currentIndex = $sections->search(fn (SitePageDraftArea $candidate): bool => $candidate->is($section));
        $focusSectionId = null;

        if (is_int($currentIndex)) {
            $focusSectionId = $sections[$currentIndex + 1]->id
                ?? $sections[$currentIndex - 1]->id
                ?? null;
        }

        $label = $this->labelFor($section);

        DB::transaction(function () use ($page, $section): void {
            $section->delete();

            $this->orderedSections($page)->values()->each(function (SitePageDraftArea $candidate, int $index): void {
                $candidate->update([
                    'sort_order' => $index + 1,
                ]);
            });
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'deleted',
                'message' => "Afsnittet '{$label}' er fjernet fra siden.",
                'focus_section_id' => $focusSectionId,
            ]);
        }

        return $this->redirectToEditor($site, $page, $focusSectionId, "Afsnittet '{$label}' er fjernet fra siden.");
    }

    private function authorizeSection(Site $site, SitePageDraft $page, SitePageDraftArea $section): void
    {
        $this->authorize('update', $site);
        abort_unless($page->site_id === $site->id && $section->site_page_draft_id === $page->id, 404);
    }

    /**
     * @return \Illuminate\Support\Collection<int, SitePageDraftArea>
     */
    private function orderedSections(SitePageDraft $page): Collection
    {
        return $page->areas()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function redirectToEditor(Site $site, SitePageDraft $page, ?int $sectionId, string $message): RedirectResponse
    {
        $url = route('cms.pages.show', [$site, $page]);

        if ($sectionId !== null) {
            $url .= "#area-{$sectionId}";
        }

        return redirect()
            ->to($url)
            ->with('status', $message);
    }

    private function labelFor(SitePageDraftArea $section): string
    {
        return SitePageAreaBlueprints::displayLabel($section->area_type, $section->label, $section->area_key);
    }
}
