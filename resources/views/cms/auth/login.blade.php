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
            <section class="auth-login-panel">
                <div class="auth-login-brand">
                    <a href="{{ route('home') }}" class="auth-login-brand__link">
                        <x-application-header-logo class="auth-login-brand__logo" />
                    </a>
                </div>

                <div class="auth-login-card">
                    <div class="auth-login-copy">
                        <p class="auth-login-eyebrow">Kunde-CMS</p>
                        <h1>Log ind i dit CMS</h1>
                        <p class="auth-login-text">
                            Log ind og fortsæt med din løsning, rediger de frigivne sektioner
                            og hold styr på indholdet i dit kunde-CMS.
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
                            <p>Adgang til kunde-CMS administreres på din konto.</p>
                            <p>Har du lige gemt en løsning fra prisberegneren, kan du logge ind og fortsætte her.</p>

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
                </div>
            </section>
        </main>
    </body>
</html>
