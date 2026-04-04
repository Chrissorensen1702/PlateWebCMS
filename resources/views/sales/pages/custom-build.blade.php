@extends('sales.layouts.default')

@section('title', 'Custom build')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight">
        <div class="ui-shell">
            <div class="section-heading section-heading--split">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Custom build</p>
                    <h1 class="section-heading__title">Custom builds med et skraeddersyet udtryk og samme CMS-kerne.</h1>
                </div>

                <p class="section-heading__side">
                    Naar projektet kraever et mere unikt visuelt udtryk eller saerlige funktioner, bygger du frit i frontenden uden at slippe den fordel, det giver at have et faelles kundelogin og et kontrolleret CMS.
                </p>
            </div>

            <div class="custom-build-grid">
                <article class="ui-card ui-card--dark custom-build-story">
                    <p class="ui-kicker ui-kicker--light">Projektspor</p>
                    <h2 class="ui-title">{{ $plan?->name ?? 'Custom Build' }}</h2>
                    <p class="custom-build-story__copy">
                        {{ $plan?->summary ?? 'Et unikt site bygget fra bunden, stadig koblet til dit eget kontrollerede kundelogin.' }}
                    </p>

                    <div class="custom-build-story__meta">
                        <article class="custom-build-story__meta-item">
                            <span>Fra pris</span>
                            <strong>{{ $plan?->price_from ? number_format($plan->price_from, 0, ',', '.') . ' kr' : 'Efter tilbud' }}</strong>
                        </article>

                        <article class="custom-build-story__meta-item">
                            <span>Levering</span>
                            <strong>{{ $plan?->build_time ?? 'Efter scope' }}</strong>
                        </article>
                    </div>

                    <ul class="ui-list custom-build-story__list">
                        @foreach (($plan?->features ?? []) as $feature)
                            <li class="ui-list__item">
                                <span class="ui-list__dot"></span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>

                <div class="custom-build-stack">
                    @foreach ($pillars as $pillar)
                        <article class="ui-card custom-build-note">
                            <h2 class="custom-build-note__title">{{ $pillar['title'] }}</h2>
                            <p class="custom-build-note__copy">{{ $pillar['copy'] }}</p>
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
                    <h2 class="section-heading__title">Custom-projekter kan stadig foelge et enkelt og tydeligt flow.</h2>
                </div>
            </div>

            @include('sales.partials.process-steps')
        </div>
    </section>
@endsection
