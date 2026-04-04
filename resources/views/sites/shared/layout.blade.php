<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name', 'CMS Studio'))</title>
        @vite(['resources/css/sites/app.css', 'resources/js/sites/app.js'])
    </head>
    @php
        $siteColorVariables = isset($site)
            ? \App\Support\Sites\SiteColorPalettes::cssVariables($site->colorSettings->palette_key ?? null)
            : [];
        $siteColorInlineStyle = collect($siteColorVariables)
            ->map(fn (string $value, string $variable): string => "{$variable}: {$value}")
            ->implode('; ');
    @endphp
    <body class="site-theme-shell site-theme--{{ $theme ?? 'base' }}" @if($siteColorInlineStyle !== '') style="{{ $siteColorInlineStyle }}" @endif>
        @yield('content')
    </body>
</html>
