<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CMS Studio') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|space-grotesk:400,500,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/cms/auth.css', 'resources/js/cms/app.js'])
    </head>
    <body class="auth-body antialiased">
        <div class="auth-shell">
            <div class="auth-shell__inner">
                <div class="auth-shell__grid">
                    <aside class="ui-card ui-card--dark auth-shell__aside">
                        <a href="{{ route('home') }}" class="brand-lockup brand-lockup--light">
                            <x-application-header-logo class="brand-lockup__wordmark" />
                        </a>

                        <div class="auth-shell__copy">
                            <p class="ui-kicker ui-kicker--light">Kundelogin</p>
                            <h1 class="ui-title">Log ind og rediger de dele af siden, der er gjort klar til kunden.</h1>
                            <p class="auth-shell__copy-text">
                                Denne side er til eksisterende kunder og interne brugere. Nye henvendelser kommer ind via den offentlige salgsside.
                            </p>
                        </div>

                        <a href="{{ route('home') }}" class="ui-button ui-button--light-outline">
                            Tilbage til salgssiden
                        </a>
                    </aside>

                    <div class="auth-shell__panel">
                        <div class="ui-card auth-shell__panel-inner">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
