@extends('sales.layouts.default')

@section('title', 'Se designs')
@section('body-class', 'marketing-body marketing-body--designs')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight designs-page__hero">
        <div
            class="ui-shell designs-page__shell"
            x-data="{ activeTheme: null }"
            x-init="$watch('activeTheme', value => document.documentElement.classList.toggle('designs-page-modal-open', Boolean(value)))"
            x-on:keydown.escape.window="activeTheme = null"
        >
            <div class="designs-page__layout">
                <aside class="designs-page__sidebar">
                    <div class="section-heading">
                        <div class="section-heading__content">
                            <h1 class="section-heading__title">Et design, der passer til jeres forretning</h1>
                        </div>

                        <p class="designs-page__lead">
                            Med <strong>Altier</strong> og <strong>Studio</strong> får du en række templates at vælge imellem. Dette er selv rammen for jeres hjemmeside, men kan
                            tilpasses med det antal sider og de sektioner i ønsker at have. Det er nemt, effektivt og styrker jeres digitale branding.
                        </p>
                    </div>

                    <article class="ui-card designs-page__palette">
                        <div class="designs-page__palette-header">
                            <p class="ui-kicker designs-page__palette-kicker">Farveretninger</p>
                            <h2 class="designs-page__palette-title">Vælg farver, så temaet passer til jeres branding</h2>
                        </div>

                        <div class="designs-page__palette-list">
                            @foreach ($themes as $theme)
                                <div
                                    class="designs-page__palette-row"
                                    x-bind:class="{ 'designs-page__palette-row--active': activeTheme === '{{ $theme['key'] }}' }"
                                >
                                    <span class="designs-page__palette-label">{{ $theme['palette_label'] }}</span>

                                    <div class="designs-page__palette-dots" aria-label="Farver for {{ $theme['palette_label'] }}">
                                        @foreach ($theme['palette'] as $color)
                                            <span class="designs-page__palette-dot" style="--palette-dot: {{ $color }};"></span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <div class="designs-page__notes">
                        @foreach ($designNotes as $note)
                            <article class="ui-card designs-page__note">
                                <h2 class="designs-page__note-title">{{ $note['title'] }}</h2>
                                <p class="designs-page__note-copy">{{ $note['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </aside>

                <div class="designs-page__content">
                    <article class="ui-card designs-page__steps">
                        <div class="designs-page__steps-grid">
                            <div class="designs-page__step">
                                <span class="designs-page__step-number">1</span>
                                <p class="designs-page__step-copy">Vælg det tema, der passer<br>bedst til jeres forretning,<br>målgruppe og det udtryk,<br>I gerne vil skabe.</p>
                            </div>

                            <div class="designs-page__step">
                                <span class="designs-page__step-number">2</span>
                                <p class="designs-page__step-copy">Tilpas farverne, så de matcher<br>jeres brand og visuelle identitet.<br>Vælg en farveretning, der<br>understøtter det udtryk, I gerne vil skabe.</p>
                            </div>

                            <div class="designs-page__step">
                                <span class="designs-page__step-number">3</span>
                                <p class="designs-page__step-copy">Tilføj de sider og sektioner,<br>I har brug for. Vælg mellem<br>mere end 30 forskellige<br>moduler og indholdstyper.</p>
                            </div>
                        </div>
                    </article>

                    <div class="designs-page__stack">
                        @foreach ($showcaseThemes as $theme)
                            <article
                                class="ui-card designs-page__theme-panel"
                                data-theme="{{ $theme['key'] }}"
                                x-bind:class="{ 'designs-page__theme-panel--active': activeTheme === '{{ $theme['key'] }}' }"
                            >
                                <button
                                    type="button"
                                    class="designs-page__theme-toggle"
                                    aria-haspopup="dialog"
                                    aria-controls="designs-preview-modal"
                                    x-on:click="activeTheme = '{{ $theme['key'] }}'"
                                    x-bind:aria-expanded="(activeTheme === '{{ $theme['key'] }}').toString()"
                                >
                                    <h3 class="designs-page__theme-title">{{ $theme['label'] }}</h3>

                                    <div class="designs-page__theme-meta">
                                        <p class="designs-page__theme-vibe">{{ $theme['vibe'] }}</p>

                                        <span class="designs-page__theme-toggle-action">
                                            <span
                                                class="designs-page__theme-toggle-label"
                                                x-bind:class="{ 'designs-page__theme-toggle-label--active': activeTheme === '{{ $theme['key'] }}' }"
                                                x-text="activeTheme === '{{ $theme['key'] }}' ? 'Preview aaben' : 'Se preview'"
                                            >
                                                Se preview
                                            </span>
                                        </span>
                                    </div>
                                </button>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>

            <div
                id="designs-preview-modal"
                class="designs-page__modal"
                x-cloak
                x-show="activeTheme"
                x-transition.opacity.duration.200ms
                x-bind:aria-hidden="(! activeTheme).toString()"
                aria-modal="true"
                role="dialog"
            >
                <div class="designs-page__modal-backdrop" x-on:click="activeTheme = null" aria-hidden="true"></div>

                @foreach ($showcaseThemes as $theme)
                    <section
                        class="designs-page__modal-dialog"
                        x-cloak
                        x-show="activeTheme === '{{ $theme['key'] }}'"
                        x-transition.opacity.duration.150ms
                        x-on:click.stop
                        aria-label="Preview af {{ $theme['label'] }}"
                    >
                        <div class="designs-page__modal-header">
                            <div class="designs-page__modal-copy">
                                <p class="designs-page__modal-kicker">{{ $theme['vibe'] }}</p>
                                <h2 class="designs-page__modal-title">{{ $theme['label'] }}</h2>
                                <p class="designs-page__modal-description">{{ $theme['description'] }}</p>
                            </div>

                            <div class="designs-page__modal-actions">
                                <a
                                    href="{{ route('sales.designs.preview', ['theme' => $theme['key']]) }}"
                                    class="designs-page__modal-link"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    Aabn separat
                                </a>

                                <button type="button" class="designs-page__modal-close" x-on:click="activeTheme = null">
                                    Luk
                                </button>
                            </div>
                        </div>

                        <div class="designs-page__browser designs-page__browser--modal">
                            <div class="designs-page__browser-stage">
                                <iframe
                                    src="about:blank"
                                    title="Preview af {{ $theme['label'] }}"
                                    class="designs-page__browser-frame designs-page__browser-frame--modal"
                                    loading="lazy"
                                    tabindex="-1"
                                    x-bind:src="activeTheme === '{{ $theme['key'] }}' ? @js($theme['embed_url']) : 'about:blank'"
                                ></iframe>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>
@endsection
