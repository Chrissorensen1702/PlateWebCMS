@extends('sales.layouts.default')

@section('title', 'Templates')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight">
        <div class="ui-shell">
            <div class="section-heading section-heading--split">
                <div class="section-heading__content">
                    <p class="section-heading__kicker">Templates</p>
                    <h1 class="section-heading__title">Template-pakker der er hurtige at saelge og levere.</h1>
                </div>

                <p class="section-heading__side">
                    Her samler du de standardiserede pakker, hvor design, sektioner og workflow er gennemarbejdet pa forhaand. Det giver dig fart, mens kunden stadig faar et professionelt CMS-setup.
                </p>
            </div>

            @include('sales.partials.plans-grid', ['plans' => $plans])
        </div>
    </section>

    <section class="ui-section ui-section--compact">
        <div class="ui-shell marketing-support-grid">
            @foreach ($notes as $note)
                <article class="ui-card marketing-support-card">
                    <h2 class="marketing-support-card__title">{{ $note['title'] }}</h2>
                    <p class="marketing-support-card__copy">{{ $note['copy'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
