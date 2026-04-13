<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ trim($__env->yieldContent('title')) !== '' ? trim($__env->yieldContent('title')).' | '.config('app.name', 'CMS Studio') : config('app.name', 'CMS Studio') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700|space-grotesk:400,500,700&display=swap" rel="stylesheet" />
        <script>document.documentElement.classList.add('js-ready');</script>

        @vite(['resources/css/sales/app.css', 'resources/js/sales/app.js'])
    </head>
    <body class="@yield('body-class', 'marketing-body')">
        <div class="marketing-body__glow marketing-body__glow--top"></div>
        <div class="marketing-body__glow marketing-body__glow--side"></div>

        <header class="marketing-header">
            @yield('header')
        </header>

        <main>
            @yield('main-content')
        </main>

        @include('sales.partials.footer')
    </body>
</html>
