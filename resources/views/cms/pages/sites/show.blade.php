<x-app-layout>
    <x-slot name="header">
        @include('cms.pages.sites.partials.site-summary', [
            'site' => $site,
            'canUpdateSite' => $canUpdateSite,
            'publishRedirectTo' => url()->current(),
            'showBackButton' => false,
            'showPreviewButton' => true,
            'showPublishButton' => true,
            'summaryVariant' => 'dashboard',
        ])
    </x-slot>

    <div class="site-editor-page">
        <div class="ui-shell">
            @php
                $nextSortOrder = ($sitePages->max('sort_order') ?? 0) + 1;
                $createPageModalName = "create-site-page-{$site->id}";
                $selectedPage = $canManageSiteContent ? $activePage : null;
                $pageSettingsModalName = $selectedPage ? "site-page-settings-{$selectedPage->id}" : null;
                $selectedLivePage = $selectedPage?->sourcePage;
                $selectedPreviewUrl = $selectedLivePage
                    ? ($selectedLivePage->is_home ? route('sites.show', $site) : route('sites.page', [$site, $selectedLivePage->slug]))
                    : null;
                $publishedPageCount = $sitePages->where('is_published', true)->count();
                $draftPageCount = $sitePages->count() - $publishedPageCount;
                $primaryDomain = $site->primary_domain;
                $styleSectionKeys = collect(['header', 'footer', 'colors', 'theme'])
                    ->filter(fn ($sectionKey) => isset($globalSections[$sectionKey]))
                    ->values();
                $integrationVisibilitySectionKeys = collect(['booking', 'seo', 'newsletter'])
                    ->filter(fn ($sectionKey) => isset($globalSections[$sectionKey]))
                    ->values();
                $dashboardSectionLabels = [
                    'theme' => 'Theme',
                    'seo' => 'SEO og meta',
                ];
            @endphp

            @if ($canManageSiteContent)
                @include('cms.pages.sites.partials.create-page-modal', [
                    'site' => $site,
                    'modalName' => $createPageModalName,
                    'sortOrder' => $nextSortOrder,
                    'availablePageTemplates' => $availablePageTemplates,
                    'canUpdateSite' => $canUpdateSite,
                ])
            @endif

            @if ($selectedPage && $pageSettingsModalName)
                @include('cms.pages.sites.partials.page-settings-modal', [
                    'site' => $site,
                    'page' => $selectedPage,
                    'modalName' => $pageSettingsModalName,
                    'canUpdateSite' => $canUpdateSite,
                    'livePreviewUrl' => $selectedPreviewUrl,
                ])
            @endif

            <div class="site-dashboard-workspace">
                <aside class="site-dashboard-workspace__sidebar">
                    @include('cms.pages.sites.partials.page-navigation', [
                        'site' => $site,
                        'sitePages' => $sitePages,
                        'activePage' => $selectedPage,
                        'canUpdateSite' => $canUpdateSite,
                        'canManageSiteContent' => $canManageSiteContent,
                        'canUseCustomCode' => $canUseCustomCode,
                        'modalName' => $createPageModalName,
                        'pageSettingsModalName' => $pageSettingsModalName,
                    ])
                </aside>

                <div class="site-dashboard-workspace__main">
                    <section class="ui-card site-dashboard-panel site-dashboard-panel--workspace">
                        <div class="site-dashboard-panel__header">
                            <div>
                                <p class="site-dashboard-panel__eyebrow">Globalt website indhold</p>
                                <h3 class="site-dashboard-panel__title">Websitekonfiguration</h3>
                                <p class="site-dashboard-panel__copy">
                                    Alle globale indstillinger samt opsaetning for websitet samlet et sted.
                                </p>
                            </div>

                            <div class="site-dashboard-panel__header-actions site-dashboard-panel__header-actions--meta">
                                <div class="site-dashboard-hero__chips">
                                <span class="site-dashboard-hero__chip">{{ $sitePages->count() }} sider</span>
                                <span class="site-dashboard-hero__chip">{{ $publishedPageCount }} publicerede</span>
                                <span class="site-dashboard-hero__chip">{{ $draftPageCount }} kladder</span>
                                <span class="site-dashboard-hero__chip">Theme: {{ $site->theme }}</span>

                                @if ($primaryDomain)
                                    <span class="site-dashboard-hero__chip">{{ $primaryDomain }}</span>
                                @endif
                                </div>
                            </div>
                        </div>

                        <div class="site-dashboard-global-groups">
                            <section class="site-dashboard-global-group">
                                <div class="site-dashboard-global-group__header">
                                    <p class="site-dashboard-global-group__eyebrow">Style</p>
                                </div>

                                <div class="site-dashboard-global-group__grid">
                                    @foreach ($styleSectionKeys as $sectionKey)
                                        @php($definition = $globalSections[$sectionKey])

                                        <a
                                            href="{{ route('cms.sites.global.section', [$site, $sectionKey]) }}"
                                            class="site-dashboard-global-card"
                                        >
                                            <span class="site-dashboard-global-card__eyebrow">{{ $definition['eyebrow'] }}</span>
                                            <span class="site-dashboard-global-card__title">
                                                {{ $dashboardSectionLabels[$sectionKey] ?? $definition['label'] }}
                                            </span>
                                            <span class="site-dashboard-global-card__copy">{{ $definition['card_copy'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </section>

                            <section class="site-dashboard-global-group">
                                <div class="site-dashboard-global-group__header">
                                    <p class="site-dashboard-global-group__eyebrow">Integration og synlighed</p>
                                </div>

                                <div class="site-dashboard-global-group__grid">
                                    @foreach ($integrationVisibilitySectionKeys as $sectionKey)
                                        @php($definition = $globalSections[$sectionKey])

                                        <a
                                            href="{{ route('cms.sites.global.section', [$site, $sectionKey]) }}"
                                            class="site-dashboard-global-card"
                                        >
                                            <span class="site-dashboard-global-card__eyebrow">{{ $definition['eyebrow'] }}</span>
                                            <span class="site-dashboard-global-card__title">
                                                <span>{{ $dashboardSectionLabels[$sectionKey] ?? $definition['label'] }}</span>

                                                @if ($sectionKey === 'booking')
                                                    <span class="site-dashboard-global-card__wordmark" aria-label="PlateBook">
                                                        <span class="site-dashboard-global-card__wordmark-paren site-dashboard-global-card__wordmark-paren--plate">(</span>
                                                        <span class="site-dashboard-global-card__wordmark-plate">Plate</span><span class="site-dashboard-global-card__wordmark-book">Book</span>
                                                        <span class="site-dashboard-global-card__wordmark-paren site-dashboard-global-card__wordmark-paren--book">)</span>
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="site-dashboard-global-card__copy">{{ $definition['card_copy'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
