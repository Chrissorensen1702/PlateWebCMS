<x-app-layout>
    <x-slot name="header">
        <div class="section-heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">PLATFORM PAKKER</p>
                <h2 class="section-heading__title">Administrer pakker</h2>
                <p class="section-heading__copy">
                    Opret, rediger og hold styr på de pakker der vises på salgssiden og bruges i kunde-flowet.
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $createPlanErrors = $errors->getBag('createPlan');
        $targetPlanId = old('form_target');
        $targetPlanId = is_string($targetPlanId) && str_starts_with($targetPlanId, 'plan-')
            ? substr($targetPlanId, 5)
            : '';
    @endphp

    <div class="plans-admin-page" x-data="{ openPlan: '{{ $targetPlanId }}' }">
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="plans-admin-page__layout">
                <section class="ui-card plans-admin-list">
                    <div class="plans-admin-list__header">
                        <p class="section-heading__kicker">Eksisterende pakker</p>
                        <h3 class="plans-admin-card__title">Pakker i systemet</h3>
                        <p class="plans-admin-card__copy">
                            Klik på en pakke for at åbne den i drawer’en og redigere navn, pris, features og synlighed.
                        </p>
                    </div>

                    <div class="plans-admin-list__items">
                        @forelse ($plans as $plan)
                            <button
                                type="button"
                                class="plans-admin-list__item"
                                x-on:click="openPlan = '{{ $plan->id }}'"
                            >
                                <div class="plans-admin-list__copy">
                                    <div class="plans-admin-card__top">
                                        <div>
                                            <p class="plans-admin-card__eyebrow">{{ $plan->kind === 'custom' ? 'Custom' : 'Template' }}</p>
                                            <h4 class="plans-admin-list__title">{{ $plan->name }}</h4>
                                        </div>

                                        <span class="plans-admin-card__status{{ $plan->is_active ? ' plans-admin-card__status--active' : '' }}">
                                            {{ $plan->is_active ? 'Aktiv' : 'Skjult' }}
                                        </span>
                                    </div>

                                    <p class="plans-admin-list__summary">{{ $plan->headline }}</p>

                                    <div class="plans-admin-card__meta">
                                        <span>{{ $plan->sites_count }} sites</span>
                                        <span>{{ $plan->leads_count }} leads</span>
                                        <span>{{ count($plan->features) }} features</span>
                                    </div>
                                </div>

                                <div class="plans-admin-list__aside">
                                    <span class="dashboard-feed__meta">
                                        {{ $plan->price_from ? 'Fra ' . number_format($plan->price_from, 0, ',', '.') . ' kr.' : 'Pris efter aftale' }}
                                    </span>
                                    <span class="dashboard-feed__meta">{{ $plan->slug }}</span>
                                </div>
                            </button>
                        @empty
                            <div class="plans-admin-list__empty">
                                <p class="ui-copy">Der er ikke oprettet nogle pakker endnu.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="ui-card plans-admin-card plans-admin-card--create">
                    <p class="section-heading__kicker">Ny pakke</p>
                    <h3 class="plans-admin-card__title">Opret en ny pakke</h3>
                    <p class="plans-admin-card__copy">
                        Brug samme struktur som på salgssiden, så pakkerne er lette at genbruge i onboarding og site-oprettelse.
                    </p>

                    @if ($createPlanErrors->any())
                        <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                            <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                            <ul class="ui-list">
                                @foreach ($createPlanErrors->all() as $error)
                                    <li class="ui-list__item">
                                        <span class="ui-list__dot"></span>
                                        <span>{{ $error }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('cms.plans.store') }}" class="plans-admin-form">
                        @csrf

                        @include('cms.pages.plans.partials.form-fields', [
                            'plan' => null,
                            'kindOptions' => $kindOptions,
                            'features' => old('features', ''),
                        ])

                        <div class="plans-admin-form__actions">
                            <button type="submit" class="ui-button ui-button--success">Opret pakke</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>

        <div
            class="plans-drawer"
            x-cloak
            x-show="openPlan"
            x-on:keydown.escape.window="openPlan = ''"
        >
            <div class="plans-drawer__backdrop" x-on:click="openPlan = ''"></div>

            <aside class="plans-drawer__panel" x-on:click.stop>
                @foreach ($plans as $plan)
                    @php
                        $formTarget = "plan-{$plan->id}";
                        $updateErrors = $errors->getBag('updatePlan' . $plan->id);
                        $isTargetedForm = old('form_target') === $formTarget;
                    @endphp

                    <div x-show="openPlan === '{{ $plan->id }}'" x-cloak>
                        <div class="plans-drawer__header">
                            <div>
                                <p class="plans-admin-card__eyebrow">{{ $plan->kind === 'custom' ? 'Custom' : 'Template' }}</p>
                                <h3 class="plans-admin-card__title">{{ $plan->name }}</h3>
                                <p class="plans-admin-card__copy">
                                    {{ $plan->headline }}
                                </p>
                            </div>

                            <button type="button" class="ui-button ui-button--outline plans-drawer__close" x-on:click="openPlan = ''">
                                Luk
                            </button>
                        </div>

                        <div class="plans-drawer__body">
                            <div class="plans-admin-card__meta">
                                <span>{{ $plan->sites_count }} sites</span>
                                <span>{{ $plan->leads_count }} leads</span>
                                <span>Slug: {{ $plan->slug }}</span>
                                @if ($plan->price_from)
                                    <span>Fra {{ number_format($plan->price_from, 0, ',', '.') }} kr.</span>
                                @endif
                            </div>

                            @if ($updateErrors->any() && $isTargetedForm)
                                <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                                    <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                                    <ul class="ui-list">
                                        @foreach ($updateErrors->all() as $error)
                                            <li class="ui-list__item">
                                                <span class="ui-list__dot"></span>
                                                <span>{{ $error }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('cms.plans.update', $plan) }}" class="plans-admin-form plans-admin-form--drawer">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="form_target" value="{{ $formTarget }}">

                                @include('cms.pages.plans.partials.form-fields', [
                                    'plan' => $plan,
                                    'kindOptions' => $kindOptions,
                                    'features' => $isTargetedForm ? old('features') : implode(PHP_EOL, $plan->features),
                                ])

                                <div class="plans-admin-form__actions">
                                    <button type="submit" class="ui-button ui-button--ink">Gem ændringer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </aside>
        </div>
    </div>
</x-app-layout>
