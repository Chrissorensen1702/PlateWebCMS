<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-button ui-button--ink']) }}>
    {{ $slot }}
</button>
