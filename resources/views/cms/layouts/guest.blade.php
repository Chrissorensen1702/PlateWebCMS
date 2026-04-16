<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $isRegisterPage = request()->routeIs('register');
        $asideKicker = $isRegisterPage ? 'Kom i gang' : 'Kundelogin';
        $asideTitle = $isRegisterPage
            ? 'Opret konto og gem din løsning, så du kan fortsætte uden at starte forfra.'
            : 'Log ind og rediger de dele af siden, der er gjort klar til kunden.';
        $asideCopy = $isRegisterPage
            ? 'Når kontoen er oprettet, kan du vende tilbage til din løsning, følge den videre i CMS-flowet og komme hurtigt tilbage senere.'
            : 'Eksisterende kunder kan logge ind her, og nye kunder kan starte på prissiden, gemme deres løsning og oprette konto bagefter.';
        $asideButtonHref = $isRegisterPage ? route('templates') : route('home');
        $asideButtonLabel = $isRegisterPage ? 'Se priser og pakker' : 'Tilbage til salgssiden';
        $asideHighlights = $isRegisterPage
            ? [
                'Gem dit setup og fortsæt senere',
                'Få adgang til kundelogin og CMS',
                'Behold overblik over pris og løsning ét sted',
            ]
            : [
                'Log ind og rediger sikkert i dit CMS',
                'Fortsæt med eksisterende løsninger',
                'Bygget til websites med klare kundeflows',
            ];
    @endphp
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
                            <p class="ui-kicker ui-kicker--light">{{ $asideKicker }}</p>
                            <h1 class="ui-title">{{ $asideTitle }}</h1>
                            <p class="auth-shell__copy-text">
                                {{ $asideCopy }}
                            </p>

                            <ul class="auth-shell__highlights">
                                @foreach ($asideHighlights as $highlight)
                                    <li class="auth-shell__highlight">{{ $highlight }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <a href="{{ $asideButtonHref }}" class="ui-button ui-button--light-outline">
                            {{ $asideButtonLabel }}
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
