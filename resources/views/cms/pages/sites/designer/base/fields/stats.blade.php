@php
    $fieldName = fn (string $key): string => isset($inputNamePrefix) ? "{$inputNamePrefix}[{$key}]" : $key;
    $fieldValue = fn (string $key, mixed $default = ''): mixed => isset($oldPrefix)
        ? old("{$oldPrefix}.{$key}", $default)
        : old($key, $default);
@endphp

<div class="site-section-editor__layout">
    <section class="site-section-editor__panel">
        <x-cms.section-design-modal button-label="Visuel opsætning">
            <div class="site-section-editor__panel-header">
                <h5 class="site-section-editor__panel-title">Visuel opsætning</h5>
                <p class="site-section-editor__panel-copy">Her styrer du hvordan nøgletallene præsenteres uden at bryde layoutet.</p>
            </div>

            @include('cms.pages.sites.partials.design-choice-group', [
                'name' => $fieldName('display_style'),
                'label' => 'Visning',
                'help' => 'Vælg om nøgletallene skal stå som kort eller i en mere samlet række.',
                'selected' => $fieldValue('display_style', $data['display_style'] ?? 'cards'),
                'options' => [
                    ['value' => 'cards', 'label' => 'Kort', 'preview' => '[]', 'hint' => 'Mere visuelt og opdelt'],
                    ['value' => 'strip', 'label' => 'Række', 'preview' => '===', 'hint' => 'Mere kompakt og samlet'],
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
        </x-cms.section-design-modal>

        <div class="site-section-editor__grid">
            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Lille overtekst <x-help-tooltip text="En kort introduktion over nøgletallene." /></span>
                <input type="text" name="{{ $fieldName('eyebrow') }}" value="{{ $fieldValue('eyebrow', $data['eyebrow'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Titel <x-help-tooltip text="Overskriften der sætter retning for tallene eller highlights." /></span>
                <input type="text" name="{{ $fieldName('title') }}" value="{{ $fieldValue('title', $data['title'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Brødtekst <x-help-tooltip text="Kort forklaring af hvorfor nøgletallene er relevante." /></span>
                <textarea name="{{ $fieldName('copy') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('copy', $data['copy'] ?? '') }}</textarea>
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Nøgletal (én pr. linje) <x-help-tooltip text="Skriv et tal og en label på hver linje som fx: 98% | Kundetilfredshed." /></span>
                <textarea name="{{ $fieldName('items') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('items', implode("\n", $data['items'] ?? [])) }}</textarea>
            </label>
        </div>
    </section>

</div>
