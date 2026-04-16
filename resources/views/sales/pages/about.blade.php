@extends('sales.layouts.default')

@section('title', 'Om os')
@section('body-class', 'marketing-body marketing-body--about')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight about-page__hero">
        <div class="ui-shell about-page__shell">
            <div class="section-heading section-heading--split">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Om os</p>
                    <h1 class="section-heading__title">Vi bygger loesninger, der samler hjemmeside, booking og CMS i samme retning.</h1>
                </div>

                <p class="section-heading__side">
                    PlateWeb er bygget med tanke paa virksomheder, der gerne vil have et skarpere digitalt setup uden at drukne i
                    flere forskellige systemer. Vi tror paa en enklere salgsmodel, tydeligere pakker og et CMS, der er nemt at
                    komme tilbage til efter levering.
                </p>
            </div>

            <div class="about-page__grid">
                <article class="ui-card ui-card--dark about-page__story">
                    <p class="ui-kicker ui-kicker--light">Vores retning</p>
                    <h2 class="ui-title">En mere samlet leverance fra foerste klik til daglig drift.</h2>
                    <p class="about-page__story-copy">
                        Tanken er at skabe et univers, hvor hjemmeside, bookingsystem og kundelogin ikke opleves som tre separate
                        produkter, men som en samlet loesning kunden faktisk kan bruge. Det giver en mere helstøbt oplevelse udadtil
                        og et mere kontrolleret setup bag kulissen.
                    </p>

                    <ul class="ui-list about-page__story-list">
                        @foreach ($highlights as $highlight)
                            <li class="ui-list__item">
                                <span class="ui-list__dot"></span>
                                <span>{{ $highlight }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>

                <div class="about-page__stack">
                    @foreach ($pillars as $pillar)
                        <article class="ui-card about-page__note">
                            <h2 class="about-page__note-title">{{ $pillar['title'] }}</h2>
                            <p class="about-page__note-copy">{{ $pillar['copy'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section ui-section--compact">
        <div class="ui-shell">
            <div class="section-heading section-heading--split">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Samarbejde</p>
                    <h2 class="section-heading__title">Vi arbejder bedst, naar retning, pris og forventninger er tydelige fra start.</h2>
                </div>
            </div>

            @include('sales.partials.process-steps')
        </div>
    </section>
@endsection
