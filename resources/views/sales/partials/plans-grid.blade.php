<div class="pricing-grid">
    @foreach ($packages as $package)
        <article id="pricing-package-{{ $package['key'] }}" class="ui-card ui-card--hover package-card package-card--{{ $package['tone'] }}{{ $package['featured'] ? ' package-card--featured' : '' }}" x-bind:class="packageCardClasses('{{ $package['key'] }}')">
            <div class="package-card__top">
                <span class="package-card__badge">{{ $package['badge'] }}</span>
                <span class="package-card__badge package-card__badge--recommended" x-cloak x-show="isRecommended('{{ $package['key'] }}')" x-transition.opacity.duration.250ms>Anbefalet</span>
            </div>

            <div class="package-card__heading">
                <h2 class="package-card__title">{{ $package['title'] }}</h2>

                <div class="package-card__price-block">
                    <p
                        class="package-card__price"
                        x-text="annualBilling ? {{ Illuminate\Support\Js::from($package['annual_price']) }} : {{ Illuminate\Support\Js::from($package['price']) }}"
                    >{{ $package['price'] }}</p>
                    <p
                        class="package-card__price-note"
                        x-text="annualBilling ? {{ Illuminate\Support\Js::from($package['annual_suffix']) }} : {{ Illuminate\Support\Js::from($package['price_suffix']) }}"
                    >{{ $package['price_suffix'] }}</p>
                    <p class="package-card__delivery">{{ $package['delivery'] }}</p>
                </div>
            </div>

            <p class="package-card__headline">{{ $package['headline'] }}</p>

            <a href="{{ $package['href'] }}" class="ui-button {{ $package['featured'] ? 'ui-button--light' : 'ui-button--ink' }} package-card__action">
                {{ $package['label'] }}
            </a>

            <ul class="package-card__points">
                @foreach ($package['points'] as $point)
                    <li class="package-card__point">{{ $point }}</li>
                @endforeach
            </ul>
        </article>
    @endforeach
</div>
