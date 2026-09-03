@php
    $fallback = $page['props']['readableFallback'] ?? null;
@endphp

@if (is_array($fallback))
    <main id="readable-fallback" data-readable-fallback>
        <section class="readable-fallback-hero">
            @isset($fallback['eyebrow'])
                <p>{{ $fallback['eyebrow'] }}</p>
            @endisset

            <h1>{{ $fallback['title'] ?? config('app.name') }}</h1>

            @isset($fallback['summary'])
                <p>{{ $fallback['summary'] }}</p>
            @endisset

            @if (! empty($fallback['actions']) && is_array($fallback['actions']))
                <nav aria-label="Δοκιμαστική πλοήγηση">
                    @foreach ($fallback['actions'] as $action)
                        <a href="{{ $action['href'] ?? '#' }}">{{ $action['label'] ?? '' }}</a>
                    @endforeach
                </nav>
            @endif
        </section>

        @if (! empty($fallback['sections']) && is_array($fallback['sections']))
            <section class="readable-fallback-sections">
                @foreach ($fallback['sections'] as $section)
                    <article>
                        <h2>{{ $section['title'] ?? '' }}</h2>
                        <p>{{ $section['body'] ?? '' }}</p>
                    </article>
                @endforeach
            </section>
        @endif
    </main>
@endif
