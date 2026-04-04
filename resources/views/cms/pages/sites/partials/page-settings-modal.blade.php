<x-modal name="{{ $modalName }}" :show="$errors->getBag('updatePage')->isNotEmpty()" maxWidth="2xl" focusable>
    <div class="site-page-settings-modal">
        <div class="site-page-settings-modal__header">
            <div>
                <p class="site-editor-main-card__eyebrow">Sideopsaetning</p>
                <h3 class="site-editor-main-card__title">{{ $page->name }}</h3>
                <p class="site-editor-main-card__copy">
                    Her styre du sidenavn, slug og metadata for den valgte side.
                </p>
            </div>

            <button
                type="button"
                class="ui-button ui-button--outline site-page-settings-modal__close"
                x-on:click="$dispatch('close-modal', '{{ $modalName }}')"
            >
                Luk
            </button>
        </div>

        @php($updateErrors = $errors->getBag('updatePage'))
        @php($deletePageFormId = "delete-site-page-modal-{$page->id}")

        <form id="{{ $deletePageFormId }}" method="POST" action="{{ route('cms.pages.destroy', [$site, $page]) }}" onsubmit="return confirm('Er du sikker paa, at du vil slette denne side?');">
            @csrf
            @method('DELETE')
        </form>

        <form method="POST" action="{{ route('cms.pages.update', [$site, $page]) }}" class="site-page-draft-form">
            @csrf
            @method('PATCH')
            <input type="hidden" name="return_to" value="dashboard">

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

            <fieldset @disabled(! $canUpdateSite)>
                @include('cms.pages.sites.partials.page-settings-fields', ['page' => $page])
            </fieldset>

            <div class="site-page-settings-modal__actions">
                @if ($canUpdateSite)
                    <div class="site-page-settings-modal__submit">
                        <button type="submit" class="ui-button ui-button--ink">
                            Gem aendringer i kladde
                        </button>

                        <button type="submit" form="{{ $deletePageFormId }}" class="ui-button ui-button--danger">
                            Slet side
                        </button>
                    </div>
                @else
                    <p class="ui-copy">Denne tenant-rolle giver kun laeseadgang til sideindholdet.</p>
                @endif
            </div>
        </form>
    </div>
</x-modal>
