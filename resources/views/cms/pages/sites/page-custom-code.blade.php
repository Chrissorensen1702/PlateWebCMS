<x-app-layout>
    <x-slot name="header">
        <div class="site-editor-toolbar site-editor-toolbar--compact">
            <div class="site-editor-toolbar__content">
                <h2 class="site-editor-toolbar__title">Custom kode</h2>
            </div>

            <div class="site-editor-toolbar__actions">
                <a href="{{ route('cms.sites.show', ['site' => $site, 'page' => $page->id]) }}" class="ui-button ui-button--outline">
                    Tilbage til website-dashboard
                </a>

                <a href="{{ route('cms.pages.show', [$site, $page]) }}" class="ui-button ui-button--outline">
                    Åbn designer
                </a>

                <a href="{{ $page->sourcePage ? ($page->sourcePage->is_home ? route('sites.show', $site) : route('sites.page', [$site, $page->sourcePage->slug])) : route('sites.show', $site) }}" class="ui-button ui-button--ink" target="_blank" rel="noreferrer">
                    Se preview
                </a>

                @if ($canUpdateSite)
                    <button
                        type="submit"
                        form="site-page-custom-code-form"
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
            <form id="site-page-custom-code-form" method="POST" action="{{ route('cms.pages.update', [$site, $page]) }}" class="site-page-draft-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="return_to" value="custom-code">
                <input type="hidden" name="name" value="{{ old('name', $page->name) }}">
                <input type="hidden" name="slug" value="{{ old('slug', $page->slug) }}">
                <input type="hidden" name="title" value="{{ old('title', $page->title) }}">
                <input type="hidden" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}">
                <input type="hidden" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}">
                <input type="hidden" name="is_published" value="{{ old('is_published', $page->is_published ? 1 : 0) }}">
                <input type="hidden" name="is_home" value="{{ old('is_home', $page->is_home ? 1 : 0) }}">

                @if ($errors->getBag('updatePage')->any())
                    <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                        <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                        <ul class="ui-list">
                            @foreach ($errors->getBag('updatePage')->all() as $error)
                                <li class="ui-list__item">
                                    <span class="ui-list__dot"></span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="site-editor-builder site-editor-builder--custom-code">
                    <section class="site-editor-builder__preview">
                        <article class="site-theme-preview-card site-editor-live-preview">
                            <div class="site-theme-preview-card__frame site-editor-live-preview__frame">
                                <div class="site-theme-preview-card__header">
                                    <div>
                                        <span class="site-dashboard-panel__detail-label">Kladdepreview</span>
                                        <h4 class="site-editor-live-preview__title">Se custom layoutet på siden</h4>
                                        <p class="site-editor-live-preview__copy">Previewet viser den senest gemte kladde. Når custom layout er aktivt, renderer siden din HTML og CSS direkte.</p>
                                    </div>

                                    <div class="site-editor-live-preview__actions">
                                        @if ($canUpdateSite)
                                            <button type="submit" class="site-editor-live-preview__refresh">
                                                Gem kladde
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="site-theme-preview-card__viewport site-editor-live-preview__viewport">
                                    <iframe
                                        title="Live preview af custom kode"
                                        class="site-theme-preview-card__iframe site-editor-live-preview__iframe"
                                        src="{{ route('cms.pages.preview', [$site, $page]) }}"
                                    ></iframe>
                                </div>
                            </div>
                        </article>
                    </section>

                    <aside class="site-editor-builder__properties">
                        <section class="ui-card site-editor-main-card site-custom-code-card">
                            <div class="site-editor-main-card__header">
                                <div>
                                    <p class="site-editor-main-card__eyebrow">Developer værktøj</p>
                                    <h3 class="site-editor-main-card__title">Fri HTML og CSS på {{ $page->name }}</h3>
                                    <p class="site-editor-main-card__copy">
                                        Brug custom mode når vi skal bygge noget helt særligt for kunden uden at lave et nyt theme først. Det her er kun til developer-flowet.
                                    </p>
                                </div>
                            </div>

                            <fieldset @disabled(! $canUpdateSite) class="site-custom-code-card__fields">
                                <label class="ui-field">
                                    <span class="ui-field__label">Layoutmode</span>
                                    <select name="layout_mode" class="ui-field__control">
                                        @php($currentLayoutMode = \App\Support\Sites\SitePageLayoutModes::normalize(old('layout_mode', $page->layout_mode ?? \App\Support\Sites\SitePageLayoutModes::STRUCTURED)))
                                        <option value="structured" @selected($currentLayoutMode === \App\Support\Sites\SitePageLayoutModes::STRUCTURED)>Brug almindelige sektioner</option>
                                        <option value="custom-main" @selected($currentLayoutMode === \App\Support\Sites\SitePageLayoutModes::CUSTOM_MAIN)>Overtag kun main-indholdet</option>
                                        <option value="custom-full" @selected($currentLayoutMode === \App\Support\Sites\SitePageLayoutModes::CUSTOM_FULL)>Overtag hele siden med custom kode</option>
                                    </select>
                                </label>

                                <div class="site-custom-code-card__notice">
                                    <p class="site-custom-code-card__notice-title">Sådan virker det</p>
                                    <p class="ui-copy">
                                        Overtag kun main-indholdet beholder den faste header og footer. Overtag hele siden renderer kun din egen HTML og CSS. Hvis du skifter tilbage til almindelige sektioner, bliver din custom kode gemt som kladde men ikke vist på siden.
                                    </p>
                                </div>

                                <label class="ui-field site-custom-code-card__field">
                                    <span class="ui-field__label">HTML</span>
                                    <textarea
                                        name="custom_html"
                                        rows="18"
                                        class="ui-field__control site-custom-code-card__textarea"
                                        spellcheck="false"
                                        placeholder="<section class=&quot;custom-hero&quot;>..."
                                    >{{ old('custom_html', $page->custom_html) }}</textarea>
                                </label>

                                <label class="ui-field site-custom-code-card__field">
                                    <span class="ui-field__label">CSS</span>
                                    <textarea
                                        name="custom_css"
                                        rows="16"
                                        class="ui-field__control site-custom-code-card__textarea"
                                        spellcheck="false"
                                        placeholder=".custom-hero {\n  padding: 6rem 0;\n}"
                                    >{{ old('custom_css', $page->custom_css) }}</textarea>
                                </label>
                            </fieldset>

                            <div class="site-page-draft-form__actions">
                                @if ($canUpdateSite)
                                    <button type="submit" class="ui-button ui-button--ink">
                                        Gem custom kode i kladde
                                    </button>
                                @else
                                    <p class="ui-copy">Din developer-konto har kun læseadgang til denne side.</p>
                                @endif
                            </div>
                        </section>
                    </aside>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
