<x-app-layout>
    <x-slot name="header">
        @php
            $headerLivePage = $page->sourcePage;
            $headerPreviewUrl = $headerLivePage
                ? ($headerLivePage->is_home ? route('sites.show', $site) : route('sites.page', [$site, $headerLivePage->slug]))
                : route('sites.show', $site);
        @endphp

        <div class="site-editor-toolbar site-editor-toolbar--compact">
            <div class="site-editor-toolbar__content">
                <h2 class="site-editor-toolbar__title">Designer</h2>
            </div>

            <div class="site-editor-toolbar__actions">
                <a href="{{ route('cms.sites.show', ['site' => $site, 'page' => $page->id]) }}" class="ui-button ui-button--outline">
                    Tilbage til website-dashboard
                </a>

                @if (auth()->user()?->isDeveloper())
                    <a href="{{ route('cms.pages.custom-code.show', [$site, $page]) }}" class="ui-button ui-button--outline">
                        Custom kode
                    </a>
                @endif

                <a href="{{ $headerPreviewUrl }}" class="ui-button ui-button--ink" target="_blank" rel="noreferrer">
                    Se preview
                </a>

                @if ($canUpdateSite)
                    <button
                        type="submit"
                        form="site-page-draft-form"
                        name="publish_after_save"
                        value="1"
                        class="ui-button ui-button--success"
                    >
                        Offentliggør ændringer
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="site-editor-page">
        <div class="ui-shell">
            @php
                $updateErrors = $errors->getBag('updatePage');
                $livePage = $page->sourcePage;
                $livePreviewUrl = $livePage
                    ? ($livePage->is_home ? route('sites.show', $site) : route('sites.page', [$site, $livePage->slug]))
                    : null;
                $draftPreviewUrl = route('cms.pages.preview', [$site, $page]);
                $areaIds = $page->areas->pluck('id')->map(fn ($id) => (string) $id)->values();
                $initialActiveArea = (string) old('active_area', (string) ($page->areas->first()?->id ?? ''));
                $createSectionErrors = $errors->getBag('createSection');
            @endphp

            <form id="site-page-draft-form" method="POST" action="{{ route('cms.pages.update', [$site, $page]) }}" class="site-page-draft-form" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="return_to" value="design">
                <input type="hidden" name="name" value="{{ old('name', $page->name) }}">
                <input type="hidden" name="slug" value="{{ old('slug', $page->slug) }}">
                <input type="hidden" name="title" value="{{ old('title', $page->title) }}">
                <input type="hidden" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}">
                <input type="hidden" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}">
                <input type="hidden" name="is_published" value="{{ old('is_published', $page->is_published ? 1 : 0) }}">
                <input type="hidden" name="is_home" value="{{ old('is_home', $page->is_home ? 1 : 0) }}">
                @if ($updateErrors->any())
                    <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                        <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                        <ul class="ui-list">
                            @foreach ($updateErrors->all() as $error)
                                <li class="ui-list__item">
                                    <span class="ui-list__dot"></span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section
                    class="ui-card site-editor-main-card"
                    x-data="sitePageEditor({
                        activeArea: @js($initialActiveArea),
                        areaIds: @js($areaIds),
                        canReorder: @js($canUpdateSite),
                        initialPanelModal: @js($createSectionErrors->any() ? 'library' : ''),
                        initialLibraryCategory: @js(array_key_first($availableSectionCategories ?? []) ?: ''),
                        autosaveEnabled: @js($canUpdateSite),
                        autosaveUrl: @js(route('cms.pages.update', [$site, $page])),
                        previewUrl: @js($draftPreviewUrl),
                        previewPageMap: @js($previewEditorPageMap ?? []),
                    })"
                >
                    <div
                        x-ref="draftForm"
                        x-on:input="scheduleAutosave($event)"
                        x-on:change="scheduleAutosave($event)"
                        x-on:submit="prepareManualSubmit()"
                    >
                    <input type="hidden" name="active_area" x-model="activeArea">

                    <div class="site-editor-builder">
                        <section class="site-editor-builder__preview">
                            <article class="site-theme-preview-card site-editor-live-preview">
                                <div class="site-theme-preview-card__frame site-editor-live-preview__frame">
                                    <div class="site-theme-preview-card__header">
                                        <div>
                                            <span class="site-dashboard-panel__detail-label">Kladdepreview</span>
                                            <h4 class="site-editor-live-preview__title">Se siden mens du bygger</h4>
                                            <p class="site-editor-live-preview__copy">Previewet viser den senest gemte kladde af siden.</p>
                                        </div>

                                        <div class="site-editor-live-preview__actions">
                                            <span
                                                class="site-editor-live-preview__status"
                                                x-bind:class="{
                                                    'site-editor-live-preview__status--pending': autosaveState === 'pending',
                                                    'site-editor-live-preview__status--saving': autosaveState === 'saving',
                                                    'site-editor-live-preview__status--saved': autosaveState === 'saved',
                                                    'site-editor-live-preview__status--error': autosaveState === 'error'
                                                }"
                                                x-text="autosaveMessage"
                                            ></span>

                                            @if ($canUpdateSite)
                                                <button type="submit" class="site-editor-live-preview__refresh">
                                                    Gem kladde
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="site-theme-preview-card__viewport site-editor-live-preview__viewport">
                                        <iframe
                                            x-ref="previewFrame"
                                            title="Live preview af siden"
                                            class="site-theme-preview-card__iframe site-editor-live-preview__iframe"
                                            src="{{ $draftPreviewUrl }}"
                                            x-on:load="handlePreviewLoad()"
                                        ></iframe>
                                    </div>
                                </div>
                            </article>
                        </section>

                        <aside class="site-editor-builder__properties">
                            <section class="site-editor-panel-nav site-editor-builder__properties-header">
                                <div class="site-editor-panel-nav__header">
                                    <div>
                                        <p class="site-editor-panel-nav__eyebrow">Egenskaber</p>
                                        <h4 class="site-editor-panel-nav__title">Rediger aktiv sektion</h4>
                                        <p class="site-editor-panel-nav__copy">Brug felterne her til at forme indhold og layout for det valgte modul.</p>
                                    </div>
                                </div>

                                <div class="site-editor-builder__property-actions">
                                    <button type="button" class="site-editor-builder__property-button" x-on:click="openPanelModal('sections')">
                                        Sideafsnit
                                        <span x-text="areaIds.length">{{ $page->areas->count() }}</span>
                                    </button>

                                    <button type="button" class="site-editor-builder__property-button" x-on:click="openPanelModal('library')">
                                        Modulbibliotek
                                    </button>
                                </div>
                            </section>

                                <div class="site-editor-sections">
                                    @forelse ($page->areas as $area)
                                        <div x-cloak x-show="activeArea === '{{ $area->id }}' && areaIds.includes('{{ $area->id }}')" x-transition.opacity.duration.150ms>
                                            @include('cms.pages.sites.partials.area-form', [
                                                'site' => $site,
                                                'page' => $page,
                                                'area' => $area,
                                                'canUpdateSite' => $canUpdateSite,
                                            'inputNamePrefix' => "areas[{$area->id}]",
                                            'oldPrefix' => "areas.{$area->id}",
                                            ])
                                        </div>
                                    @empty
                                    @endforelse

                                    <article class="site-editor-empty" x-show="areaIds.length === 0" x-cloak>
                                        <p class="ui-copy">Denne side har ingen afsnit endnu.</p>
                                    </article>
                                </div>
                            </aside>
                    </div>

                    @if ($canUpdateSite)
                        <form
                            x-ref="sectionReorderForm"
                            method="POST"
                            action="{{ route('cms.pages.sections.reorder', [$site, $page]) }}"
                            class="site-editor-panel-nav__reorder-form"
                        >
                            @csrf
                            @method('PATCH')
                            <input x-ref="sectionReorderFocus" type="hidden" name="focus_section_id" value="">
                            <div x-ref="sectionReorderInputs"></div>
                        </form>
                    @endif

                    <div
                        class="site-editor-modal"
                        x-cloak
                        x-show="panelModal"
                        x-transition.opacity.duration.180ms
                        x-on:keydown.escape.window="closePanelModal()"
                    >
                        <div class="site-editor-modal__backdrop" x-on:click="closePanelModal()"></div>

                        <div class="site-editor-modal__dialog" x-on:click.stop>
                            <section class="site-editor-panel-nav site-editor-modal__panel" x-show="panelModal === 'sections'" x-cloak>
                                <div class="site-editor-panel-nav__header">
                                    <div>
                                        <p class="site-editor-panel-nav__eyebrow">Sideafsnit</p>
                                        <h4 class="site-editor-panel-nav__title">Sektioner på siden</h4>
                                        <p class="site-editor-panel-nav__copy">Vælg hvilket afsnit du vil redigere, og træk dem for at ændre rækkefølgen.</p>
                                    </div>

                                    <button type="button" class="site-editor-modal__close" x-on:click="closePanelModal()">Luk</button>
                                </div>

                                <div class="site-editor-panel-nav__links site-editor-panel-nav__links--sections" x-ref="sectionList">
                                    @forelse ($page->areas as $index => $area)
                                        @php
                                            $navLabel = \App\Support\Sites\SitePageAreaBlueprints::displayLabel($area->area_type, $area->label, $area->area_key);
                                            if (($site->theme ?? 'base') === 'base' && $area->area_type === 'contact') {
                                                $navLabel = 'Find os (Google Maps)';
                                            }
                                        @endphp

                                        <article
                                            class="site-editor-panel-nav__section-card{{ $canUpdateSite ? ' site-editor-panel-nav__section-card--draggable' : '' }}"
                                            x-show="areaIds.includes('{{ $area->id }}')"
                                            x-bind:data-section-id="areaIds.includes('{{ $area->id }}') ? '{{ $area->id }}' : null"
                                            draggable="{{ $canUpdateSite ? 'true' : 'false' }}"
                                            x-bind:class="{
                                                'site-editor-panel-nav__section-card--active': activeArea === '{{ $area->id }}',
                                                'site-editor-panel-nav__section-card--dragging': draggingArea === '{{ $area->id }}',
                                                'site-editor-panel-nav__section-card--drop-target': dropTargetArea === '{{ $area->id }}' && draggingArea !== '{{ $area->id }}'
                                            }"
                                            x-on:dragstart="startDragging($event, '{{ $area->id }}')"
                                            x-on:dragover.prevent="handleDragOver($event)"
                                            x-on:dragenter.prevent
                                            x-on:drop.prevent
                                            x-on:dragend="finishDragging()"
                                        >
                                            <button
                                                type="button"
                                                class="site-editor-panel-nav__button"
                                                x-bind:class="{ 'site-editor-panel-nav__button--active': activeArea === '{{ $area->id }}' }"
                                                x-on:click="activeArea = '{{ $area->id }}'; closePanelModal()"
                                            >
                                                <span class="site-editor-panel-nav__button-index">
                                                    @if ($canUpdateSite)
                                                        <span class="site-editor-panel-nav__drag-grip" aria-hidden="true">::</span>
                                                    @else
                                                        {{ $index + 1 }}
                                                    @endif
                                                </span>

                                                <span class="site-editor-panel-nav__button-body">
                                                    <span class="site-editor-panel-nav__button-title">{{ $navLabel }}</span>
                                                    <span class="site-editor-panel-nav__button-copy">{{ $area->is_active ? 'Vises på siden' : 'Skjult på siden' }}</span>
                                                </span>

                                                <span class="site-editor-panel-nav__button-status{{ $area->is_active ? '' : ' site-editor-panel-nav__button-status--muted' }}">
                                                    {{ $area->is_active ? 'Aktiv' : 'Skjult' }}
                                                </span>
                                            </button>

                                            @if ($canUpdateSite)
                                                <div class="site-editor-panel-nav__section-actions">
                                                    <form method="POST" action="{{ route('cms.pages.sections.visibility', [$site, $page, $area]) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="is_active" value="{{ $area->is_active ? 0 : 1 }}">
                                                        <button type="submit" class="site-editor-panel-nav__action-button">
                                                            {{ $area->is_active ? 'Skjul' : 'Vis' }}
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('cms.pages.sections.destroy', [$site, $page, $area]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="button"
                                                            class="site-editor-panel-nav__action-button site-editor-panel-nav__action-button--danger"
                                                            x-on:click="deleteSection('{{ $area->id }}', '{{ route('cms.pages.sections.destroy', [$site, $page, $area]) }}', '{{ addslashes($navLabel) }}')"
                                                        >
                                                            Slet
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </article>
                                    @empty
                                    @endforelse

                                    <article class="site-editor-empty" x-show="areaIds.length === 0" x-cloak>
                                        <p class="ui-copy">Denne side har ingen afsnit endnu.</p>
                                    </article>
                                </div>
                            </section>

                            <section class="site-editor-panel-nav site-editor-module-library site-editor-modal__panel" x-show="panelModal === 'library'" x-cloak>
                                <div class="site-editor-panel-nav__header">
                                    <div>
                                        <p class="site-editor-panel-nav__eyebrow">Modulbibliotek</p>
                                        <h4 class="site-editor-panel-nav__title">Tilføj nye sektioner</h4>
                                        <p class="site-editor-panel-nav__copy">Vælg et modul og læg det direkte ind på siden.</p>
                                    </div>

                                    <button type="button" class="site-editor-modal__close" x-on:click="closePanelModal()">Luk</button>
                                </div>

                                @if ($createSectionErrors->any())
                                    <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                                        <p class="ui-copy">Der er lige et modulfelt vi skal have rettet:</p>
                                        <ul class="ui-list">
                                            @foreach ($createSectionErrors->all() as $error)
                                                <li class="ui-list__item">
                                                    <span class="ui-list__dot"></span>
                                                    <span>{{ $error }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="site-editor-module-library__workspace">
                                    <aside class="site-editor-module-library__categories">
                                        <div class="site-editor-module-library__categories-list">
                                            @foreach (($availableSectionCategories ?? []) as $categoryKey => $category)
                                                <button
                                                    type="button"
                                                    class="site-editor-module-library__category-button"
                                                    x-bind:class="{ 'site-editor-module-library__category-button--active': activeLibraryCategory === '{{ $categoryKey }}' }"
                                                    x-on:click="activeLibraryCategory = '{{ $categoryKey }}'"
                                                >
                                                    <span class="site-editor-module-library__category-label">{{ $category['label'] }}</span>
                                                    <span class="site-editor-module-library__category-count">{{ count($category['modules']) }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </aside>

                                    <div class="site-editor-module-library__groups">
                                        @foreach (($availableSectionCategories ?? []) as $categoryKey => $category)
                                            <section class="site-editor-module-library__group" x-show="activeLibraryCategory === '{{ $categoryKey }}'" x-cloak>
                                                <header class="site-editor-module-library__group-header">
                                                    <h5 class="site-editor-module-library__group-title">{{ $category['label'] }}</h5>
                                                </header>

                                                <div class="site-editor-module-library__items">
                                                    @foreach ($category['modules'] as $module)
                                                        @php
                                                            $moduleLabel = $module['label'];
                                                            $moduleDescription = $module['description'];

                                                            if (($site->theme ?? 'base') === 'base' && $module['type'] === 'contact') {
                                                                $moduleLabel = 'Find os (Google Maps)';
                                                                $moduleDescription = 'Tekst i venstre side og Google Maps i højre side.';
                                                            }
                                                        @endphp

                                                        <form method="POST" action="{{ route('cms.pages.sections.store', [$site, $page]) }}">
                                                            @csrf
                                                            <input type="hidden" name="area_type" value="{{ $module['type'] }}">

                                                            <button type="submit" class="site-editor-module-library__item" @disabled(! $canUpdateSite)>
                                                                <span class="site-editor-module-library__item-body">
                                                                    <strong>{{ $moduleLabel }}</strong>
                                                                    <small>{{ $moduleDescription }}</small>
                                                                </span>

                                                                <span class="site-editor-module-library__item-add">Tilføj</span>
                                                            </button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            </section>
                                        @endforeach
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    @unless ($canUpdateSite)
                        <div class="site-page-draft-form__actions">
                            <p class="ui-copy">Denne tenant-rolle giver kun læseadgang til sideindholdet.</p>
                        </div>
                    @endunless
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
