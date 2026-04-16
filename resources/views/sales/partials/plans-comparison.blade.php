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
                    <div class="pricing-compare__feature">
                        <span class="pricing-compare__feature-copy">{{ $row['label'] }}</span>

                        @if (! empty($row['note']))
                            @php($noteMeta = is_array($row['note']) ? $row['note'] : ['label' => (string) $row['note']])

                            <span class="pricing-compare__feature-note">
                                <span
                                    class="pricing-note pricing-note--align-start"
                                    x-data="{ open: false }"
                                    x-on:click.outside="open = false"
                                    x-on:keydown.escape.window="open = false"
                                >
                                    <span class="pricing-note__label">{{ $noteMeta['label'] ?? '' }}</span>

                                    @if (! empty($noteMeta['tiers']))
                                        <span class="pricing-note__popover-wrap">
                                            <button
                                                type="button"
                                                class="pricing-note__trigger"
                                                x-bind:aria-expanded="open.toString()"
                                                aria-label="Vis SMS-priser"
                                                x-on:click="open = ! open"
                                            >
                                                ?
                                            </button>

                                            <div
                                                class="pricing-note__popover"
                                                x-cloak
                                                x-show="open"
                                                x-transition.opacity.duration.150ms
                                                style="display: none;"
                                            >
                                                <p class="pricing-note__popover-title">{{ $noteMeta['title'] ?? 'SMS-priser' }}</p>

                                                @if (! empty($noteMeta['caption']))
                                                    <p class="pricing-note__popover-copy">{{ $noteMeta['caption'] }}</p>
                                                @endif

                                                <div class="pricing-note__tiers">
                                                    @foreach ($noteMeta['tiers'] as $tier)
                                                        <div class="pricing-note__tier">
                                                            <span class="pricing-note__tier-range">{{ $tier['range'] ?? '' }}</span>
                                                            <span class="pricing-note__tier-price">{{ $tier['price'] ?? '' }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </span>
                                    @endif
                                </span>
                            </span>
                        @endif
                    </div>

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
