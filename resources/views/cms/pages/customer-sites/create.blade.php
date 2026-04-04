<x-app-layout>
    <x-slot name="header">
        <div class="section-heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">Nyt kundeprojekt</p>
                <h2 class="section-heading__title">Opret kunde og site</h2>
            </div>
        </div>
    </x-slot>

    @php($createCustomerSiteErrors = $errors->getBag('createCustomerSite'))

    <div class="dashboard-page">
        <div class="ui-shell">
            @if (session('status'))
                <div class="ui-status">
                    {{ session('status') }}
                </div>
            @endif

            <section class="ui-card dashboard-panel dashboard-panel--form">
                <p class="section-heading__kicker">Kunde + login + site</p>
                <h3 class="dashboard-panel__title">Alt det praktiske på samme side</h3>
                <p class="dashboard-feed__empty">
                    Det her laver en ny tenant, en login-bruger til kunden, et nyt website og en starter-forside + kontaktside, så vi hurtigt kan komme videre i editoren.
                </p>

                @if ($createCustomerSiteErrors->any())
                    <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                        <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                        <ul class="ui-list">
                            @foreach ($createCustomerSiteErrors->all() as $error)
                                <li class="ui-list__item">
                                    <span class="ui-list__dot"></span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('cms.dashboard.customer-sites.store') }}" class="dashboard-create-site-form">
                    @csrf
                    @include('cms.pages.customer-sites.partials.form-fields')

                    <div class="dashboard-create-site-form__actions">
                        <button type="submit" class="ui-button ui-button--success">
                            Opret kunde og site
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
