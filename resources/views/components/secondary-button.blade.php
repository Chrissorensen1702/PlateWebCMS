<button {{ $attributes->merge(['type' => 'button', 'class' => 'ui-button ui-button--light']) }}>
    {{ $slot }}
</button>
