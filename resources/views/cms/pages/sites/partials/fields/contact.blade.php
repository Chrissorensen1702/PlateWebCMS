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

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Email <x-help-tooltip text="Den emailadresse besøgende ser eller kan klikke på fra kontaktafsnittet." /></span>
                <input type="email" name="{{ $fieldName('email') }}" value="{{ $fieldValue('email', $data['email'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Telefon <x-help-tooltip text="Telefonnummeret der vises i kontaktområdet." /></span>
                <input type="text" name="{{ $fieldName('phone') }}" value="{{ $fieldValue('phone', $data['phone'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Knaptekst <x-help-tooltip text="Teksten på kontaktafsnittets handlingsknap." /></span>
                <input type="text" name="{{ $fieldName('cta_label') }}" value="{{ $fieldValue('cta_label', $data['cta_label'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Knaplink <x-help-tooltip text="Linket som kontaktknappen sender brugeren videre til." /></span>
                <input type="text" name="{{ $fieldName('cta_href') }}" value="{{ $fieldValue('cta_href', $data['cta_href'] ?? '') }}" class="ui-field__control">
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

    <aside class="site-section-editor__panel site-section-editor__panel--design">
        <div class="site-section-editor__panel-header">
            <p class="site-section-editor__panel-eyebrow">Design</p>
            <h5 class="site-section-editor__panel-title">Opsætning</h5>
            <p class="site-section-editor__panel-copy">Vælg hvordan kontaktafsnittet skal fremstå uden at give slip på designet.</p>
        </div>

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('layout_style'),
            'label' => 'Layout',
            'help' => 'Vælg om sektionen skal være delt, centreret eller vise tekst sammen med et Google Maps-kort.',
            'selected' => $fieldValue('layout_style', $data['layout_style'] ?? 'split'),
            'options' => [
                ['value' => 'split', 'label' => 'Delt', 'preview' => '||', 'hint' => 'Tekst og kontakt ved siden af hinanden'],
                ['value' => 'center', 'label' => 'Centreret', 'preview' => '[]', 'hint' => 'Mere roligt og samlet udtryk'],
                ['value' => 'map', 'label' => 'Kort', 'preview' => 'M', 'hint' => 'Tekst til venstre og Google Maps til højre'],
            ],
        ])

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

        <section class="site-design-group">
            <div class="site-design-group__header">
                <span class="ui-field__label ui-field__label--with-help">
                    Vis telefonnummer
                    <x-help-tooltip text="Fjern fluebenet hvis telefonnummeret ikke skal vises på siden, men stadig gemmes i CMS'et." />
                </span>
            </div>

            <div class="site-design-toggle-card">
                <input type="hidden" name="{{ $fieldName('show_phone') }}" value="0">
                <label class="site-section-editor__checkbox">
                    <input type="checkbox" name="{{ $fieldName('show_phone') }}" value="1" {{ (string) $fieldValue('show_phone', $data['show_phone'] ?? '1') === '1' ? 'checked' : '' }}>
                    <span>Vis telefonnummer på siden</span>
                </label>
            </div>
        </section>
    </aside>
</div>
