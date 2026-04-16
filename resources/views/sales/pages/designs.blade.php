@extends('sales.layouts.default')

@section('title', 'Se designs')
@section('body-class', 'marketing-body marketing-body--designs')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight designs-page__hero">
        <div class="ui-shell designs-page__shell">
            <div class="section-heading section-heading--split">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Se designs</p>
                    <h1 class="section-heading__title">Fa et indtryk af de designretninger, som loesningerne kan tage udgangspunkt i.</h1>
                </div>

                <p class="section-heading__side">
                    Designsiden viser de visuelle spor i universet. De fungerer som et staerkt udgangspunkt, hvad enten loesningen
                    lander som en skarp template eller udvikler sig videre mod et mere frit Signature-forloeb.
                </p>
            </div>

            <div class="designs-page__overview">
                <article class="ui-card ui-card--dark designs-page__feature">
                    <p class="ui-kicker ui-kicker--light">Showcase</p>
                    <h2 class="ui-title">Designvalg skal goere det lettere at saelge og lettere at differentiere kunderne.</h2>
                    <p class="designs-page__feature-copy">
                        I stedet for at starte fra et tomt laerred viser vi tydelige retninger, som kan tilpasses med farver, sektioner,
                        indhold og bookingmoduler. Det goer det lettere for kunden at se sig selv i loesningen.
                    </p>
                </article>

                <div class="ui-card designs-page__preview">
                    <img
                        src="{{ asset('images/sales/plateweb-desktop-demo.png') }}"
                        alt="PlateWeb designpreview"
                        class="designs-page__preview-image"
                    >
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section ui-section--compact">
        <div class="ui-shell">
            <div class="designs-page__grid">
                @foreach ($themes as $theme)
                    <article class="ui-card designs-page__theme-card designs-page__theme-card--{{ $theme['key'] }}">
                        <div class="designs-page__theme-head">
                            <p class="designs-page__theme-label">{{ $theme['label'] }}</p>
                            <p class="designs-page__theme-vibe">{{ $theme['vibe'] }}</p>
                        </div>

                        <p class="designs-page__theme-copy">{{ $theme['description'] }}</p>

                        <div class="designs-page__theme-tags" aria-label="{{ $theme['label'] }} anbefales til">
                            @foreach ($theme['recommended_for'] as $recommendation)
                                <span class="designs-page__theme-tag">{{ $recommendation }}</span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="ui-section ui-section--compact">
        <div class="ui-shell designs-page__notes">
            @foreach ($designNotes as $note)
                <article class="ui-card designs-page__note">
                    <h2 class="designs-page__note-title">{{ $note['title'] }}</h2>
                    <p class="designs-page__note-copy">{{ $note['copy'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
