<div class="pricing-compare">
    <div class="pricing-compare__scroll">
        <div class="pricing-compare__table">
            <div class="pricing-compare__head pricing-compare__row">
                <div class="pricing-compare__feature pricing-compare__feature--head">Funktioner</div>

                @foreach ($packages as $package)
                    <div class="pricing-compare__plan{{ $package['featured'] ? ' pricing-compare__plan--featured' : '' }}" x-bind:class="planRecommendationClasses('{{ $package['key'] }}')">
                        {{ $package['title'] }}
                    </div>
                @endforeach
            </div>

            @foreach ($comparisonRows as $row)
                <div class="pricing-compare__row">
                    <div class="pricing-compare__feature">{{ $row['label'] }}</div>

                    @foreach ($packages as $package)
                        @php($value = $row['values'][$package['key']] ?? false)

                        <div class="pricing-compare__value{{ $package['featured'] ? ' pricing-compare__value--featured' : '' }}" x-bind:class="valueRecommendationClasses('{{ $package['key'] }}')">
                            @if ($value === true)
                                <span class="pricing-compare__icon pricing-compare__icon--yes" aria-label="Inkluderet">✓</span>
                            @elseif ($value === false)
                                <span class="pricing-compare__icon pricing-compare__icon--no" aria-label="Ikke inkluderet">–</span>
                            @else
                                <span class="pricing-compare__text">{{ $value }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
