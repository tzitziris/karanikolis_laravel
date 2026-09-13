{{--
    Deliberately plain. An error page is the one page that must render when
    something else is broken, so it does not go through Vite: no manifest
    lookup, no hashed filenames, no fonts on the critical path.
--}}
<!DOCTYPE html>
<html lang="el">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>@yield('title') · {{ \App\Services\PageMetadata::SITE_NAME }}</title>
        <link rel="stylesheet" href="/css/error.css">
    </head>
    <body>
        <main>
            <p class="eyebrow">@yield('eyebrow')</p>
            <h1>@yield('title')</h1>
            <p class="detail">@yield('detail')</p>
            @hasSection('action')
                @yield('action')
            @else
                <a class="action" href="/">Αρχική</a>
            @endif
        </main>
    </body>
</html>
