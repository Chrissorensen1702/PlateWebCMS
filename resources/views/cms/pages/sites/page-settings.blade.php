<x-app-layout>
    <x-slot name="header">
        @include('cms.pages.sites.partials.site-summary', [
            'site' => $site,
            'canUpdateSite' => $canUpdateSite,
            'saveFormId' => 'site-page-settings-form',
            'publishRedirectTo' => url()->current(),
            'backHref' => route('cms.sites.show', ['site' => $site, 'page' => $page->id]),
            'backLabel' => 'Tilbage til site-dashboard',
        ])
    </x-slot>

    <div class="site-editor-page">
        <div class="ui-shell">
            @php
                $updateErrors = $errors->getBag('updatePage');
                $livePage = $page->sourcePage;
                $livePreviewUrl = $livePage
                    ? ($livePage->is_home ? route('sites.show', $site) : route('sites.page', [$site, $livePage->slug]))
                    : null;
                $deletePageFormId = "delete-site-page-{$page->id}";
            @endphp

            <form id="{{ $deletePageFormId }}" method="POST" action="{{ route('cms.pages.destroy', [$site, $page]) }}" onsubmit="return confirm('Er du sikker paa, at du vil slette denne side?');">
                @csrf
                @method('DELETE')
            </form>

            <form id="site-page-settings-form" method="POST" action="{{ route('cms.pages.update', [$site, $page]) }}" class="site-page-draft-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="return_to" value="settings">

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

                <section class="ui-card site-editor-main-card">
                    <div class="site-editor-main-card__header">
                        <div>
                            <p class="site-editor-main-card__eyebrow">Sideopsaetning</p>
                            <h3 class="site-editor-main-card__title">{{ $page->name }}</h3>
                            <p class="site-editor-main-card__copy">
                                Her styrer du sidenavn, slug, meta-beskrivelse og public-status uden at rode rundt i designeren.
                            </p>
                        </div>

                        <div class="site-editor-main-card__meta">
                            <a href="{{ route('cms.pages.show', [$site, $page]) }}" class="ui-button ui-button--outline">
                                Aaben designer
                            </a>

                            @if (auth()->user()?->isDeveloper())
                                <a href="{{ route('cms.pages.custom-code.show', [$site, $page]) }}" class="ui-button ui-button--outline">
                                    Custom kode
                                </a>
                            @endif

                            @if ($livePreviewUrl)
                                <a href="{{ $livePreviewUrl }}" class="ui-button ui-button--outline">
                                    Aaben live side
                                </a>
                            @endif
                        </div>
                    </div>

                    <fieldset @disabled(! $canUpdateSite)>
                        @include('cms.pages.sites.partials.page-settings-fields', ['page' => $page])
                    </fieldset>

                    <div class="site-page-draft-form__actions">
                        @if ($canUpdateSite)
                            <button type="submit" class="ui-button ui-button--ink">
                                Gem aendringer i kladde
                            </button>

                            <button type="submit" form="{{ $deletePageFormId }}" class="ui-button ui-button--danger">
                                Slet side
                            </button>
                        @else
                            <p class="ui-copy">Denne tenant-rolle giver kun laeseadgang til sideindholdet.</p>
                        @endif
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
