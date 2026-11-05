# Replacement Giannis upload — verification

Giannis's `fightVideos` entry now uses `K0jLEe7oz8E`. Downloaded its maxresdefault JPEG and removed the black side bars, keeping the face and upper body in a clean 672×378 (16:9) crop. Rebuilt the local WebP derivatives and Vite assets. The player and CSP were not changed.

Real command output:

```text
php artisan images:build-static --force
Done. 8061437 source bytes, 3750178 output bytes, 86 written, 0 skipped.

npm run build
667 modules transformed.
✓ built in 544ms

php artisan test
Tests: 203 passed (2459 assertions)
Duration: 14.97s

./vendor/bin/pint --test
{"tool":"pint","result":"passed"}
```

Vite retains the existing warnings that the six local font URLs resolve at runtime. The build exits successfully. Tests ran against the existing MariaDB test database with local database access.

Browser testing used desktop Chromium at 1440×900 and touch-phone emulation at 390×844. Clicked/tapped each of the three videos in sequence in each environment. Every video was unpaused and its playback time increased between two samples. All reported `playsInline: true`; fullscreen remained false. Starting each subsequent video disconnected the preceding iframe, leaving exactly one iframe and two posters. Before the first click there were zero iframes and zero external requests.

| Video | Desktop playback time samples | Phone playback time samples |
| --- | --- | --- |
| Giannis | 1.782 → 2.389 seconds | 1.563 → 2.170 seconds |
| Kostas | 1.983 → 2.589 seconds | 1.991 → 2.596 seconds |
| Vasiliki | 1.913 → 2.519 seconds | 2.296 → 2.902 seconds |

The replacement poster loads through SiteImage as local WebP. [Poster screenshot](fights-licensed-poster.png), [desktop playback screenshot](fights-licensed-playback-desktop.png), [phone playback screenshot](fights-licensed-playback-mobile.png).

No production deployment or physical phone test was performed. No git command, .env change, dependency addition, player fallback or CSP change was made.
