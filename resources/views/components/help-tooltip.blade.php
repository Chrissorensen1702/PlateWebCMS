@props([
    'text',
])

<span
    class="ui-help-tip"
    x-data="{ open: false }"
    x-on:mouseenter="open = true"
    x-on:mouseleave="open = false"
    x-on:focusin="open = true"
    x-on:focusout="open = false"
    x-on:click.outside="open = false"
>
    <button
        type="button"
        class="ui-help-tip__trigger"
        x-on:click.prevent="open = ! open"
        aria-label="Vis feltforklaring"
        x-bind:aria-expanded="open.toString()"
    >
        ?
    </button>

    <span
        class="ui-help-tip__bubble"
        x-show="open"
        x-transition.opacity.duration.150ms
        style="display: none;"
        role="tooltip"
    >
        {{ $text }}
    </span>
</span>
