<x-app-layout>
    <div class="site-editor-page">
        <div class="ui-shell">
            @include("cms.pages.sites.partials.global.{$activeGlobalSectionDefinition['partial']}", [
                'site' => $site,
                'canUpdateSite' => $canUpdateSite,
                'availableThemes' => $availableThemes ?? [],
                'availableColorPalettes' => $availableColorPalettes ?? [],
            ])
        </div>
    </div>
</x-app-layout>
