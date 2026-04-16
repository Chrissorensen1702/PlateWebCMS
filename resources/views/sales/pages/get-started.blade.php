@extends('sales.layouts.default')

@section('title', 'Kom i gang')
@section('body-class', 'marketing-body marketing-body--get-started')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight get-started-page">
        <div class="ui-shell get-started-page__shell">
            @php($flowRows = array_chunk($flowSteps, 3))
            @php($arrowSequence = 0)

            <header class="get-started-page__intro">
                <h1 class="get-started-page__title">Det skal være hurtigt, enkelt og godt for forretningen.</h1>
            </header>

            <div class="get-started-diagram" aria-label="Flow for at komme i gang">
                @foreach ($flowRows as $rowIndex => $row)
                    <div class="get-started-diagram__row">
                        @foreach ($row as $step)
                            @php($stepNumber = ($rowIndex * 3) + $loop->iteration)
                            @php($isFinalStep = $stepNumber === count($flowSteps))

                            <article
                                class="ui-card get-started-box{{ $isFinalStep ? ' get-started-box--final' : '' }}"
                                data-step="{{ $stepNumber }}"
                            >
                                @if ($isFinalStep)
                                    <span class="get-started-box__status">Faerdig</span>
                                @endif

                                <p class="get-started-box__eyebrow">{{ $step['eyebrow'] }}</p>
                                <h2 class="get-started-box__title">{{ $step['title'] }}</h2>
                                <p class="get-started-box__copy">{{ $step['copy'] }}</p>

                                @if (! empty($step['action']))
                                    <a href="{{ $step['action']['href'] }}" class="ui-button ui-button--outline get-started-box__action">
                                        {{ $step['action']['label'] }}
                                    </a>
                                @endif
                            </article>

                            @if (! $loop->last)
                                @php($currentArrowSequence = $arrowSequence++)

                                <div class="get-started-diagram__arrow" aria-hidden="true" style="--arrow-order: {{ $currentArrowSequence }};">
                                    <span></span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if ($rowIndex < count($flowRows) - 1)
                        @php($currentArrowSequence = $arrowSequence++)

                        <div class="get-started-diagram__transition" aria-hidden="true" style="--arrow-order: {{ $currentArrowSequence }};">
                            <svg
                                class="get-started-diagram__transition-svg"
                                viewBox="0 0 1200 220"
                                preserveAspectRatio="none"
                                aria-hidden="true"
                            >
                                <defs>
                                    <marker
                                        id="get-started-transition-arrow"
                                        markerWidth="10"
                                        markerHeight="10"
                                        refX="7"
                                        refY="5"
                                        orient="auto"
                                        markerUnits="userSpaceOnUse"
                                    >
                                        <path d="M0,0 L10,5 L0,10" class="get-started-diagram__transition-marker"></path>
                                    </marker>
                                </defs>
                                <path
                                    class="get-started-diagram__transition-path"
                                    d="M1074 30
                                       L1142 30
                                       L1142 146
                                       L54 146
                                       L54 196
                                       L106 196"
                                    marker-end="url(#get-started-transition-arrow)"
                                />
                            </svg>

                            <span class="get-started-diagram__transition-mobile"></span>
                        </div>
                    @endif
                @endforeach

            </div>
        </div>
    </section>
@endsection
