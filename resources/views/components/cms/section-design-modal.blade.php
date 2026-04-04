@props([
    'buttonLabel' => 'Designvalg',
])

<div x-data="{ designOpen: false }" class="site-section-editor__design-tools">
    <div class="site-section-editor__design-tools-bar">
        <button type="button" class="site-section-editor__design-trigger" x-on:click="designOpen = true">
            {{ $buttonLabel }}
        </button>
    </div>

    <div
        class="site-section-editor__design-modal"
        x-cloak
        x-show="designOpen"
        x-transition.opacity.duration.180ms
        x-on:keydown.escape.window="designOpen = false"
    >
        <div class="site-section-editor__design-backdrop" x-on:click="designOpen = false"></div>

        <div class="site-section-editor__design-dialog" x-on:click.stop>
            <aside class="site-section-editor__panel site-section-editor__panel--design site-section-editor__panel--design-modal">
                <div class="site-section-editor__design-dialog-top">
                    <div></div>
                    <button type="button" class="site-section-editor__design-close" x-on:click="designOpen = false">Luk</button>
                </div>

                {{ $slot }}
            </aside>
        </div>
    </div>
</div>
