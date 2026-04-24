@php($navUserName = Auth::user()->displayNameWithEmploymentRole())
@php($externalTools = Auth::user()->isDeveloper() ? [
    [
        'label' => 'Laravel Cloud',
        'href' => 'https://cloud.laravel.com/',
        'image' => asset('images/cms/dashboard-tools/laravel-cloud.png'),
    ],
    [
        'label' => 'GitHub',
        'href' => 'https://github.com/',
        'image' => asset('images/cms/dashboard-tools/github.svg'),
    ],
    [
        'label' => 'Simply',
        'href' => 'https://www.simply.com/dk/',
        'image' => asset('images/cms/dashboard-tools/simply.png'),
    ],
] : [])

<nav x-data="{ open: false }" class="cms-nav">
    <div class="ui-shell">
        <div class="cms-nav__bar">
            <div class="cms-nav__left">
                <a href="{{ route('dashboard') }}" class="brand-lockup">
                    <x-application-header-logo class="brand-lockup__wordmark" />
                </a>

                @if (! empty($navPlanChip))
                    <div class="cms-nav__plan-chip cms-nav__plan-chip--desktop" aria-label="Nuværende plan">
                        <span class="cms-nav__plan-label">{{ $navPlanChip['label'] }}</span>
                        <strong class="cms-nav__plan-value">{{ $navPlanChip['value'] }}</strong>
                    </div>
                @endif

                @if ($externalTools !== [])
                    <div class="cms-nav__external-tools" aria-label="Eksterne værktøjer">
                        @foreach ($externalTools as $tool)
                            <a
                                href="{{ $tool['href'] }}"
                                class="cms-nav__external-tool"
                                target="_blank"
                                rel="noreferrer"
                                title="{{ $tool['label'] }}"
                                aria-label="{{ $tool['label'] }}"
                            >
                                <img src="{{ $tool['image'] }}" alt="" class="cms-nav__external-tool-image">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="cms-nav__right">
                <div class="cms-nav__links">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (! Auth::user()->isDeveloper())
                        <x-nav-link :href="route('customer.solution.show')" :active="request()->routeIs('customer.solution.*')">
                            {{ __('Min løsning') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isDeveloper())
                        <x-nav-link :href="route('cms.customers.index')" :active="request()->routeIs('cms.customers.*')">
                            {{ __('Kunder') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isDeveloper())
                        <x-nav-link :href="route('cms.projects.index')" :active="request()->routeIs('cms.projects.*')">
                            {{ __('Projekter') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isDeveloper())
                        <x-nav-link :href="route('cms.leads.index')" :active="request()->routeIs('cms.leads.*')">
                            {{ __('Henvendelser') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->isDeveloper())
                        <x-nav-link :href="route('cms.orders.index')" :active="request()->routeIs('cms.orders.*')">
                            {{ __('Bestillinger') }}
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('cms.sites.index')" :active="request()->routeIs('cms.sites.*')">
                        {{ __('Sider') }}
                    </x-nav-link>

                    @if (Auth::user()->canManageTenantAccess())
                        <x-dropdown align="left" width="w-56" contentClasses="cms-nav-dropdown__content py-2 bg-white">
                            <x-slot name="trigger">
                                <button type="button" class="app-nav-link cms-nav-dropdown__trigger{{ request()->routeIs('cms.access.*') ? ' app-nav-link--active' : '' }}">
                                    <span>{{ __('Min konto') }}</span>

                                    <svg class="cms-nav-dropdown__icon" :class="{ 'cms-nav-dropdown__icon--open': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="cms-nav-dropdown__heading">
                                    <p class="cms-nav-dropdown__title">Min konto</p>
                                    <p class="cms-nav-dropdown__copy">Styr team og tenant-adgange.</p>
                                </div>

                                <x-dropdown-link :href="route('cms.access.index')">
                                    {{ __('Adgange') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endif

                </div>

                <div class="cms-nav__user">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="cms-user-button">
                                <div>{{ $navUserName }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="cms-profile-menu">
                                <p class="cms-profile-menu__name">{{ $navUserName }}</p>
                                <p class="cms-profile-menu__email">{{ Auth::user()->email }}</p>
                            </div>

                            <x-dropdown-link :href="route('home')">
                                {{ __('Public Site') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Min profil') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}" class="cms-profile-menu__logout">
                                @csrf

                                <button type="submit" class="ui-button ui-button--danger cms-logout-button cms-logout-button--dropdown">
                                    Log ud
                                </button>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="cms-nav__toggle">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

        <div :class="{'block': open, 'hidden': ! open}" class="cms-nav__mobile hidden sm:hidden">
        <div class="cms-nav__mobile-links">
            @if ($externalTools !== [])
                <div class="cms-nav__mobile-external-tools">
                    @foreach ($externalTools as $tool)
                        <a
                            href="{{ $tool['href'] }}"
                            class="cms-nav__mobile-external-tool"
                            target="_blank"
                            rel="noreferrer"
                        >
                            <img src="{{ $tool['image'] }}" alt="" class="cms-nav__mobile-external-tool-image">
                            <span>{{ $tool['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (! Auth::user()->isDeveloper())
                <x-responsive-nav-link :href="route('customer.solution.show')" :active="request()->routeIs('customer.solution.*')">
                    {{ __('Min løsning') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isDeveloper())
                <x-responsive-nav-link :href="route('cms.customers.index')" :active="request()->routeIs('cms.customers.*')">
                    {{ __('Kunder') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isDeveloper())
                <x-responsive-nav-link :href="route('cms.projects.index')" :active="request()->routeIs('cms.projects.*')">
                    {{ __('Projekter') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isDeveloper())
                <x-responsive-nav-link :href="route('cms.leads.index')" :active="request()->routeIs('cms.leads.*')">
                    {{ __('Henvendelser') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->isDeveloper())
                <x-responsive-nav-link :href="route('cms.orders.index')" :active="request()->routeIs('cms.orders.*')">
                    {{ __('Bestillinger') }}
                </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('cms.sites.index')" :active="request()->routeIs('cms.sites.*')">
                {{ __('Sider') }}
            </x-responsive-nav-link>

            @if (Auth::user()->canManageTenantAccess())
                <div class="cms-nav__mobile-group">
                    <p class="cms-nav__mobile-group-title">Min konto</p>

                    <x-responsive-nav-link :href="route('cms.access.index')" :active="request()->routeIs('cms.access.*')">
                        {{ __('Adgange') }}
                    </x-responsive-nav-link>
                </div>
            @endif

            @if (! empty($navPlanChip))
                <div class="cms-nav__mobile-plan">
                    <p class="cms-nav__mobile-group-title">{{ $navPlanChip['label'] }}</p>
                    <div class="cms-nav__mobile-plan-value">{{ $navPlanChip['value'] }}</div>
                </div>
            @endif

        </div>

        <div class="cms-nav__mobile-user">
            <div class="cms-nav__mobile-user-copy">
                <div class="cms-nav__mobile-name">{{ $navUserName }}</div>
                <div class="cms-nav__mobile-email">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('logout') }}" class="cms-nav__mobile-logout">
                    @csrf

                    <button type="submit" class="ui-button ui-button--danger cms-logout-button cms-logout-button--mobile">
                        Log ud
                    </button>
                </form>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Min profil') }}
                </x-responsive-nav-link>
            </div>
        </div>
    </div>
</nav>
