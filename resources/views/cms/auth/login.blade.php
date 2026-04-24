<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CMS Studio') }} | Kundelogin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|space-grotesk:400,500,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/cms/auth.css', 'resources/js/cms/app.js'])
    </head>
    <body class="auth-login-body antialiased">
        <main class="auth-login-page">
            <section class="auth-login-shell">
                <section class="auth-login-column auth-login-column--form">
                    <div class="auth-login-column__inner">
                        <div class="auth-login-card-shell">
                            <div class="auth-login-card-topbar">
                                <a href="{{ route('home') }}" class="auth-login-brand__link">
                                    <img
                                        src="{{ asset('images/logo/plateweb-sales.svg') }}"
                                        alt="PlateWeb"
                                        class="auth-login-brand__logo"
                                    >
                                </a>

                                <a href="{{ route('home') }}" class="auth-login-back-link">
                                    Tilbage til forsiden
                                </a>
                            </div>

                            <section class="auth-login-card">
                                <div class="auth-login-card__header">
                                    <p class="auth-login-eyebrow">Kundelogin</p>
                                    <h2>Velkommen tilbage</h2>
                                    <p class="auth-login-text">
                                        Log ind med din e-mail og adgangskode for at fortsætte i dit CMS.
                                    </p>
                                </div>

                                @if (session('status'))
                                    <div class="auth-login-alert auth-login-alert--success" role="status">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="auth-login-alert" role="alert">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" class="auth-login-form">
                                    @csrf

                                    <label class="auth-login-field" for="email">
                                        <span>E-mail</span>
                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            placeholder="navn@firma.dk"
                                            autocomplete="username"
                                            required
                                            autofocus
                                        >
                                        <x-input-error :messages="$errors->get('email')" class="ui-field__error" />
                                    </label>

                                    <label class="auth-login-field" for="password">
                                        <span>Adgangskode</span>
                                        <div class="auth-login-password-wrap" x-data="{ showPassword: false }">
                                            <input
                                                id="password"
                                                x-bind:type="showPassword ? 'text' : 'password'"
                                                type="password"
                                                name="password"
                                                placeholder="Indtast adgangskode"
                                                autocomplete="current-password"
                                                required
                                            >
                                            <button
                                                type="button"
                                                class="auth-login-password-toggle"
                                                x-on:click="showPassword = !showPassword"
                                                x-bind:aria-label="showPassword ? 'Skjul adgangskode' : 'Vis adgangskode'"
                                                x-text="showPassword ? 'Skjul' : 'Vis'"
                                            ></button>
                                        </div>
                                        <x-input-error :messages="$errors->get('password')" class="ui-field__error" />
                                    </label>

                                    <label for="remember_me" class="auth-login-check">
                                        <input id="remember_me" type="checkbox" name="remember" @checked(old('remember'))>
                                        <span>Husk mig</span>
                                    </label>

                                    <div class="auth-login-actions">
                                        <button type="submit" class="auth-login-button">
                                            Log ind
                                        </button>

                                        @if (Route::has('password.request'))
                                            <a class="auth-login-link" href="{{ route('password.request') }}">
                                                Glemt din adgangskode?
                                            </a>
                                        @endif
                                    </div>

                                    <div class="auth-login-help">
                                        <p>Har du allerede været i gang med din løsning?</p>
                                        <p>Log ind for at fortsætte, eller opret konto hvis du ikke har adgang endnu.</p>

                                        @if (session('sales.pending_customer_solution') && Route::has('register'))
                                            <a class="auth-login-public-link" href="{{ route('register') }}">
                                                Opret konto og fortsæt din løsning
                                            </a>
                                        @else
                                            <a class="auth-login-public-link" href="{{ route('sales.customer-cms') }}">
                                                Læs mere om kunde-CMS
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </section>
                        </div>
                    </div>
                </section>

                <aside class="auth-login-column auth-login-column--visual" aria-hidden="true">
                    <div class="auth-login-visual-stage">
                        <div
                            class="auth-login-lottie"
                            data-lottie-src="{{ asset('lotties/lottie-test2.json') }}"
                            data-lottie-loop="true"
                        ></div>
                    </div>
                </aside>
            </section>
        </main>
    </body>
</html>
