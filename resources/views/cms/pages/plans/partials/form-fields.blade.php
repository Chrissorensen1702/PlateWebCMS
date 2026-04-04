@php($prefix = $plan ? 'updatePlan' . $plan->id : 'createPlan')

<div class="plans-admin-form__grid">
    <label class="ui-field">
        <span class="ui-field__label">Navn</span>
        <input
            type="text"
            name="name"
            value="{{ old('name', $plan?->name) }}"
            class="ui-field__control"
            required
        >
    </label>

    <label class="ui-field">
        <span class="ui-field__label">Slug</span>
        <input
            type="text"
            name="slug"
            value="{{ old('slug', $plan?->slug) }}"
            class="ui-field__control"
            placeholder="Valgfri - laves automatisk hvis tom"
        >
    </label>

    <label class="ui-field">
        <span class="ui-field__label">Type</span>
        <select name="kind" class="ui-field__control">
            @foreach ($kindOptions as $kindKey => $kindLabel)
                <option value="{{ $kindKey }}" @selected(old('kind', $plan?->kind ?? 'template') === $kindKey)>{{ $kindLabel }}</option>
            @endforeach
        </select>
    </label>

    <label class="ui-field">
        <span class="ui-field__label">Sortering</span>
        <input
            type="number"
            min="0"
            name="sort_order"
            value="{{ old('sort_order', $plan?->sort_order ?? 0) }}"
            class="ui-field__control"
        >
    </label>

    <label class="ui-field plans-admin-form__field--wide">
        <span class="ui-field__label">Headline</span>
        <input
            type="text"
            name="headline"
            value="{{ old('headline', $plan?->headline) }}"
            class="ui-field__control"
            required
        >
    </label>

    <label class="ui-field plans-admin-form__field--wide">
        <span class="ui-field__label">Beskrivelse</span>
        <textarea name="summary" rows="4" class="ui-field__control" required>{{ old('summary', $plan?->summary) }}</textarea>
    </label>

    <label class="ui-field">
        <span class="ui-field__label">Fra pris</span>
        <input
            type="number"
            min="0"
            name="price_from"
            value="{{ old('price_from', $plan?->price_from) }}"
            class="ui-field__control"
            placeholder="Fx 3999"
        >
    </label>

    <label class="ui-field">
        <span class="ui-field__label">Leveringstid</span>
        <input
            type="text"
            name="build_time"
            value="{{ old('build_time', $plan?->build_time) }}"
            class="ui-field__control"
            placeholder="Fx 2 uger"
        >
    </label>

    <label class="ui-field plans-admin-form__field--wide">
        <span class="ui-field__label">Features (én pr. linje)</span>
        <textarea name="features" rows="5" class="ui-field__control">{{ $features }}</textarea>
    </label>
</div>

<label class="plans-admin-form__toggle">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $plan?->is_active ?? true))>
    <span>Pakke er aktiv og må vises i flowet</span>
</label>
