@props(['active'])

@php
$classes = ($active ?? false)
            ? 'app-responsive-link app-responsive-link--active'
            : 'app-responsive-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
