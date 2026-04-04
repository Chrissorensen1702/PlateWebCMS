@extends('sales.layouts.default')

@section('title', 'Kontakt')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight">
        <div class="ui-shell contact-grid">
            <div class="contact-copy">
                <div class="section-heading">
                    <div class="section-heading__content">
                        <p class="section-heading__kicker">Kontakt</p>
                        <h1 class="section-heading__title">Lad os finde den rigtige pakke til projektet.</h1>
                    </div>

                    <p class="section-heading__copy">
                        Uanset om kunden peger mod template eller custom build, er formularen her dit faste startpunkt. Den gemmer leadet i databasen og giver os et klart sted at tage dialogen videre fra.
                    </p>
                </div>

                @if (session('status'))
                    <div class="ui-status contact-copy__flash">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="contact-sidebar">
                    @foreach ($contactPoints as $point)
                        <article class="ui-card contact-side-card">
                            <h2 class="contact-side-card__title">{{ $point['title'] }}</h2>
                            <p class="contact-side-card__copy">{{ $point['copy'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            @include('sales.partials.contact-form')
        </div>
    </section>
@endsection
