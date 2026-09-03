<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name') }}</title>

        <link rel="preload" href="/fonts/inter/inter-greek-400-900.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/inter/inter-latin-400-900.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/roboto-condensed/roboto-condensed-greek-700-900.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/roboto-condensed/roboto-condensed-latin-700-900.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/jetbrains-mono/jetbrains-mono-greek-400-700.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/jetbrains-mono/jetbrains-mono-latin-400-700.woff2" as="font" type="font/woff2" crossorigin>
        @viteReactRefresh
        @vite('resources/js/app.jsx')
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
