@extends('sales.layouts.default')

@section('title', 'Forside')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    @include('sales.partials.home-hero')

    <section class="marketing-process-section">
        <div class="marketing-process-section__shell">
            <div class="section-heading section-heading--split marketing-process-heading" data-reveal style="--reveal-delay: 40ms;">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Vores funktioner</p>
                    <h2 class="section-heading__title marketing-process-heading__title">Vi har samlet alt i én løsning<br>så du kan bruge tiden på at skabe omsætning</h2>
                </div>
            </div>

            @include('sales.partials.home-feature-boxes', ['features' => $homeFeatures])
        </div>
    </section>
@endsection
