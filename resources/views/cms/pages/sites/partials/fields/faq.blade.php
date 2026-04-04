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
                <span class="ui-field__label ui-field__label--with-help">Lille overtekst <x-help-tooltip text="En kort introduktion over spørgsmålene." /></span>
                <input type="text" name="{{ $fieldName('eyebrow') }}" value="{{ $fieldValue('eyebrow', $data['eyebrow'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Titel <x-help-tooltip text="Overskriften for spørgsmålssektionen." /></span>
                <input type="text" name="{{ $fieldName('title') }}" value="{{ $fieldValue('title', $data['title'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Brødtekst <x-help-tooltip text="Kort introduktion til spørgsmålene og hvordan man bruger sektionen." /></span>
                <textarea name="{{ $fieldName('copy') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('copy', $data['copy'] ?? '') }}</textarea>
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Spørgsmål og svar (én pr. linje) <x-help-tooltip text="Skriv et spørgsmål og svar på hver linje som fx: Hvordan fungerer det? | Vi starter med en afklaring." /></span>
                <textarea name="{{ $fieldName('items') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('items', implode("\n", $data['items'] ?? [])) }}</textarea>
            </label>
        </div>
    </section>

    <aside class="site-section-editor__panel site-section-editor__panel--design">
        <div class="site-section-editor__panel-header">
            <h5 class="site-section-editor__panel-title">Visuel opsætning</h5>
            <p class="site-section-editor__panel-copy">Her vælger du om spørgsmålene skal vises samlet eller som mere tydelige kort.</p>
        </div>

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('layout_style'),
            'label' => 'Visning',
            'help' => 'Vælg om spørgsmålene skal stå i en samlet liste eller som tydelige kort.',
            'selected' => $fieldValue('layout_style', $data['layout_style'] ?? 'stacked'),
            'options' => [
                ['value' => 'stacked', 'label' => 'Liste', 'preview' => '≡', 'hint' => 'Samlet og overskuelig'],
                ['value' => 'cards', 'label' => 'Kort', 'preview' => '[]', 'hint' => 'Mere opdelt og visuelt'],
            ],
        ])

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('section_tone'),
            'label' => 'Udtryk',
            'help' => 'Skift mellem neutral eller mere fremhævet baggrund.',
            'selected' => $fieldValue('section_tone', $data['section_tone'] ?? 'default'),
            'options' => [
                ['value' => 'default', 'label' => 'Standard', 'preview' => 'O', 'hint' => 'Roligt og neutralt'],
                ['value' => 'accent', 'label' => 'Fremhævet', 'preview' => '*', 'hint' => 'Mere markant sektion'],
            ],
        ])
    </aside>
</div>
