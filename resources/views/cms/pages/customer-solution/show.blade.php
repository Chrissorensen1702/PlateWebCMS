<x-app-layout>
    <x-slot name="header">
        <div class="section-heading customer-solution-page__heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">MIN LØSNING</p>
                <h2 class="section-heading__title">Gem din retning og vend tilbage når det passer dig</h2>
            </div>
        </div>
    </x-slot>

    @php
        $metricLabels = [
            'locations' => 'Lokationer',
            'staff' => 'Medarbejdere',
            'bookings' => 'Bookinger/år',
            'sections' => ($resolvedSolution['package_key'] ?? null) === 'launch' ? 'Sider' : 'Sektioner',
            'traffic_tier' => 'Forventet trafik',
            'lead_module' => 'Nyhedsbrev / leadmodul',
            'seo_copy' => 'Professionel opsætning',
        ];

        $metricValues = [
            'locations' => $resolvedSolution['locations'],
            'staff' => $resolvedSolution['staff'],
            'bookings' => number_format((int) $resolvedSolution['bookings'], 0, ',', '.'),
            'sections' => $resolvedSolution['sections'],
            'traffic_tier' => match ($resolvedSolution['traffic_tier'] ?? 'low') {
                'high' => 'Høj',
                'medium' => 'Mellem',
                default => 'Lav',
            },
            'lead_module' => ! empty($resolvedSolution['lead_module']) ? 'Ja' : 'Nej',
            'seo_copy' => ! empty($resolvedSolution['seo_copy']) ? 'Ja' : 'Nej',
        ];
    @endphp

    <div class="customer-solution-page">
        <div class="ui-shell customer-solution-page__shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="customer-solution-page__grid">
                <section class="ui-card ui-card--dark customer-solution-hero">
                    @if ($hasSavedSolution)
                        <p class="ui-kicker ui-kicker--light">Din gemte løsning</p>
                        <h3 class="ui-title">{{ $resolvedSolution['title'] }}</h3>
                        <p class="customer-solution-hero__price">{{ $resolvedSolution['price'] }}</p>
                        <p class="customer-solution-hero__note">{{ $resolvedSolution['price_note'] }}</p>
                        <p class="customer-solution-hero__detail">{{ $resolvedSolution['detail'] }}</p>

                        <div class="customer-solution-hero__actions">
                            <a href="{{ $adjustHref }}" class="ui-button ui-button--light">
                                Gå til prissiden
                            </a>
                            <a href="{{ $contactHref }}" class="ui-button ui-button--light-outline">
                                Kontakt os om næste skridt
                            </a>
                        </div>
                    @else
                        <p class="ui-kicker ui-kicker--light">Ingen løsning endnu</p>
                        <h3 class="ui-title">Se vores faste pakker</h3>
                        <p class="customer-solution-hero__detail">
                            PlateWeb er nu samlet i faste pakker. Gå til prissiden og se hvilken retning der passer bedst til dit setup, før du går videre.
                        </p>

                        <div class="customer-solution-hero__actions">
                            <a href="{{ route('templates') }}" class="ui-button ui-button--light">
                                Gå til prissiden
                            </a>
                        </div>
                    @endif
                </section>

                <div class="customer-solution-page__stack">
                    <section class="ui-card customer-solution-panel">
                        <p class="section-heading__kicker">Overblik</p>
                        <h3 class="customer-solution-panel__title">Det du har gemt på kontoen</h3>

                        <div class="customer-solution-metrics">
                            @foreach ($resolvedSolution['visible_fields'] as $field)
                                <article class="customer-solution-metric">
                                    <p class="customer-solution-metric__label">{{ $metricLabels[$field] ?? $field }}</p>
                                    <p class="customer-solution-metric__value">{{ $metricValues[$field] ?? '-' }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <section class="ui-card customer-solution-panel">
                        <p class="section-heading__kicker">Indhold</p>
                        <h3 class="customer-solution-panel__title">Det følger med i løsningen</h3>

                        <ul class="customer-solution-points">
                            @foreach (($resolvedSolution['points'] ?? []) as $point)
                                @php($pointLabel = is_array($point) ? ($point['label'] ?? '') : $point)
                                @php($pointNote = is_array($point) ? ($point['note'] ?? null) : null)
                                @php($pointNoteLabel = is_array($pointNote) ? ($pointNote['label'] ?? null) : $pointNote)
                                <li class="customer-solution-points__item">
                                    {{ $pointLabel }}

                                    @if ($pointNoteLabel)
                                        <small>{{ $pointNoteLabel }}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
