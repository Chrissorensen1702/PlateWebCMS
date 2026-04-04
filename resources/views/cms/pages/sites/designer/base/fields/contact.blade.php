@php
    $fieldName = fn (string $key): string => isset($inputNamePrefix) ? "{$inputNamePrefix}[{$key}]" : $key;
    $fieldValue = fn (string $key, mixed $default = ''): mixed => isset($oldPrefix)
        ? old("{$oldPrefix}.{$key}", $default)
        : old($key, $default);
@endphp

<div class="site-section-editor__layout">
    <section class="site-section-editor__panel">
        <x-cms.section-design-modal button-label="Designvalg">
            <div class="site-section-editor__panel-header">
                <p class="site-section-editor__panel-eyebrow">Design</p>
                <h5 class="site-section-editor__panel-title">Udtryk</h5>
                <p class="site-section-editor__panel-copy">Afsnittet bruger altid et delt layout med tekst til venstre og Google Maps til højre.</p>
            </div>

            @include('cms.pages.sites.partials.design-choice-group', [
                'name' => $fieldName('section_tone'),
                'label' => 'Udtryk',
                'help' => 'Skift mellem et neutralt kontaktfelt eller en mere fremhævet version.',
                'selected' => $fieldValue('section_tone', $data['section_tone'] ?? 'default'),
                'options' => [
                    ['value' => 'default', 'label' => 'Standard', 'preview' => 'O', 'hint' => 'Rent og neutralt'],
                    ['value' => 'accent', 'label' => 'Fremhævet', 'preview' => '*', 'hint' => 'Mere synlig afslutning'],
                ],
            ])
        </x-cms.section-design-modal>

        <div class="site-section-editor__grid">
            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Lille overtekst <x-help-tooltip text="Den lille tekst over kontaktafsnittets hovedoverskrift." /></span>
                <input type="text" name="{{ $fieldName('eyebrow') }}" value="{{ $fieldValue('eyebrow', $data['eyebrow'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Titel <x-help-tooltip text="Hovedoverskriften i kontaktområdet." /></span>
                <input type="text" name="{{ $fieldName('title') }}" value="{{ $fieldValue('title', $data['title'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Brødtekst <x-help-tooltip text="En kort forklaring eller invitation til at tage kontakt." /></span>
                <textarea name="{{ $fieldName('copy') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('copy', $data['copy'] ?? '') }}</textarea>
            </label>

            <div class="site-section-editor__field--full site-section-editor__map-help">
                <div class="site-section-editor__note">
                    <strong>Sådan får du Google iframe-koden:</strong>
                    <ol class="site-section-editor__note-list">
                        <li>Åbn Google Maps og søg på din lokation.</li>
                        <li>Klik på menuen (tre streger) eller "Del"-knappen.</li>
                        <li>Vælg fanen "Integrer kort" (Embed map).</li>
                        <li>Vælg størrelse (lille, medium, stor eller tilpasset).</li>
                        <li>Klik på "Kopiér HTML".</li>
                    </ol>
                </div>

                <label class="ui-field">
                    <span class="ui-field__label ui-field__label--with-help">Google Maps embed-link <x-help-tooltip text="Bruges til kort-layoutet. Du kan indsætte enten selve embed-linket eller hele iframe-koden fra Google Maps." /></span>
                    <textarea name="{{ $fieldName('map_embed_url') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('map_embed_url', $data['map_embed_url'] ?? '') }}</textarea>
                </label>
            </div>
        </div>
    </section>

</div>
