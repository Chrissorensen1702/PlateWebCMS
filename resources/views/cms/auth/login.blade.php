<x-guest-layout>
    <div class="auth-page">
        <div class="auth-page__intro">
            <p class="ui-kicker">Adgang</p>
            <h2 class="ui-title">Log ind i dit CMS</h2>
            <p class="auth-page__copy">
                Kunder kan logge ind igen her og fortsætte med deres løsning. Nye kunder kan starte på prissiden, gemme deres setup og oprette konto derfra.
            </p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="ui-field">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="ui-field__error" />
            </div>

            <div class="ui-field" x-data="{ showPassword: false }">
                <x-input-label for="password" :value="__('Password')" />

                <div class="auth-password-field">
                    <x-text-input
                        id="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="auth-password-field__input"
                    />

                    <button
                        type="button"
                        class="auth-password-field__toggle"
                        x-on:click="showPassword = !showPassword"
                        x-bind:aria-label="showPassword ? 'Skjul adgangskode' : 'Vis adgangskode'"
                        x-text="showPassword ? 'Skjul' : 'Vis'"
                    ></button>
                </div>

                <x-input-error :messages="$errors->get('password')" class="ui-field__error" />
            </div>

            <label for="remember_me" class="auth-page__remember">
                <input id="remember_me" type="checkbox" name="remember">
                <span class="auth-page__remember-label">{{ __('Remember me') }}</span>
            </label>

            <div class="auth-page__actions">
                @if (Route::has('password.request'))
                    <a class="auth-page__link" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button>
                    {{ __('Log in') }}
                </x-primary-button>
            </div>

            @if (session('sales.pending_customer_solution') && Route::has('register'))
                <p class="auth-page__helper">
                    Har du lige gemt en løsning fra prisberegneren?
                    <a class="auth-page__link" href="{{ route('register') }}">Opret konto og fortsæt her</a>
                </p>
            @endif
        </form>
    </div>
</x-guest-layout>
