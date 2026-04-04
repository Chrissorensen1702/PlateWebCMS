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
                <span class="ui-field__label ui-field__label--with-help">Lille overtekst <x-help-tooltip text="Den lille introduktionstekst over afsnittets overskrift." /></span>
                <input type="text" name="{{ $fieldName('eyebrow') }}" value="{{ $fieldValue('eyebrow', $data['eyebrow'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Titel <x-help-tooltip text="Overskriften for dette afsnit." /></span>
                <input type="text" name="{{ $fieldName('title') }}" value="{{ $fieldValue('title', $data['title'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Brødtekst <x-help-tooltip text="Brødteksten der forklarer emnet i afsnittet mere detaljeret." /></span>
                <textarea name="{{ $fieldName('copy') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('copy', $data['copy'] ?? '') }}</textarea>
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Punkter (én pr. linje) <x-help-tooltip text="Hver linje bliver vist som et separat punkt i listen på siden." /></span>
                <textarea name="{{ $fieldName('items') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('items', implode("\n", $data['items'] ?? [])) }}</textarea>
            </label>
        </div>
    </section>

    <aside class="site-section-editor__panel site-section-editor__panel--design">
        <div class="site-section-editor__panel-header">
            <h5 class="site-section-editor__panel-title">Visuel opsætning</h5>
            <p class="site-section-editor__panel-copy">Her styrer du udtrykket for afsnittet med faste, sikre valg.</p>
        </div>

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('text_align'),
            'label' => 'Placering',
            'help' => 'Vælg om tekst og indhold skal være venstrestillet eller centreret.',
            'selected' => $fieldValue('text_align', $data['text_align'] ?? 'left'),
            'options' => [
                ['value' => 'left', 'label' => 'Venstre', 'preview' => 'L', 'hint' => 'Klassisk læseretning'],
                ['value' => 'center', 'label' => 'Centreret', 'preview' => 'C', 'hint' => 'Mere kampagnepræget udtryk'],
            ],
        ])

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('section_tone'),
            'label' => 'Udtryk',
            'help' => 'Skift mellem et almindeligt afsnit eller en mere fremhævet boks.',
            'selected' => $fieldValue('section_tone', $data['section_tone'] ?? 'default'),
            'options' => [
                ['value' => 'default', 'label' => 'Standard', 'preview' => 'O', 'hint' => 'Roligt og neutralt'],
                ['value' => 'accent', 'label' => 'Fremhævet', 'preview' => '*', 'hint' => 'Lidt mere fokus og varme'],
            ],
        ])

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('items_style'),
            'label' => 'Visning af punkter',
            'help' => 'Vælg om punkterne skal vises som klassisk liste eller som små kort.',
            'selected' => $fieldValue('items_style', $data['items_style'] ?? 'list'),
            'options' => [
                ['value' => 'list', 'label' => 'Liste', 'preview' => '1.', 'hint' => 'Enkel og overskuelig'],
                ['value' => 'cards', 'label' => 'Kort', 'preview' => '[]', 'hint' => 'Mere visuelt udtryk'],
            ],
        ])
    </aside>
</div>
