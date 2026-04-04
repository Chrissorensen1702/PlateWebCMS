@php
    $fieldName = fn (string $key): string => isset($inputNamePrefix) ? "{$inputNamePrefix}[{$key}]" : $key;
    $fieldValue = fn (string $key, mixed $default = ''): mixed => isset($oldPrefix)
        ? old("{$oldPrefix}.{$key}", $default)
        : old($key, $default);
@endphp

<div class="site-section-editor__grid">
    <label class="ui-field">
        <span class="ui-field__label ui-field__label--with-help">Lille overtekst <x-help-tooltip text="Den lille tekst over afsnittets overskrift." /></span>
        <input type="text" name="{{ $fieldName('eyebrow') }}" value="{{ $fieldValue('eyebrow', $data['eyebrow'] ?? '') }}" class="ui-field__control">
    </label>

    <label class="ui-field site-section-editor__field--full">
        <span class="ui-field__label ui-field__label--with-help">Titel <x-help-tooltip text="Overskriften for dette område." /></span>
        <input type="text" name="{{ $fieldName('title') }}" value="{{ $fieldValue('title', $data['title'] ?? '') }}" class="ui-field__control">
    </label>

    <label class="ui-field site-section-editor__field--full">
        <span class="ui-field__label ui-field__label--with-help">Brødtekst <x-help-tooltip text="Den tekst der beskriver indholdet i området." /></span>
        <textarea name="{{ $fieldName('copy') }}" class="ui-field__control ui-field__control--textarea">{{ $fieldValue('copy', $data['copy'] ?? '') }}</textarea>
    </label>
</div>
