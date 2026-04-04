<div class="site-page-form-card__grid">
    <label class="ui-field">
        <span class="ui-field__label ui-field__label--with-help">Navn <x-help-tooltip text="Det interne navn på siden i CMS-navigationen." /></span>
        <input
            type="text"
            name="name"
            value="{{ old('name', $page->name) }}"
            class="ui-field__control"
        >
    </label>

    <label class="ui-field">
        <span class="ui-field__label ui-field__label--with-help">Slug / URL-del <x-help-tooltip text="Den del af adressen der kommer efter domænet, for eksempel /kontakt." /></span>
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $page->slug) }}"
            class="ui-field__control"
        >
    </label>

    <label class="ui-field site-page-form-card__field--full">
        <span class="ui-field__label ui-field__label--with-help">Titel <x-help-tooltip text="Sidens titel, som bruges i browseren og som udgangspunkt for SEO." /></span>
        <input
            type="text"
            name="title"
            value="{{ old('title', $page->title) }}"
            class="ui-field__control"
        >
    </label>

    <label class="ui-field site-page-form-card__field--full">
        <span class="ui-field__label ui-field__label--with-help">Meta-beskrivelse <x-help-tooltip text="Den korte beskrivelse som søgemaskiner ofte viser under sidens titel." /></span>
        <textarea name="meta_description" class="ui-field__control ui-field__control--textarea">{{ old('meta_description', $page->meta_description) }}</textarea>
    </label>

    <label class="ui-field">
        <span class="ui-field__label ui-field__label--with-help">Sortering <x-help-tooltip text="Bestemmer rækkefølgen på siden i navigationen. Lavere tal vises først." /></span>
        <input
            type="number"
            name="sort_order"
            min="0"
            max="999"
            value="{{ old('sort_order', $page->sort_order) }}"
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
                {{ old('is_published', $page->is_published) ? 'checked' : '' }}
            >
            <span class="ui-field__label--with-help">Siden er public <x-help-tooltip text="Bestemmer om siden skal være synlig på det live site, når du publicerer." /></span>
        </label>

        <input type="hidden" name="is_home" value="0">
        <label class="site-page-form-card__checkbox">
            <input
                type="checkbox"
                name="is_home"
                value="1"
                {{ old('is_home', $page->is_home) ? 'checked' : '' }}
            >
            <span class="ui-field__label--with-help">Brug som forside <x-help-tooltip text="Gør siden til sitets forside, når besøgende går ind på domænet." /></span>
        </label>
    </div>
</div>
