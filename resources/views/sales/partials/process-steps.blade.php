<div class="feature-story">
    <div class="feature-story__intro">
        <p class="feature-story__eyebrow">Tre dele. Et samlet flow.</p>
        <p class="feature-story__copy">
            I stedet for tre løse værktøjer får du én løsning, hvor hjemmesiden skaffer opmærksomhed, bookingen konverterer og CMS'et holder siden levende.
        </p>
        <div class="feature-story__trail" aria-hidden="true">
            <span>Website</span>
            <span>Booking</span>
            <span>CMS</span>
        </div>
    </div>

    <div class="feature-story__cards">
        @foreach ($steps as $step)
            <article class="ui-card feature-story-card feature-story-card--{{ $loop->iteration }}">
                <div class="feature-story-card__top">
                    <p class="feature-story-card__number">{{ $step['eyebrow'] }}</p>
                    <p class="feature-story-card__tag">{{ $step['tag'] ?? '' }}</p>
                </div>

                <div class="feature-story-card__visual" aria-hidden="true">
                    @if ($loop->iteration === 1)
                        <div class="feature-graphic feature-graphic--browser">
                            <div class="feature-graphic__browser-top">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="feature-graphic__browser-body">
                                <div class="feature-graphic__hero-bar"></div>
                                <div class="feature-graphic__hero-card"></div>
                                <div class="feature-graphic__browser-grid">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    @elseif ($loop->iteration === 2)
                        <div class="feature-graphic feature-graphic--booking">
                            <div class="feature-graphic__booking-sidebar">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="feature-graphic__booking-board">
                                <div class="feature-graphic__booking-row feature-graphic__booking-row--green"></div>
                                <div class="feature-graphic__booking-row feature-graphic__booking-row--purple"></div>
                                <div class="feature-graphic__booking-row feature-graphic__booking-row--yellow"></div>
                            </div>
                        </div>
                    @else
                        <div class="feature-graphic feature-graphic--cms">
                            <div class="feature-graphic__cms-header"></div>
                            <div class="feature-graphic__cms-grid">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="feature-graphic__cms-pill"></div>
                        </div>
                    @endif
                </div>

                <div class="feature-story-card__body">
                    <h3 class="feature-story-card__title">{{ $step['title'] }}</h3>
                    <p class="feature-story-card__copy">{{ $step['copy'] }}</p>
                </div>

                @if (! empty($step['highlight'] ?? null))
                    <div class="feature-story-card__highlight">
                        <span class="feature-story-card__pulse" aria-hidden="true"></span>
                        <span>{{ $step['highlight'] }}</span>
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</div>
