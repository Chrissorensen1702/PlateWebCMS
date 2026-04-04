@php
    $selectedValue = (string) ($selected ?? '');
@endphp

<section class="site-design-group">
    <div class="site-design-group__header">
        <span class="ui-field__label ui-field__label--with-help">
            {{ $label }}
            @isset($help)
                <x-help-tooltip :text="$help" />
            @endisset
        </span>

        @isset($description)
            <p class="site-design-group__copy">{{ $description }}</p>
        @endisset
    </div>

    <div class="site-design-group__options">
        @foreach ($options as $option)
            @php
                $optionValue = (string) $option['value'];
                $isChecked = $selectedValue === $optionValue;
            @endphp

            <label class="site-design-option">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optionValue }}"
                    {{ $isChecked ? 'checked' : '' }}
                >

                <span class="site-design-option__card">
                    <span class="site-design-option__preview">{{ $option['preview'] }}</span>
                    <span class="site-design-option__text">
                        <strong>{{ $option['label'] }}</strong>
                        @if (! empty($option['hint']))
                            <small>{{ $option['hint'] }}</small>
                        @endif
                    </span>
                </span>
            </label>
        @endforeach
    </div>
</section>
