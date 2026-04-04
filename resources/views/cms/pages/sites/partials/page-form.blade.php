@php
    $formTarget = $page ? "page-{$page->id}" : 'create-page';
    $isTargetedForm = old('form_target') === $formTarget;
    $errorBag = $errors->getBag($page ? 'updatePage' : 'createPage');
    $formTitle = $page ? 'Sideindstillinger' : 'Ny side';
    $selectedPageTemplate = $page
        ? null
        : ($isTargetedForm ? old('page_template') : array_key_first($availablePageTemplates ?? []));
@endphp

<article class="site-page-form-card">
    <div class="site-page-form-card__header">
        <div>
            <p class="site-page-form-card__eyebrow">{{ $formTitle }}</p>
            <h4 class="site-page-form-card__title">
                {{ $page ? 'Opdater metadata, URL og public status for siden.' : 'Opret en ny færdig side-skabelon og rediger derefter kun dens indhold.' }}
            </h4>
        </div>

        @if ($page)
            <span class="dashboard-feed__meta">{{ $page->areas->count() }} afsnit på siden</span>
        @endif
    </div>

    <form method="POST" action="{{ $action }}" class="site-page-form-card__form">
        @csrf
        @isset($method)
            @method($method)
        @endisset

        <input type="hidden" name="form_target" value="{{ $formTarget }}">

        @if ($isTargetedForm && $errorBag->any())
            <div class="site-page-form-card__errors">
                <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                <ul class="ui-list">
                    @foreach ($errorBag->all() as $error)
                        <li class="ui-list__item">
                            <span class="ui-list__dot"></span>
                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <fieldset @disabled(! $canUpdateSite)>
            <div class="site-page-form-card__grid">
                <label class="ui-field">
                    <span class="ui-field__label">Navn</span>
                    <input
                        type="text"
                        name="name"
                        value="{{ $isTargetedForm ? old('name') : ($page?->name ?? '') }}"
                        class="ui-field__control"
                    >
                </label>

                <label class="ui-field">
                    <span class="ui-field__label">Slug / URL-del</span>
                    <input
                        type="text"
                        name="slug"
                        value="{{ $isTargetedForm ? old('slug') : ($page?->slug ?? '') }}"
                        class="ui-field__control"
                    >
                </label>

                <label class="ui-field site-page-form-card__field--full">
                    <span class="ui-field__label">Titel</span>
                    <input
                        type="text"
                        name="title"
                        value="{{ $isTargetedForm ? old('title') : ($page?->title ?? '') }}"
                        class="ui-field__control"
                    >
                </label>

                <label class="ui-field site-page-form-card__field--full">
                    <span class="ui-field__label">Meta-beskrivelse</span>
                    <textarea name="meta_description" class="ui-field__control ui-field__control--textarea">{{ $isTargetedForm ? old('meta_description') : ($page?->meta_description ?? '') }}</textarea>
                </label>

                <label class="ui-field">
                    <span class="ui-field__label">Sortering</span>
                    <input
                        type="number"
                        name="sort_order"
                        min="0"
                        max="999"
                        value="{{ $isTargetedForm ? old('sort_order') : ($page?->sort_order ?? $sortOrder) }}"
                        class="ui-field__control"
                    >
                </label>

                <div class="site-page-form-card__toggles">
                    <input type="hidden" name="is_published" value="0">
                    <label class="site-page-form-card__checkbox">
                        <input
                            type="checkbox"
                            name="is_published"
                            value="1"
                            {{ ($isTargetedForm ? old('is_published') : ($page?->is_published ?? true)) ? 'checked' : '' }}
                        >
                        <span>Siden er offentlig</span>
                    </label>

                    <input type="hidden" name="is_home" value="0">
                    <label class="site-page-form-card__checkbox">
                        <input
                            type="checkbox"
                            name="is_home"
                            value="1"
                            {{ ($isTargetedForm ? old('is_home') : ($page?->is_home ?? false)) ? 'checked' : '' }}
                        >
                        <span>Brug som forside</span>
                    </label>
                </div>
            </div>

            @unless ($page)
                <div class="site-page-form-card__section-picker">
                    <div class="site-page-form-card__section-picker-copy">
                        <span class="ui-field__label">Sidetype</span>
                        <p class="ui-copy">Vaelg en færdig side-skabelon. Hver side bliver oprettet med en fast struktur, så kunden kun redigerer indholdet bagefter.</p>
                    </div>

                    <div class="site-page-form-card__section-options">
                        @forelse (($availablePageTemplates ?? []) as $template => $definition)
                            <label class="site-page-form-card__section-option">
                                <input
                                    type="radio"
                                    name="page_template"
                                    value="{{ $template }}"
                                    {{ $selectedPageTemplate === $template ? 'checked' : '' }}
                                >
                                <span class="site-page-form-card__section-option-body">
                                    <strong>{{ $definition['label'] }}</strong>
                                    <small>{{ $definition['description'] }}</small>
                                    @if (! empty($definition['areas']))
                                        <span class="site-page-form-card__section-option-meta">
                                            Indeholder: {{ implode(' · ', $definition['areas']) }}
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="ui-copy">Det aktive theme har ikke nogen færdige sidetyper endnu.</p>
                        @endforelse
                    </div>
                </div>
            @endunless
        </fieldset>

        <div class="site-page-form-card__actions">
            @if ($canUpdateSite)
                <button type="submit" class="ui-button ui-button--ink">{{ $submitLabel }}</button>
            @else
                <p class="ui-copy">Din tenant-rolle har læseadgang, så sideopsaetningen kan ikke ændres fra dette login.</p>
            @endif
        </div>
    </form>
</article>
