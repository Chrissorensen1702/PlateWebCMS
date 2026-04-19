<div class="home-feature-grid">
    @foreach ($features as $feature)
        @php($isFeatured = ($feature['logo'] ?? null) === 'platebook')
        <article
            class="ui-card home-feature-card home-feature-card--{{ $loop->iteration }}{{ $isFeatured ? ' home-feature-card--featured' : '' }}"
            data-reveal
            style="--reveal-delay: {{ 100 + ($loop->index * 90) }}ms;"
        >
            @if ($isFeatured)
                <p class="home-feature-card__badge">Vores stærkeste forskel</p>
            @endif

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
                @if ($isFeatured)
                    <p class="home-feature-card__summary">Lad kunder booke direkte på din egen hjemmeside uden en ekstern bookingside.</p>
                @endif
            </div>

            <ul class="home-feature-card__points" aria-label="{{ $feature['title'] }}">
                @foreach ($feature['points'] as $point)
                    <li class="home-feature-card__point">{{ $point }}</li>
                @endforeach
            </ul>

            @if (! empty($feature['href'] ?? null))
                <a href="{{ $feature['href'] }}" class="ui-button {{ $isFeatured ? 'ui-button--ink home-feature-card__action--featured' : 'ui-button--outline' }} home-feature-card__action">
                    {{ $isFeatured ? 'Se bookingsystem' : 'Læs mere' }}
                </a>
            @endif
        </article>
    @endforeach
</div>
