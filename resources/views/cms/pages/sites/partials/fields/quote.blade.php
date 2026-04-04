@php
    $fieldName = fn (string $key): string => isset($inputNamePrefix) ? "{$inputNamePrefix}[{$key}]" : $key;
    $fieldValue = fn (string $key, mixed $default = ''): mixed => isset($oldPrefix)
        ? old("{$oldPrefix}.{$key}", $default)
        : old($key, $default);
@endphp

<div class="site-section-editor__layout">
    <section class="site-section-editor__panel">
        <div class="site-section-editor__grid">
            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Lille overtekst <x-help-tooltip text="En kort label over citatet, fx Kundeudtalelse eller Statement." /></span>
                <input type="text" name="{{ $fieldName('eyebrow') }}" value="{{ $fieldValue('eyebrow', $data['eyebrow'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Citat <x-help-tooltip text="Selve citatet eller udtalelsen der skal fremhæves." /></span>
                <textarea name="{{ $fieldName('quote_text') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('quote_text', $data['quote_text'] ?? '') }}</textarea>
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Navn <x-help-tooltip text="Navnet på personen bag citatet." /></span>
                <input type="text" name="{{ $fieldName('quote_author') }}" value="{{ $fieldValue('quote_author', $data['quote_author'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Titel eller virksomhed <x-help-tooltip text="En kort rolle, titel eller virksomhedsnavn under citatet." /></span>
                <input type="text" name="{{ $fieldName('quote_role') }}" value="{{ $fieldValue('quote_role', $data['quote_role'] ?? '') }}" class="ui-field__control">
            </label>
        </div>
    </section>

    <aside class="site-section-editor__panel site-section-editor__panel--design">
        <div class="site-section-editor__panel-header">
            <h5 class="site-section-editor__panel-title">Visuel opsætning</h5>
            <p class="site-section-editor__panel-copy">Her styrer du om citatet skal føles roligt eller mere markant.</p>
        </div>

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('text_align'),
            'label' => 'Placering',
            'help' => 'Vælg om citatet skal stå venstrestillet eller centreret.',
            'selected' => $fieldValue('text_align', $data['text_align'] ?? 'left'),
            'options' => [
                ['value' => 'left', 'label' => 'Venstre', 'preview' => 'L', 'hint' => 'Klassisk og redaktionelt'],
                ['value' => 'center', 'label' => 'Centreret', 'preview' => 'C', 'hint' => 'Mere kampagnepræget'],
            ],
        ])

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('section_tone'),
            'label' => 'Udtryk',
            'help' => 'Skift mellem et neutralt eller mere fremhævet citatmodul.',
            'selected' => $fieldValue('section_tone', $data['section_tone'] ?? 'accent'),
            'options' => [
                ['value' => 'default', 'label' => 'Standard', 'preview' => 'O', 'hint' => 'Roligt og neutralt'],
                ['value' => 'accent', 'label' => 'Fremhævet', 'preview' => '*', 'hint' => 'Mere tydelig markering'],
            ],
        ])
    </aside>
</div>
