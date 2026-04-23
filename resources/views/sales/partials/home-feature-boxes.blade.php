<div class="home-feature-grid">
    @foreach ($features as $feature)
        @php($isFeatured = (bool) ($feature['featured'] ?? false))
        <article
            class="ui-card home-feature-card home-feature-card--{{ $loop->iteration }}{{ $isFeatured ? ' home-feature-card--featured' : '' }}"
        >
            <div class="home-feature-card__meta">
                <p class="home-feature-card__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                @if ($isFeatured)
                    <p class="home-feature-card__tag">Kernen i løsningen</p>
                @endif
            </div>

            <div class="home-feature-card__header">
                <p class="home-feature-card__label">Det får du</p>
                <h3 class="home-feature-card__title">{{ $feature['title'] }}</h3>
                @if (! empty($feature['summary'] ?? null))
                    <p class="home-feature-card__summary">{{ $feature['summary'] }}</p>
                @endif
            </div>

            <ul class="home-feature-card__points" aria-label="{{ $feature['title'] }}">
                @foreach ($feature['points'] as $point)
                    <li class="home-feature-card__point">{{ $point }}</li>
                @endforeach
            </ul>

            @if (! empty($feature['href'] ?? null))
                <a href="{{ $feature['href'] }}" class="ui-button {{ $isFeatured ? 'ui-button--ink home-feature-card__action--featured' : 'ui-button--outline' }} home-feature-card__action">
                    {{ $feature['cta_label'] ?? ($isFeatured ? 'Se bookingsystem' : 'Læs mere') }}
                </a>
            @endif
        </article>
    @endforeach
</div>
