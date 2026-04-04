<?php

namespace App\Support\Sites;

use App\Models\Site;
use Illuminate\Support\Facades\DB;

class SiteDraftManager
{
    public static function ensureDraftsForSite(Site $site): void
    {
        if ($site->draft_initialized_at !== null) {
            return;
        }

        self::refreshDraftsFromLive($site);
    }

    public static function refreshDraftsFromLive(Site $site): void
    {
        $pages = $site->pages()
            ->with([
                'areas' => fn ($query) => $query->ordered()->with('fields'),
            ])
            ->ordered()
            ->get();

        DB::transaction(function () use ($site, $pages): void {
            $site->draftPages()->delete();

            foreach ($pages as $page) {
                $draftPage = $site->draftPages()->create([
                    'source_page_id' => $page->id,
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'template_key' => $page->template_key,
                    'layout_mode' => $page->layout_mode,
                    'custom_html' => $page->custom_html,
                    'custom_css' => $page->custom_css,
                    'meta_description' => $page->meta_description,
                    'is_home' => $page->is_home,
                    'is_published' => $page->is_published,
                    'sort_order' => $page->sort_order,
                ]);

                foreach ($page->areas as $area) {
                    $draftArea = $draftPage->areas()->create([
                        'source_area_id' => $area->id,
                        'area_key' => $area->area_key,
                        'area_type' => $area->area_type,
                        'label' => $area->label,
                        'sort_order' => $area->sort_order,
                        'is_active' => $area->is_active,
                    ]);

                    foreach ($area->fields as $field) {
                        $draftArea->fields()->create([
                            'field_key' => $field->field_key,
                            'position' => $field->position,
                            'value' => $field->value,
                        ]);
                    }
                }
            }

            $site->forceFill([
                'draft_initialized_at' => now(),
            ])->save();
        });

        $site->unsetRelation('draftPages');
        $site->refresh();
    }

    public static function publishSite(Site $site): void
    {
        self::ensureDraftsForSite($site);

        $draftPages = $site->draftPages()
            ->with([
                'areas' => fn ($query) => $query->ordered()->with('fields'),
            ])
            ->ordered()
            ->get();

        DB::transaction(function () use ($site, $draftPages): void {
            $retainedPageIds = [];

            foreach ($draftPages as $draftPage) {
                $livePage = $draftPage->sourcePage;

                if ($livePage === null || $livePage->site_id !== $site->id) {
                    $livePage = $site->pages()->create(self::pageAttributes($draftPage));
                    $draftPage->forceFill(['source_page_id' => $livePage->id])->save();
                } else {
                    $livePage->update(self::pageAttributes($draftPage));
                }

                $retainedPageIds[] = $livePage->id;

                $existingAreas = $livePage->areas()
                    ->with('fields')
                    ->get()
                    ->keyBy('id');

                $retainedAreaIds = [];

                foreach ($draftPage->areas as $draftArea) {
                    $liveArea = $draftArea->sourceArea;

                    if ($liveArea === null || $liveArea->site_page_id !== $livePage->id) {
                        $liveArea = $livePage->areas()->create(self::areaAttributes($draftArea));
                        $draftArea->forceFill(['source_area_id' => $liveArea->id])->save();
                    } else {
                        $liveArea->update(self::areaAttributes($draftArea));
                    }

                    $retainedAreaIds[] = $liveArea->id;
                    $liveArea->fields()->delete();

                    foreach ($draftArea->fields as $field) {
                        $liveArea->fields()->create([
                            'field_key' => $field->field_key,
                            'position' => $field->position,
                            'value' => $field->value,
                        ]);
                    }
                }

                $areasToDelete = $existingAreas->keys()->diff($retainedAreaIds)->all();

                if ($areasToDelete !== []) {
                    $livePage->areas()->whereKey($areasToDelete)->delete();
                }
            }

            $pagesToDelete = $site->pages()->pluck('id')->diff($retainedPageIds)->all();

            if ($pagesToDelete !== []) {
                $site->pages()->whereKey($pagesToDelete)->delete();
            }

            $site->forceFill([
                'draft_initialized_at' => now(),
                'last_published_at' => now(),
            ])->save();
        });

        $site->unsetRelation('pages');
        $site->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private static function pageAttributes(object $draftPage): array
    {
        return [
            'name' => $draftPage->name,
            'slug' => $draftPage->slug,
            'title' => $draftPage->title,
            'template_key' => $draftPage->template_key,
            'layout_mode' => $draftPage->layout_mode ?? 'structured',
            'custom_html' => $draftPage->custom_html,
            'custom_css' => $draftPage->custom_css,
            'meta_description' => $draftPage->meta_description,
            'is_home' => (bool) $draftPage->is_home,
            'is_published' => (bool) $draftPage->is_published,
            'sort_order' => (int) $draftPage->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function areaAttributes(object $draftArea): array
    {
        return [
            'area_key' => $draftArea->area_key,
            'area_type' => $draftArea->area_type,
            'label' => $draftArea->label,
            'sort_order' => (int) $draftArea->sort_order,
            'is_active' => (bool) $draftArea->is_active,
        ];
    }
}
