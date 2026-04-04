<x-app-layout>
    <div class="site-editor-page">
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            @include("cms.pages.sites.partials.global.{$activeGlobalSectionDefinition['partial']}", [
                'site' => $site,
                'canUpdateSite' => $canUpdateSite,
                'availableThemes' => $availableThemes ?? [],
                'availableColorPalettes' => $availableColorPalettes ?? [],
            ])
        </div>
    </div>
</x-app-layout>
