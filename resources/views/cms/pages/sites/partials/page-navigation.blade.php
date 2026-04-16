<section class="ui-card site-editor-page-jump">
    <div class="site-editor-page-jump__header">
        <div>
            <p class="site-editor-page-jump__eyebrow">REDIGER SIDER</p>
        </div>

        <div class="site-editor-page-jump__header-actions">
            <span class="dashboard-feed__meta">{{ $sitePages->count() }} sider</span>

            @if (! empty($canUpdateSite) && ! empty($canManageSiteContent))
                <button
                    type="button"
                    class="ui-button site-editor-page-jump__add"
                    x-data=""
                    x-on:click="$dispatch('open-modal', '{{ $modalName }}')"
                    aria-label="Opret ny side"
                    title="Opret ny side"
                >
                    <span aria-hidden="true">+</span>
                </button>
            @endif
        </div>
    </div>

    <div class="site-editor-page-jump__links">
        @if (empty($canManageSiteContent))
            <p class="ui-copy">Den valgte pakke inkluderer ikke sidebygger og indholdsredigering i CMS'et.</p>
        @else
            @forelse ($sitePages as $navPage)
                @php($isActive = isset($activePage) && $activePage?->is($navPage))

                <article class="site-editor-page-jump__item{{ $isActive ? ' site-editor-page-jump__item--active' : '' }}">
                    <a
                        href="{{ route('cms.sites.show', ['site' => $site, 'page' => $navPage->id]) }}"
                        class="site-editor-page-jump__overlay"
                        aria-label="Vaelg siden {{ $navPage->name }}"
                    ></a>

                    <div class="site-editor-page-jump__item-main">
                        <div class="site-editor-page-jump__link{{ $isActive ? ' site-editor-page-jump__link--active' : '' }}">
                            <span class="site-editor-page-jump__link-title">{{ $navPage->name }}</span>
                            <small>{{ $navPage->is_home ? 'Forside' : '/' . $navPage->slug }}</small>
                        </div>

                        @if ($isActive)
                            <div class="site-editor-page-jump__item-actions">
                                <a href="{{ route('cms.pages.show', [$site, $navPage]) }}" class="site-editor-page-jump__action site-editor-page-jump__action--primary">
                                    @if (! empty($canUpdateSite))
                                        Designer
                                    @else
                                        Se side
                                    @endif
                                </a>

                                @if (! empty($canUseCustomCode))
                                    <a href="{{ route('cms.pages.custom-code.show', [$site, $navPage]) }}" class="site-editor-page-jump__action">
                                        Custom kode
                                    </a>
                                @endif

                                <button
                                    type="button"
                                    class="site-editor-page-jump__action"
                                    x-data=""
                                    x-on:click="$dispatch('open-modal', '{{ $pageSettingsModalName }}')"
                                >
                                    Sideopsaetning
                                </button>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <p class="ui-copy">Der er ingen sider endnu. Brug + knappen for at oprette den foerste side.</p>
            @endforelse
        @endif
    </div>
</section>
