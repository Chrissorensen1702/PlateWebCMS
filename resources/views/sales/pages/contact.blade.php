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
                        <h1 class="section-heading__title">Start med et vejledende tilbud og den pakke der passer bedst.</h1>
                    </div>

                    <p class="section-heading__copy">
                        Brug formularen som næste skridt fra prissiden. Vi tager udgangspunkt i den løsning du har valgt,
                        afklarer pris og scope hurtigt og bekræfter den endelige retning bagefter.
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
