# Fight videos QA — 5 November 2026

Later update: Giannis now uses `K0jLEe7oz8E` and all three videos play inline. His new poster is cropped to 672×378 with no black bars. See [the current playback and verification results](fight-videos-licensed-qa.md). The original findings below describe the earlier upload and poster.

Implemented below AthletesSection and above ClosingSection. Existing page fonts, colour tokens, square geometry and spacing are retained. The ui-ux-pro-max accessibility, interaction and responsive checks were applied within the project's design system.

## Final verification

```text
php artisan test
Tests: 203 passed (2471 assertions)
Duration: 15.61s

./vendor/bin/pint --test
{"tool":"pint","result":"passed"}

npm run build
vite v8.2.2
667 modules transformed.
✓ built in 550ms

php artisan images:build-static --force
Done. 8077222 source bytes, 3814038 output bytes, 89 written, 0 skipped.
```

The first sandboxed test attempt could not open the MariaDB socket. The final suite ran successfully with access to the existing MariaDB test database. Vite emits warnings that the six existing local font URLs are resolved at runtime; font coverage/existence tests pass and browser fonts load locally. CSP tests pass unchanged.

## Screenshots and sizing

All three names fit without breaking words at every size below. All posters load as local responsive WebP images. Every article's reveal reaches translate(0px, 0px) when scrolled into view. The player frame remains 16:9.

| Viewport | Screenshot | document scrollWidth | clientWidth | Names fit |
| --- | --- | ---: | ---: | --- |
| 1920×1080 | [Screenshot](fights-1920x1080.png) | 1905 | 1905 | Yes |
| 1440×900 | [Screenshot](fights-1440x900.png) | 1425 | 1425 | Yes |
| 1366×768 | [Screenshot](fights-1366x768.png) | 1351 | 1351 | Yes |
| 1024×768 | [Screenshot](fights-1024x768.png) | 1009 | 1009 | Yes |
| 768×1024 | [Screenshot](fights-768x1024.png) | 753 | 753 | Yes |
| 390×844 | [Screenshot](fights-390x844.png) | 390 | 390 | Yes |
| 320×640 | [Screenshot](fights-320x640.png) | 320 | 320 | Yes |

There is no horizontal overflow. Desktop Chromium reserves a 15px vertical scrollbar, so the requested strict `scrollWidth === innerWidth` expression is false there; `scrollWidth === clientWidth` is true. In touch-phone emulation the requested strict expression is true. The 1280px name-fit check also passed. Additional `-item-1`, `-item-2`, and `-item-3` screenshots capture each card at 768, 390 and 320px widths.

Poster review: Papadelis's internal black bands and Stavridou's letterboxing were cropped locally. Stavridou's best available fallback gives a 512×288 cropped source and a 480px maximum derivative, so it is visibly softer when enlarged at tablet width. Giannis's final poster is the unmodified maxresdefault JPEG from `-TlzFF7LXOA`, as requested in the final instruction. That YouTube thumbnail currently contains black side bars; no vertical-frame composition remains in the application source.

## Behavior

- Network inspection before play: zero YouTube, Google, thumbnail, external script or external font requests. This was checked across the viewport matrix and in the browser's network request listing.
- Papadelis and Stavridou: playback advances inline on desktop and touch-phone emulation; `playsInline` is true and fullscreen stays false.
- Starting another video unmounts the previous iframe. Exactly one iframe and two poster images remain.
- Giannis's final ID is `-TlzFF7LXOA`. Final re-test at 22:02 Athens time still shows “This video is unavailable”. **Giannis's embedded video will play only after its ID is replaced with the forthcoming playable upload.** This is the known YouTube refusal; no fallback host, CSP change or alternate player was added.
- Replacing Giannis's ID requires changing one line in `fightVideos`. His poster name is derived from his surname and is independent of the ID.
- Counts 1–9 were exercised by substituting the array only in browser responses. Every row fills its width, including final rows of one or two items. Production retains exactly the three requested entries.
- Keyboard focus has a visible 2px inset outline. Button labels include each athlete's exact name; headings precede their corresponding buttons in reading order. Posters use `pointer-events: none`.
- Reduced motion: content remains visible, opacity is 1, transforms are absent, and no athlete pin is created.
- Reveals are now created after the athletes layout and pin setup, inside the breakpoint context. Trigger starts match their measured positions within rounding error across desktop/mobile breakpoint changes. Closing-image motion starts at its section's viewport entry.
- Inertia navigation while playing: the old iframe disconnects, old page ScrollTriggers are removed, and owned athlete click/keydown listeners are removed. Returning shows three posters, no iframe, and a single desktop pin, with no duplicated triggers.
- Existing article usage was checked on `/news/agonistiki-imera-stin-kavala`: its gradient placeholder remains, no poster prop is required, and clicking creates its iframe using internal state.

No new npm dependency, production deployment, git command or .env change was made. No physical iOS/Android device testing was performed; phone checks used Chromium touch emulation. The hero, coach, athletes and closing JSX sections and all other pages were left unchanged.
