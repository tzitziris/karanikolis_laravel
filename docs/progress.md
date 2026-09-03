# Progress log

One entry per Codex prompt: what was asked, what came back, what I found, what was decided.

---

## Step 1 — Laravel skeleton + MariaDB in Docker + Pest (2026-09-01)

**Asked for:** an empty but working Laravel 13 skeleton; MariaDB in a Docker container; tests running
against MariaDB rather than SQLite; Greek locale; database-backed session/cache/queue; a cold-start
README. No frontend, no git, no npm.

**Delivered.** Verified by running it myself:

| Check | Result |
|---|---|
| `php artisan db:show` | MariaDB 11.8.9, db `karanikolis`, port 33070 |
| `php artisan test` | 2 passed, 6 assertions, 0.39s — against `karanikolis_test` |
| `./vendor/bin/pint --test` | passed |
| container | `karanikolis_laravel_mariadb`, healthy |

Laravel 13.17, PHP 8.3+, Pest 4, Pint. Dependency list is minimal as asked — no admin scaffolding.
Locale `el` with `en` fallback, `APP_FAKER_LOCALE=el_GR`. Session/cache/queue on the database driver.

**Found (not reported by Codex):**

1. **It made a git commit** (`abda331 first commit`, 54 files) despite an explicit instruction not to
   run git commands. Nothing was lost; `.env` is correctly gitignored. The owner decides whether to
   keep or undo it. This is why `AGENTS.md` now carries the rule — Codex reads it on every run.
2. **The database port is one value written in two places.** `docker-compose.yml` takes the host port
   from `.env` (`${DB_PORT:-33070}`); `phpunit.xml` hardcodes `33070`. Verified the precedence
   experimentally: a real `DB_PORT=33099` breaks the suite, and `.env` never reaches it. Failure: hit
   a port conflict, change `DB_PORT` in `.env` (the README points you at that exact line), bring the
   container up on the new port, and `php artisan test` still dials 33070 and dies with a driver
   stack trace that never mentions a port. The README's "Keep `DB_PORT=33070`" is a note to a human
   guarding a structural duplication.
3. **`DB_TEST_DATABASE` is a knob that does nothing.** It sits in `.env` and `.env.example` looking
   configurable. `phpunit.xml` hardcodes `karanikolis_test`, and the init script that reads the
   variable only runs when the container's data volume is empty. Rename it after the first
   `docker compose up` and nothing happens — silently, with tests still green.
4. **`Database\Factories\` is missing from composer autoload**, `database/factories/` does not exist,
   and `fakerphp/faker` is not installed. Model factories will not resolve at step 10.

**Decided:** fold 2, 3 and 4 into step 2, which already edits `composer.json`. Fixing them now as
separate prompts would cost two round trips for changes that touch the same files.

**Also changed by me (docs are the architect's job, not Codex's):** replaced the skeleton's generic
Laravel Boost `AGENTS.md`/`CLAUDE.md` with project-specific rules, and started `docs/`.

**Open:** `.env.example` still carries AWS, Redis, Memcached and broadcast blocks this project will
never use. Harmless, but every unused key is something a future reader must decide is irrelevant.
Will trim when we next touch it.

---

## Step 2 — single source of truth for the database, and working factories (2026-09-01)

**Asked for:** one place defining how to reach the development database and one for the test database,
with everything deriving from them; the fake `DB_TEST_DATABASE` knob either made real or removed;
model factories able to resolve.

**Delivered.** `phpunit.xml` no longer hardcodes anything — the DB entries were removed entirely and
the test connection is redirected at boot from `DB_TEST_DATABASE`. Guards throw before migrations run
if the port is unreachable, if the test database equals the development one, or if it is empty.

Verified myself, beyond the tests:

| Check | Result |
|---|---|
| `php artisan test` | 3 passed, 9 assertions |
| `./vendor/bin/pint --test` | passed |
| wrong port | fails naming `DB_PORT` and explaining the compose relationship |
| test db == dev db | fails naming both |
| empty `DB_TEST_DATABASE` | fails clearly |
| **marker row in the dev database** | **survived a full `RefreshDatabase` run** |

**Found:** the new assertion compared the live connection's database name against the very config
value that sets it, so it could never fail — the property that matters (test database ≠ development
database) was asserted nowhere. Also `env()` was being called from `AppServiceProvider`, which
returns null once config is cached.

**Decided:** fold both into step 3.

---

## Step 3 — Inertia + React 19 + Vite + Tailwind v4 (2026-09-01)

**Asked for:** the frontend toolchain, one placeholder page through Inertia, no SSR and no way to add
it by accident, a `composer run dev` script, plus the folded fix to the circular assertion.

**Delivered.** Verified:

| Check | Result |
|---|---|
| `php artisan test` | 5 passed, 23 assertions |
| `./vendor/bin/pint --test` | passed |
| `npm run build` | 313.66 kB JS (98.57 kB gzip), 5.75 kB CSS |
| browser at `/` | React renders, `lang="el"`, Tailwind applied, **zero console errors** |
| SSR | no entry point, `inertia.ssr.enabled = false`, and a test now guards all three signals |

Codex went beyond the prompt and moved the `env()` calls into `config/database.php`, which resolves
the issue I had deferred to step 18. The circular assertion was replaced correctly.

**Found by Codex's own review agent, and confirmed by me:** the README's cold-checkout section stops
at `php artisan serve` and never mentions `npm install`. On a fresh clone that is not an unstyled
page — it is a 500, `ViteManifestNotFoundException`.

**Found by me, reported by nobody:**

1. A test whose name lies: `it serves the empty Greek application shell` now asserts only
   `assertOk()`; the Greek-content check was removed when the Inertia test was added. It duplicates
   that test and promises something it no longer does.
2. `development_database` is an invented key inside the connection config, next to `database`,
   reading the same environment variable. It works, but in production that key will hold the
   *production* database name while being called "development".

**Decided:** fold 1 and the README fix into step 4. Leave 2 — renaming it is churn until we have a
second environment to be confused by.

**Also found while checking fonts (unrelated to the port, see `decisions.md`):** the display font has
no Greek glyphs, so every Greek heading on the live site is drawn by the OS fallback. Owner chose
Roboto Condensed 900 as the replacement.

---

## Steps 4-7 — typography foundation (2026-09-01)

Four prompts, because each fix exposed the next problem. Final state: 8 tests, 114 assertions, Pint
clean, build 306 KB JS + 8.4 KB CSS.

**Step 4 — self-hosted Greek fonts + design tokens.** Inter, Roboto Condensed, JetBrains Mono, greek
and latin slices only, with licences. Verified in the browser that Greek glyphs come from our files
(both Roboto Condensed slices load; Greek width 196px matches a condensed face, not generic sans at
235px). *Found:* no metric-matched fallback at all, which made us **worse than the site we replace** —
its framework generated those automatically. Also `--font-bebas` was left holding Roboto Condensed,
a name that lies in the compiled CSS. **That one was my prompt's fault**: I said "keep the original
names so the codebases stay comparable", which is right for the colour tokens and wrong for a font we
deliberately changed. I no longer give blanket "keep the names" instructions — I name the specific
tokens to preserve.

**Step 5 — metric-matched fallbacks + honest token names.** Measured afterwards: **height delta 0%**
on every sample, so the vertical layout shift is gone. Width still differs.

**Step 6 — close the swap window.** Codex chose `font-display: optional` plus preloading the Greek
slices. Codex's review agent then filed a P1 saying the single fallback face cannot match both the
Greek and Latin slices and demanded per-subset fallbacks. **I measured and rejected it.** Real
numbers: Greek capitals +8.2%, Greek sentence −5.6%, Latin capitals +10.0%. Latin is *worse* than
Greek, so it is not a Greek-vs-Latin problem; and inside Greek alone the error spans 13.8 points,
which no single `size-adjust` per range can absorb. The error tracks which glyphs are typed, not which
subset they belong to. Per-subset fallbacks would add four faces and fix almost nothing.

**Step 7 — the trap that combination created.** `optional` means a font that misses its window is
never applied for that page view. Only the Greek slices were preloaded. **Digits live in the Latin
slice.** So a cold slow first visit rendered Greek words in Roboto Condensed and every number beside
them in Arial, permanently for that view — on a design built around 15+, 240, 18, 01/02/03. Verified
in the browser: the Greek slice does not cover U+0032, the Latin slice does, only Greek was
preloaded. Neither `optional` nor Greek-only preloading causes this alone; only the combination does.

The first attempt at step 7 **produced no edits at all** — Codex ran only its review agent. Confirmed
by file mtimes and an unchanged build hash. Re-issued the prompt leading with the change to make.

**Open items (deliberately deferred, not forgotten):**

1. **166 KB of fonts now sit on the critical path** — all six files are preloaded. Measured
   alternative: the Latin slice of the display font is 45.0 KB, but a subset covering digits, ASCII
   and punctuation at one weight is **8.0 KB**; JetBrains Mono Latin goes 30.6 KB → 7.2 KB. That is
   roughly 40% off the critical path. **Deferred on purpose:** trimming Latin coverage correctly
   requires knowing which Latin characters and weights the finished pages actually render. Doing it
   now is guessing. Revisit once the four static pages exist.
2. **The guard test encodes the wrong rule.** It asserts preloaded paths equal shipped paths exactly,
   i.e. "preload everything", rather than the rule I asked for ("a family is either fully in use or
   not in use"). It is correct for today's code but will block item 1 for the wrong reason. Fold the
   correction into that step.

## Prompt 9 — blank page on client-side navigation + removal of double-authored content

**Both problems fixed and independently verified.**

Client-side navigation, measured at 1280x800 with a MutationObserver recorder (setTimeout is
clamped to 1s in a background tab, which corrupted the first attempt's sample spacing):

| | before | after |
|---|---|---|
| destination content visible | ~2750 ms | **11 ms** |
| elements hidden at any moment | 8 | **0** |
| what revealed the content | the 1800 ms watchdog | the render itself |

Measured across three navigations between two genuinely different page components
(`Placeholder` and the new `MotionDemo`). ScrollTrigger count stayed at 1 throughout — no leak.
Full page load: 388 visible characters, 0 hidden, DOMContentLoaded 106 ms.

The cause was that content was hidden with `autoAlpha: 0` and revealed by the animation. The fix
removes hiding entirely: `animateFromVisible` now only offsets elements (`y: 10`/`y: 16`) and
animates them back to zero, so content is readable at every instant and motion is pure transform.
`AnimationInvariantTest` now asserts `autoAlpha: 0` never appears in the animation source.

`ReadablePage`, `partials/readable-fallback.blade.php` and the fallback arrays are gone.
`routes/web.php` is back to 17 lines. Content lives in exactly one place.

**Open, carried forward:** the watchdog is now dead code — nothing sets `data-animation-hidden`,
so `forceRevealAll()` matches zero elements and the 1800 ms timer does nothing, while the test
still asserts the watchdog exists. `data-animation-managed` is written and never read. Decide
before the real pages land: either delete the machinery, or route all hiding through a helper
that sets the attribute so the net is real again.

## Prompt 10 — webp image pipeline + removal of the dead watchdog

`config/images.php`, `StaticImageService`, `images:build-static`, and one `SiteImage` component.
84 derivatives written for 14 photographs across 8 widths, with no upscaling — an image only gets
widths up to its own. 19 tests / 606 assertions, Pint clean, build unchanged. `curl` confirms
`Content-Type: image/webp` off the real server.

Measured saving (the originals were 2.76 MB and Vercel's optimizer, which resized them per device,
does not exist on the target host):

| | before | after |
|---|---|---|
| all 14 photos at phone width (320) | 2.76 MB | **236 KB** |
| largest single photo at 2400px | 341 KB | **275 KB** |

The watchdog was removed rather than rewired — the honest choice, since nothing hides content any
more. The test now asserts the *absence* of hiding instead of the presence of a safety net.

**Three defects found in review, to fix before any page is built:**

1. **The source photographs are not in this repository.** `config/images.php` defaults the source
   directory to `../karanikolis_site/public/media`, and two tests call `getimagesize()` on that path
   at run time. Verified: with the source directory absent, `ImagePipelineTest` fails 2 of 6. The
   test suite of this project cannot run without the read-only Next.js project sitting beside it,
   and the images cannot be rebuilt on any other machine.
2. **The manifest alignment test does not detect a mismatch.** It asserts each dimension appears
   *somewhere* in the file, not that it belongs to the right image. Verified by swapping the
   dimensions of `about-story` and `pad-work`: the test still passed. Dimensions are the one thing
   in that file that prevents layout shift.
3. **`SiteImage` throws during render for an unknown image name**, which takes down the whole page
   instead of one picture — a direct violation of invariant 1.

Minor, carried forward: `SiteImage` defaults to `loading="lazy"`, which is wrong for a hero image
and will need overriding per slot.

## Prompt 11 — the three image-pipeline defects

All three fixed, each verified independently rather than by the tests that claim them.

1. **Sources moved into the repository.** `config/images.php` now reads `resources/images/static`
   (14 JPEG, 2.8 MB, outside `public/`). The only remaining mention of `karanikolis_site` anywhere
   in the code is the test asserting it is *not* used. The manifest is now generated by the
   converter from the source files themselves, so PHP and JavaScript no longer hold two copies of
   the dimensions.
2. **The manifest test now bites.** It compares the whole generated structure with an exact match.
   Re-ran the swap that previously slipped through — `about-story` and `pad-work` exchanged — and
   the test failed as it should. Restored, confirmed by hash.
3. **`SiteImage` no longer throws.** An unknown name renders a labelled span carrying the alt text,
   so a bad name costs a picture, not the page. Loading now follows the slot: `hero` is eager,
   everything else lazy.

21 tests / 578 assertions, Pint clean.

Minor, not worth a round trip: the `hero` slot is eager but sets no `fetchpriority`, and
`widthsFor()` is a wrapper that returns its argument's property unchanged.
