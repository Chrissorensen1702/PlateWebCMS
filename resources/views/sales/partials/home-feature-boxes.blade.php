<div class="home-feature-grid">
    @foreach ($features as $feature)
        <article class="ui-card home-feature-card home-feature-card--{{ $loop->iteration }}" data-reveal style="--reveal-delay: {{ 100 + ($loop->index * 90) }}ms;">
            <div class="home-feature-card__header">
                @if (($feature['logo'] ?? null) === 'platebook')
                    <span class="home-feature-card__logo home-feature-card__logo--platebook" aria-hidden="true">
                        <span class="home-feature-card__logo-badge">
                            <img src="{{ asset('images/logo/platebook-app-logo-concept.svg') }}" alt="" class="home-feature-card__logo-image">
                        </span>
                    </span>
                @else
                    <span class="home-feature-card__emoji" aria-hidden="true">{{ $feature['emoji'] ?? '✨' }}</span>
                @endif
                <h3 class="home-feature-card__title">{{ $feature['title'] }}</h3>
            </div>

            <ul class="home-feature-card__points" aria-label="{{ $feature['title'] }}">
                @foreach ($feature['points'] as $point)
                    <li class="home-feature-card__point">{{ $point }}</li>
                @endforeach
            </ul>

            @if (! empty($feature['href'] ?? null))
                <a href="{{ $feature['href'] }}" class="ui-button ui-button--outline home-feature-card__action">
                    Læs mere
                </a>
            @endif
        </article>
    @endforeach
</div>
