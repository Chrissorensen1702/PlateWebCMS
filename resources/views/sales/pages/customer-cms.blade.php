@extends('sales.layouts.default')

@section('title', 'Kunde-CMS')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight">
        <div class="ui-shell">
            <div class="section-heading section-heading--split">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Kunde-CMS</p>
                    <h1 class="section-heading__title">Kundelogin og CMS som en stabil del af leverancen.</h1>
                </div>

                <p class="section-heading__side">
                    Tanken er ikke at bygge en fri sidebygger, men et skarpt redigeringslag oven pa dine egne templates og custom-løsninger. Det giver kunden handlefrihed uden at koste dig designkontrol.
                </p>
            </div>

            @include('sales.partials.cms-overview')
        </div>
    </section>

    <section class="ui-section ui-section--compact">
        <div class="ui-shell marketing-support-grid">
            @foreach ($principles as $principle)
                <article class="ui-card marketing-support-card">
                    <h2 class="marketing-support-card__title">{{ $principle['title'] }}</h2>
                    <p class="marketing-support-card__copy">{{ $principle['copy'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="ui-section ui-section--compact">
        <div class="ui-shell">
            <div class="section-heading section-heading--split">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Implementering</p>
                    <h2 class="section-heading__title">Saadan passer CMS-et ind i din leverance.</h2>
                </div>
            </div>

            @include('sales.partials.process-steps')
        </div>
    </section>
@endsection
