<div class="plans-grid">
    @foreach ($plans as $plan)
        <article class="ui-card ui-card--hover plan-card">
            <div class="plan-card__header">
                <div>
                    <p class="plan-card__kind">{{ $plan->kind }}</p>
                    <h3 class="plan-card__name">{{ $plan->name }}</h3>
                </div>

                @if ($plan->is_custom)
                    <span class="plan-card__tag plan-card__tag--custom">Flex</span>
                @else
                    <span class="plan-card__tag plan-card__tag--template">Template</span>
                @endif
            </div>

            <p class="plan-card__headline">{{ $plan->headline }}</p>
            <p class="plan-card__summary">{{ $plan->summary }}</p>

            <div class="plan-card__meta">
                <div>
                    <p class="plan-card__meta-label">Fra pris</p>
                    <p class="plan-card__price">
                        {{ $plan->price_from ? number_format($plan->price_from, 0, ',', '.') . ' kr' : 'Efter tilbud' }}
                    </p>
                </div>

                <div>
                    <p class="plan-card__meta-label">Levering</p>
                    <p class="plan-card__delivery">{{ $plan->build_time }}</p>
                </div>
            </div>

            <div class="plan-card__features">
                <ul class="ui-list">
                    @foreach ($plan->features ?? [] as $feature)
                        <li class="ui-list__item">
                            <span class="ui-list__dot"></span>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </article>
    @endforeach
</div>
