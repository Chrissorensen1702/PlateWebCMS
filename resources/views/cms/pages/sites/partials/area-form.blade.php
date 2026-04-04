@php
    $data = $area->data ?? [];
    $editorLabel = \App\Support\Sites\SitePageAreaBlueprints::displayLabel($area->area_type, $area->label, $area->area_key);
    if (($site->theme ?? 'base') === 'base' && $area->area_type === 'contact') {
        $editorLabel = 'Find os (Google Maps)';
    }
    $inputNamePrefix = $inputNamePrefix ?? "areas[{$area->id}]";
    $oldPrefix = $oldPrefix ?? "areas.{$area->id}";
    $designerTheme = $site->theme ?? 'base';
    $themeFieldView = "cms.pages.sites.designer.{$designerTheme}.fields.{$area->area_type}";
    $sharedFieldView = "cms.pages.sites.designer.shared.fields.{$area->area_type}";
    $legacyFieldView = "cms.pages.sites.partials.fields.{$area->area_type}";
    $resolvedFieldView = view()->exists($themeFieldView)
        ? $themeFieldView
        : (view()->exists($sharedFieldView) ? $sharedFieldView : (view()->exists($legacyFieldView) ? $legacyFieldView : null));
@endphp

<article class="site-section-editor">
    <div class="site-section-editor__header">
        <div class="site-section-editor__heading">
            <p class="site-section-editor__eyebrow">Sideafsnit</p>
            <h4 class="site-section-editor__title">{{ $editorLabel }}</h4>
        </div>

        <div class="site-section-editor__header-meta">
            <span class="dashboard-feed__meta">{{ old("{$oldPrefix}.is_active", $area->is_active) ? 'aktiv' : 'skjult' }}</span>

            <div class="site-section-editor__toggle site-section-editor__toggle--header">
                <input type="hidden" name="{{ $inputNamePrefix }}[is_active]" value="0">
                <label class="site-section-editor__checkbox">
                    <input type="checkbox" name="{{ $inputNamePrefix }}[is_active]" value="1" {{ old("{$oldPrefix}.is_active", $area->is_active) ? 'checked' : '' }}>
                    <span>Vis dette afsnit på siden</span>
                </label>
            </div>
        </div>
    </div>

    <fieldset @disabled(! $canUpdateSite)>

        @includeWhen($resolvedFieldView !== null, $resolvedFieldView, [
            'site' => $site,
            'area' => $area,
            'data' => $data,
            'inputNamePrefix' => $inputNamePrefix,
            'oldPrefix' => $oldPrefix,
        ])

        @includeWhen($resolvedFieldView === null, 'cms.pages.sites.partials.fields.fallback', [
            'site' => $site,
            'area' => $area,
            'data' => $data,
            'inputNamePrefix' => $inputNamePrefix,
            'oldPrefix' => $oldPrefix,
        ])
    </fieldset>
</article>
