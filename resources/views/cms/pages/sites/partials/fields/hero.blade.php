@php
    $fieldName = fn (string $key): string => isset($inputNamePrefix) ? "{$inputNamePrefix}[{$key}]" : $key;
    $fieldValue = fn (string $key, mixed $default = ''): mixed => isset($oldPrefix)
        ? old("{$oldPrefix}.{$key}", $default)
        : old($key, $default);
    $imagePath = trim((string) $fieldValue('image_url', $data['image_url'] ?? ''));
    $imagePreviewUrl = $imagePath === ''
        ? null
        : (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://', '//', '/'])
            ? $imagePath
            : \Illuminate\Support\Facades\Storage::disk(config('filesystems.site_media_disk', 'public'))->url($imagePath));
@endphp

<div class="site-section-editor__layout">
    <section class="site-section-editor__panel">
        <div class="site-section-editor__grid">
            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Lille overtekst <x-help-tooltip text="Den lille tekst over overskriften. Brug den til at give hurtig kontekst til afsnittet." /></span>
                <input type="text" name="{{ $fieldName('eyebrow') }}" value="{{ $fieldValue('eyebrow', $data['eyebrow'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Titel <x-help-tooltip text="Den store hovedoverskrift, som besøgende ser først i dette afsnit." /></span>
                <input type="text" name="{{ $fieldName('title') }}" value="{{ $fieldValue('title', $data['title'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field site-section-editor__field--full">
                <span class="ui-field__label ui-field__label--with-help">Brødtekst <x-help-tooltip text="Den forklarende tekst under overskriften. Brug den til at uddybe budskabet kort og klart." /></span>
                <textarea name="{{ $fieldName('copy') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('copy', $data['copy'] ?? '') }}</textarea>
            </label>

            @if (in_array(($site->theme ?? null), ['base', 'editorial'], true))
                <input type="hidden" name="{{ $fieldName('image_url') }}" value="{{ $imagePath }}">

                <div class="site-section-editor__field--full site-section-editor__media-field">
                    <div class="site-section-editor__media-header">
                        <span class="ui-field__label ui-field__label--with-help">Billede <x-help-tooltip text="Upload billedet, der skal vises i topsektionen. I base-themeet ligger det til højre for teksten." /></span>
                        @if ($imagePreviewUrl)
                            <span class="dashboard-feed__meta">Aktuelt billede</span>
                        @endif
                    </div>

                    @if ($imagePreviewUrl)
                        <div class="site-section-editor__media-preview">
                            <img src="{{ $imagePreviewUrl }}" alt="{{ $fieldValue('image_alt', $data['image_alt'] ?? 'Hero-billede') }}" class="site-section-editor__media-image">
                        </div>
                    @endif

                    <label class="ui-field">
                        <span class="ui-field__label ui-field__label--with-help">Upload nyt billede <x-help-tooltip text="Vælg et nyt billede, hvis du vil erstatte det nuværende. Understøtter almindelige billedformater." /></span>
                        <input type="file" name="{{ $fieldName('image_upload') }}" class="ui-field__control" accept="image/*">
                    </label>

                    <label class="ui-field">
                        <span class="ui-field__label ui-field__label--with-help">Billedbeskrivelse <x-help-tooltip text="Kort beskrivelse af billedet til skærmlæsere og bedre tilgængelighed." /></span>
                        <input type="text" name="{{ $fieldName('image_alt') }}" value="{{ $fieldValue('image_alt', $data['image_alt'] ?? '') }}" class="ui-field__control">
                    </label>

                    @if ($imagePreviewUrl)
                        <label class="site-section-editor__checkbox">
                            <input type="checkbox" name="{{ $fieldName('remove_image') }}" value="1">
                            <span>Fjern nuværende billede</span>
                        </label>
                    @endif
                </div>
            @endif

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Primær knaptekst <x-help-tooltip text="Teksten på den vigtigste handlingsknap i topsektionen." /></span>
                <input type="text" name="{{ $fieldName('primary_cta_label') }}" value="{{ $fieldValue('primary_cta_label', $data['primary_cta_label'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Primært knaplink <x-help-tooltip text="Hvor den vigtigste knap skal sende brugeren hen, for eksempel /kontakt." /></span>
                <input type="text" name="{{ $fieldName('primary_cta_href') }}" value="{{ $fieldValue('primary_cta_href', $data['primary_cta_href'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Sekundær knaptekst <x-help-tooltip text="Teksten på den ekstra knap, hvis du vil tilbyde en sekundær handling." /></span>
                <input type="text" name="{{ $fieldName('secondary_cta_label') }}" value="{{ $fieldValue('secondary_cta_label', $data['secondary_cta_label'] ?? '') }}" class="ui-field__control">
            </label>

            <label class="ui-field">
                <span class="ui-field__label ui-field__label--with-help">Sekundært knaplink <x-help-tooltip text="Linket til den sekundære knap, for eksempel en underside eller et anker på siden." /></span>
                <input type="text" name="{{ $fieldName('secondary_cta_href') }}" value="{{ $fieldValue('secondary_cta_href', $data['secondary_cta_href'] ?? '') }}" class="ui-field__control">
            </label>
        </div>
    </section>

    <aside class="site-section-editor__panel site-section-editor__panel--design">
        <div class="site-section-editor__panel-header">
            <h5 class="site-section-editor__panel-title">Design udtryk for afsnittet</h5>
            <p class="site-section-editor__panel-copy">Vælg dit ønskede udtryk, uden at bryde layoutet.</p>
        </div>

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('heading_size'),
            'label' => '"Titel" skriftstørrelse',
            'help' => 'Vælg om overskriften skal vises i normal eller ekstra stor størrelse.',
            'selected' => $fieldValue('heading_size', $data['heading_size'] ?? 'large'),
            'options' => [
                ['value' => 'standard', 'label' => 'Normal', 'preview' => 'T', 'hint' => 'Mere roligt udtryk'],
                ['value' => 'large', 'label' => 'Stor', 'preview' => 'TT', 'hint' => 'Mere markant førstehåndsindtryk'],
            ],
        ])

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('text_align'),
            'label' => 'Placering',
            'help' => 'Vælg om indholdet i topsektionen skal være venstrestillet eller centreret.',
            'selected' => $fieldValue('text_align', $data['text_align'] ?? 'left'),
            'options' => [
                ['value' => 'left', 'label' => 'Venstre', 'preview' => 'L', 'hint' => 'Klassisk layout'],
                ['value' => 'center', 'label' => 'Centreret', 'preview' => 'C', 'hint' => 'Mere kampagnepræget'],
            ],
        ])

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('button_align'),
            'label' => 'Knapplacering',
            'help' => 'Bestem om knapperne skal ligge til venstre, i midten eller til højre i topsektionen.',
            'selected' => $fieldValue('button_align', $data['button_align'] ?? (($data['text_align'] ?? 'left') === 'center' ? 'center' : 'left')),
            'options' => [
                ['value' => 'left', 'label' => 'Venstre', 'preview' => 'L', 'hint' => 'Knapperne starter i venstre side'],
                ['value' => 'center', 'label' => 'Midt', 'preview' => 'C', 'hint' => 'Knapperne centreres'],
                ['value' => 'right', 'label' => 'Højre', 'preview' => 'R', 'hint' => 'Knapperne samles i højre side'],
            ],
        ])

        @if (in_array(($site->theme ?? null), ['base', 'editorial'], true))
            @include('cms.pages.sites.partials.design-choice-group', [
                'name' => $fieldName('image_focus'),
                'label' => 'Billedfokus',
                'help' => 'Vælg hvilken del af billedet der er vigtigst, hvis det bliver beskåret i heroen.',
                'selected' => $fieldValue('image_focus', $data['image_focus'] ?? 'center'),
                'options' => [
                    ['value' => 'left', 'label' => 'Venstre', 'preview' => '←', 'hint' => 'Holder venstre side synlig'],
                    ['value' => 'center', 'label' => 'Midt', 'preview' => '•', 'hint' => 'Balanceret visning'],
                    ['value' => 'right', 'label' => 'Højre', 'preview' => '→', 'hint' => 'Holder højre side synlig'],
                    ['value' => 'top', 'label' => 'Top', 'preview' => '↑', 'hint' => 'Fokuserer øverst i billedet'],
                    ['value' => 'bottom', 'label' => 'Bund', 'preview' => '↓', 'hint' => 'Fokuserer nederst i billedet'],
                ],
            ])
        @endif

        @include('cms.pages.sites.partials.design-choice-group', [
            'name' => $fieldName('secondary_cta_mode'),
            'label' => 'Ekstra knap',
            'help' => 'Bestem om den ekstra knap skal vises på siden eller gemmes væk.',
            'selected' => $fieldValue('secondary_cta_mode', $data['secondary_cta_mode'] ?? 'show'),
            'options' => [
                ['value' => 'show', 'label' => 'Vis', 'preview' => '+', 'hint' => 'To knapper i afsnittet'],
                ['value' => 'hide', 'label' => 'Skjul', 'preview' => '-', 'hint' => 'Kun den primære knap'],
            ],
        ])
    </aside>
</div>
