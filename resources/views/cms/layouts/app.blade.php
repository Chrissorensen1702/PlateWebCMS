<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CMS Studio') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|space-grotesk:400,500,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/cms/app.css', 'resources/js/cms/app.js'])
    </head>
    <body class="cms-body antialiased">
        <div class="cms-shell">
            @include('cms.layouts.navigation')

            @if (isset($header) && ! auth()->user()?->isDeveloper())
                <header class="cms-page-header">
                    <div class="ui-shell cms-page-header__inner">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
