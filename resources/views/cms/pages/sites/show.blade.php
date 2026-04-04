<x-app-layout>
    <x-slot name="header">
        @include('cms.pages.sites.partials.site-summary', [
            'site' => $site,
            'canUpdateSite' => $canUpdateSite,
            'publishRedirectTo' => url()->current(),
            'showBackButton' => false,
            'showPreviewButton' => false,
            'showPublishButton' => false,
        ])
    </x-slot>

    <div class="site-editor-page">
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $nextSortOrder = ($sitePages->max('sort_order') ?? 0) + 1;
                $createPageModalName = "create-site-page-{$site->id}";
                $selectedPage = $activePage;
                $pageSettingsModalName = $selectedPage ? "site-page-settings-{$selectedPage->id}" : null;
                $selectedLivePage = $selectedPage?->sourcePage;
                $selectedPreviewUrl = $selectedLivePage
                    ? ($selectedLivePage->is_home ? route('sites.show', $site) : route('sites.page', [$site, $selectedLivePage->slug]))
                    : null;
            @endphp

            @include('cms.pages.sites.partials.create-page-modal', [
                'site' => $site,
                'modalName' => $createPageModalName,
                'sortOrder' => $nextSortOrder,
                'availablePageTemplates' => $availablePageTemplates,
                'canUpdateSite' => $canUpdateSite,
            ])

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
                        'modalName' => $createPageModalName,
                        'pageSettingsModalName' => $pageSettingsModalName,
                    ])
                </aside>

                <div class="site-dashboard-workspace__main">
                    <section class="ui-card site-dashboard-panel">
                        <div class="site-dashboard-panel__header">
                            <div>
                                <p class="site-dashboard-panel__eyebrow">Globalt website indhold</p>
                                <h3 class="site-dashboard-panel__title">Websitekonfiguration</h3>
                            </div>

                            <div class="site-dashboard-panel__header-actions">
                                <a href="{{ route('sites.show', $site) }}" class="ui-button ui-button--ink">Se preview</a>

                                @if ($canUpdateSite)
                                    <form method="POST" action="{{ route('cms.sites.publish', $site) }}">
                                        @csrf
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <button type="submit" class="ui-button ui-button--success">
                                            OFFENTLIGGOER
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="site-dashboard-global-grid">
                            @foreach (($globalSections ?? []) as $sectionKey => $definition)
                                <a href="{{ route('cms.sites.global.section', [$site, $sectionKey]) }}" class="site-dashboard-global-card">
                                    <span class="site-dashboard-global-card__title">
                                        <span>{{ $definition['label'] }}</span>

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
            </div>
        </div>
    </div>
</x-app-layout>
