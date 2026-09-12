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
(`Placeholder` and the temporary motion demo page, since removed). ScrollTrigger count stayed at 1
throughout — no leak.
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

## Prompt 12 — the site shell

Navbar and Footer now live in `resources/js/Layouts/SiteShell.jsx`, rendered by `app.jsx` *around*
the keyed page component. Four temporary routes (`/coaches`, `/schedule`, `/news`, `/about`) exist
so the shell can actually be navigated; they render `PublicPlaceholder` and get replaced one at a
time.

29 tests / 714 assertions, Pint clean, build clean (app chunk 448.50 kB, 150.86 kB gzip).

Verified independently, not by the tests that claim it:

- **The shell does not remount.** Tagged the live `[data-site-header]` and `[data-site-footer]`
  DOM nodes with expando properties, then navigated five times. Both properties survived every
  visit, so React is reusing the same nodes rather than rebuilding them.
- **Navigation costs 22–42 ms** and issues exactly one request — the Inertia JSON for the new page.
  No asset, chunk or shell refetch.
- **Prefetch works.** All five destinations are fetched ~105 ms after first paint, before any click.
- **The logo keeps its transparency.** 2.29 MB PNG source becomes five webp derivatives; corner
  pixels read alpha 127 (fully transparent), centre 0 (opaque). At DPR 2 the browser picks
  `site-logo-64.webp` — 2.1 kB, against 17.9 kB for the old site's single `logo-nav.webp`.
- **The mobile menu is a real dialog.** Portalled to `document.body`, `role="dialog"` +
  `aria-modal`, `#app` given `inert` and `aria-hidden`, both `body` and `html` overflow locked,
  focus lands on the close button, Tab wraps in both directions, Escape closes, focus returns to
  the opener, and every lock is released on close — including when the menu is closed by following
  one of its own links.
- **Nothing in the menu is hidden waiting for animation.** The panel measures opacity 1 and
  transform none, and `animateMobileMenuOpen` only offsets `y`. A thrown animation leaves a fully
  usable menu.

Open, small:

1. **No skip link.** `<main id="site-content">` has the id but nothing targets it, so a keyboard
   user tabs through the logo and five links on every page. The old site had the same gap.
2. **`isActivePath` uses a bare `startsWith`**, so a future `/about-us` would mark `/about` as the
   current page.
3. **`STATIC_IMAGE_WIDTHS` now describes only the photograph widths**, not the mark widths, while
   claiming to describe the manifest. Nothing consumes it — dead and misleading, like `widthsFor()`.
4. **The `logo` slot hard-codes `sizes: '32px'`** to match today's `h-12` styling. If the logo is
   ever resized, that number silently lies.

The gap worth naming: the two new shell tests match source text (`toContain('<SiteShell>')`), so
they record the intent but cannot prove it. The proof that the shell survives navigation exists
only in the measurement above, which nothing re-runs. That is my prompt's fault — I asked Codex to
tell me how it satisfied itself instead of asking for the property that must hold.

## Prompt 13 — stylesheet corrections

Two defects found by reading the compiled CSS instead of the source.

`text-pewter-dim` compiled to nothing. Six labels in the header and footer — the footer column
headings, the copyright line, and the mobile menu's label and contact block — were painted with a
class Tailwind silently ignored, so they rendered in whatever colour they inherited. The token
existed as a raw custom property but was never exposed to the utility layer. Root cause is prompt 2,
which ported the tokens and missed this one.

The fix is a comparison, not a patch: the suite now checks what the stylesheet defines against what
the components ask for, in both directions. Verified by mutation — removing `--color-pewter-dim`
from the theme failed two tests, and adding a `text-gunmetal` that nothing defines failed one.

The `readable-fallback` rules were also removed, dead since prompt 10. 248 → 155 lines; compiled
CSS 22.12 → 21.06 kB, gzip 5.25 → 5.02 kB. The line count fell by a third, the weight by 5 % —
minification had already absorbed most of it.

## Prompt 14 — the local database matches production

The owner checked the cPanel host on 2026-09-03: **MariaDB 10.11.18-cll-lve, PHP 8.4.24,
connection collation utf8mb4_unicode_ci, server default charset cp1252/latin1**.

Two gaps, both now closed:

1. The container ran MariaDB 11.8 against a 10.11 production. It had already produced a concrete
   failure in waiting — the development database carried `utf8mb4_uca1400_ai_ci`, a collation
   introduced in 11.4 and absent from 10.11, so a dump taken here could not be restored there.
   Pinned to `mariadb:10.11`; the container now reports **10.11.19**, the same line as production.
2. Only the test database declared its charset. The development database was created by the image
   entrypoint and inherited the server default, which is how it acquired the unportable collation.
   Both databases are now created by the init script with an explicit `utf8mb4` /
   `utf8mb4_unicode_ci`, and the server itself is started with those defaults.

Verified against the running container, not the config files: both schemas report
`utf8mb4 / utf8mb4_unicode_ci`, all nine framework tables likewise, and Greek text round-trips
exactly — `Μαχητές Ελευθερούπολης` returns 22 characters / 43 bytes, em dashes and accents intact.

34 tests / 765 assertions, Pint clean.

Worth carrying forward: `utf8mb4_unicode_ci` treats `ά` = `α`, `ς` = `σ` and ignores case. That is
right for searching and **dangerous for a unique index** — two article titles differing only by
accent or case would collide. The slug generator must expect collisions and disambiguate rather
than assume uniqueness falls out of the transliteration.

The weakness, and it is the second time: `DatabaseContainerTest` reads `docker-compose.yml`, not
the running database. During this very step Codex ran the suite green while the 11.8 container was
still up. A one-line query through the test connection would assert the real thing. Same shape as
the shell tests in prompt 12 — the suite is accumulating tests that describe intent instead of
checking it.

## Prompts 15–19 — the four static pages

Home, About, Coaches and Schedule now render at `/`, `/about`, `/coaches` and `/schedule`. The
temporary `/dokimi-kinisis` route and its page component are gone. 42 tests / 1023 assertions,
Pint clean.

Verified by measurement rather than by the tests that claim it:

- **The pinned journey section leaves nothing behind.** Home carries one pin-spacer and a document
  height of 5669px; navigating to About and Coaches drops it to zero spacers with no leftover style
  on `html` or `body`; returning to Home restores exactly one spacer and exactly 5669px. Nothing
  accumulates across visits, and the header stayed the same DOM node throughout — invariant 3 holds
  in practice, not just in the code.
- **No page scrolls horizontally** at any width from 375 to 2200px, on any of the four pages.
- **No content is hidden waiting for animation** on any page; every reveal offsets from a readable
  state.
- Each page marks exactly one image as priority (eager + `fetchpriority="high"`); the rest stay lazy.

Two honesty problems were caught and fixed, both the same shape — invented data presented as real:

1. The Coaches roster listed four fictional athletes by name with weight class and division. Names
   and categories were removed entirely rather than labelled; the section now says in Greek that
   they are withheld until official details exist.
2. The Schedule's hours are placeholder. The page says so — twice, after an over-correction that
   said it six times and read like an apology in every section.

**Greek uppercase headings overflowed four separate times** across these pages: `max-w-[Nch]` caps
(a `ch` is the width of "0", far narrower than condensed Greek capitals), then a `break-words`
"fix" that split words mid-word, then sizes left too large after that was removed, then timetable
cells. All measured and closed except one, below. This is now recorded as a standing rule for any
future page.

Known and accepted, not fixed: in the Schedule's level legend, "Προχωρημένοι" — the longest of the
five labels — is 34–38px wider than its cell at 1280px and 1920px (it fits at 1024 and 1440). It
touches nothing, leaves no gap, and causes no horizontal scroll; it simply crosses an invisible
cell boundary. Deferred to a final polish pass rather than a fifth typography round-trip.

## Prompt 20 — the news database layer

Three tables (`articles`, `article_images`, `article_videos`), models, factories and a local seeder.
50 tests / 1076 assertions, Pint clean.

Verified by querying MariaDB directly, not through the code that wrote it:

- **Cascade works at the database level.** Deleted an article with raw SQL, bypassing Eloquent
  entirely; its three images and two videos went with it.
- **The body is guaranteed valid JSON** by a `CHECK (json_valid(body))` constraint on the table, not
  merely by a cast in the model.
- **The slug is decided once.** Renaming an article from "Αρχικός τίτλος" to "Εντελώς διαφορετικός
  τίτλος" left the slug untouched — the old site's habit of recomputing it per request, which broke
  every shared link on a rename, is not reproduced.
- **Collisions resolve instead of erupting.** Four titles differing only by accent and case produced
  `dokimi-…`, `-2`, `-3`, `-4` with no exception reaching the caller — which matters because the
  collation treats "ά" as "α" and ignores case.
- Indexes: unique on `slug`, plus `published_at`, `is_visible`, and `(article_id, sort_order)` on
  both child tables. Cover images are stored as a name, never a path, so the image gateway stays the
  only route to a file.

**The first attempt transliterated Greek wrongly**, and these become permanent public URLs.
"Ελευθερούπολη" produced `eleytheroypoli` — disagreeing with the school's own domain, which spells
it `eleftheroupolis`. Worse, digraphs were applied mid-word and swallowed letters: `σύγκρουση →
sygroysi` (κ gone), `τσάντα → tsada` (ν gone), so genuinely different words collapsed onto one
address. Fixed, and confirmed as a real fix rather than a patch by testing eighteen words that were
never named in the prompt: 16 matched exactly, including every hard case — `ευ` correctly becomes
`ef` or `ev` depending on what follows (`ευχαριστώ → efcharisto`, `ευγενικός → evgenikos`), `αυ`
likewise, and `μπ`/`ντ` keep both letters inside a word (`λάμπα → lampa`, `κέντρο → kentro`).

Accepted difference: `άγγελος → aggelos` and `σύγχρονος → sygchronos`, where the commoner rendering
is `angelos` / `synchronos`. No information is lost and nothing collides — a convention, not a
defect.

**This must never be changed again once real articles are published**, because every existing link
would break silently.

The seeder was also too thin on the first attempt — plain paragraphs only, no article hidden from
visitors, and none of the Facebook-pasted content that was asked for. It now carries 18 articles
with headings, both list kinds, a blockquote, links, bold and italic, images, one hidden draft, two
never published, and one article pasted from Facebook whose emoji arrive as inline images from
**two** different hosts (`static.xx.fbcdn.net` and `www.facebook.com`) — the renderer will have to
recognise both.

## Prompt 21 — the article body renderer

`ArticleBodyRenderer` turns stored article JSON into HTML in PHP, so the editor never reaches a
visitor. 62 tests / 1134 assertions, Pint clean.

This function is the trust boundary of the site: its input is pasted from Facebook by the owner, its
output is inserted as raw markup. I attacked it with twenty-three inputs written for this review,
none of which appear in its own test suite:

| Attack | Result |
|---|---|
| `javascript:` link, plus mixed-case, embedded tab, leading control characters | link dropped, text kept |
| `data:text/html;base64,…` and `vbscript:` links | dropped |
| `href` closing the attribute early to inject `onmouseover` | dropped entirely, not merely escaped |
| `<script>` and `<img onerror>` written as article text | escaped |
| `onerror` supplied as an image attribute | ignored |
| `textAlign` carrying `;background:url(…)` | no style emitted at all |
| `heading` at level 99 | clamped |
| unknown mark types | ignored, text preserved |

The reference's own approach — escaping a link for HTML and writing it into an `href` — does not stop
any of the first three. This implementation rejects addresses rather than escaping them.

Invariant 2 holds through pasted content: a remote `<img>` becomes
`<span class="article-removed-image">Εικόνα: …</span>`, so no visitor's browser is made to fetch a
bitmap from someone else's server. Facebook emoji are recognised from **both** hosts that occur in
reality — the reference only handled one — and become real characters.

The real seeded Facebook article renders with zero occurrences of `fbcdn`, `facebook.com`, `http://`,
`https://`, `<img`, `<script`, `onerror`, `javascript:` or `emoji.php`.

YouTube and iframe nodes render as nothing, as intended; videos have their own place in the data and
will be presented later without contacting Google until asked.

No stack or time problems: 5000 levels of nesting renders in 28 ms, 20000 sibling paragraphs in
32 ms. There is no explicit depth limit, which is acceptable while the only author is the owner.

Minor, noted not fixed: content inside an unrecognised node comes out as a bare text run with no
paragraph around it. The words survive, which was the requirement, but they land outside any block.

## Prompt 22 — the news archive

`/news` reads from the database through `ArticleFeed`, and the home page's latest-news strip now
uses the same source. 69 tests / 1308 assertions, Pint clean.

**The definition of "published" lives in exactly one place** — `Article::scopeReadyForPublic()` —
and applies three conditions together: visible, has a publication date, and that date has passed.
Nothing else in the application expresses the rule; the only other mentions of `is_visible` are
factories and the seeder, which are data rather than policy.

Verified against MariaDB directly rather than through the code that wrote it. The database says 16
of the 18 seeded articles qualify; the feed returns exactly 16, over two pages of nine. Both
excluded articles stay excluded:

- «Ορατό χωρίς ημερομηνία δημοσίευσης» — visible, but never dated
- «Προσχέδιο ανακοίνωσης για αγώνες» — hidden draft

I also created a visible article dated a week ahead: the public count stayed at 16, so a future date
does not leak. Removed afterwards.

Query behaviour on one visit to `/news`: two queries for the articles — a count and a select — plus
the session reads every request makes. **No N+1**, and the select names its columns, so the large
`body` column is never loaded for a list that does not display it. The card payload confirms it:
no `body` key reaches the browser.

Bad page numbers behave sensibly. `?page=99` redirects to `?page=2`, the last page that exists, so a
reader who overshoots lands on articles rather than on emptiness; `?page=abc`, `?page=0` and
`?page=-1` all serve the first page.

## Prompt 23 — the article page

`/news/{slug}` renders a single article, with the gallery, the videos, and the body converted by
`ArticleBodyRenderer`. 76 tests / 1546 assertions, Pint clean.

**No leak by address.** The archive's rule is not restated here — `readyForPublic()` remains the
single definition, called from two places in `ArticleFeed` and nowhere else. Requested directly:

| Address | Result |
|---|---|
| a published article | 200 |
| «Ορατό χωρίς ημερομηνία δημοσίευσης» (visible, undated) | 404 |
| «Προσχέδιο ανακοίνωσης για αγώνες» (hidden draft) | 404 |
| a slug that does not exist | 404 |

The 404 is the site's own page in Greek — «Η σελίδα δεν βρέθηκε», explaining that the article may
have moved, may not be published yet, or may no longer be available — not an English framework page.

**The videos genuinely do not contact Google.** The reference defeated its own click-to-load by
showing a still frame fetched from `i.ytimg.com`, which identifies the reader before they choose
anything. Measured on a loaded article page with a video: **zero third-party requests, zero iframes**
before the click. The only external URL in the component is the `youtube-nocookie` embed, and it
exists only once the reader has pressed play.

**Queries:** three for the whole page — the article, its images, its videos — with the children eager
loaded by `article_id IN (…)`. No per-photograph or per-video query.

**The gallery lightbox** was driven from the keyboard: arrow keys move between photographs and back,
Tab wraps in both directions, Escape closes, focus returns to the thumbnail that opened it, body
scroll is released, and it reports `role="dialog"` with `aria-modal`. Nothing is hidden — the panel
measures opacity 1 and visibility visible.

**Headings hold.** Measured the longest published title, «Κλείσιμο χρονιάς με δυνατές στιγμές»
(35 characters), across ten widths from 375 to 2200px: nothing past the viewport, no horizontal
scroll. Sixth page, first time with no typography finding.

## Prompt 24 — administrator accounts and sign-in

Session auth at `/admin/login`, everything else behind it, plus `admin:create`, `admin:list` and
`admin:delete`. 89 tests / 1675 assertions, Pint clean.

**The cPanel constraint was solved properly.** The reference takes the password as `--password=` on
the command line; on a host with no terminal the only way to run artisan is a temporary cron job,
whose command line is visible in the control panel and copied into its logs and notification mail.
`admin:create` refuses that outright — «Ο κωδικός δεν πρέπει να μπει στη γραμμή της εντολής» — and
takes `--password-file=` instead, **deleting the file as soon as it has read it**, so the owner
cannot forget to. Confirmed: the password appears zero times in the command's output, and the file
is gone afterwards.

Verified by probing the running site, not by reading the tests:

| Check | Result |
|---|---|
| `/admin`, `/admin/logout`, `/admin/articles`, any unknown admin path, unauthenticated | **302 → /admin/login** |
| `POST /admin/logout` with no session or token | 419, never 200 |
| Wrong password, **existing** vs **non-existent** email | byte-identical Greek reply — no user enumeration |
| Failed attempts | blocked on the **11th**: «Έγιναν πολλές αποτυχημένες προσπάθειες. Δοκιμάστε ξανά σε 15 λεπτά.» |
| Session identifier before vs after sign-in | changes — no session fixation |
| After sign-out | `/admin` redirects again |

The admin area carries none of the public chrome: no header, no footer, and **no links back to the
public site at all**. Its text is Greek, and the fields carry `username` / `current-password`
autocomplete so a password manager behaves.

`admin:delete` asks for confirmation only on an interactive terminal, so a cron job deletes without
prompting. That is the only way it can work on the target host; worth remembering before writing
that particular cron line.

### How the owner creates their account on cPanel

1. File Manager → create a file, e.g. `/home/USER/pw.txt`, containing only the password (≥10 chars).
2. Cron Jobs → add a job, once, with the command
   `cd /home/USER/APP && php artisan admin:create --email=THEIR@EMAIL --password-file=/home/USER/pw.txt`
3. Wait for it to run. The password file deletes itself.
4. Delete the cron job.

## Prompt 26 — the dashboard: every article, publish, unpublish, delete

`php artisan test` **100 passed (1764 assertions)**. `./vendor/bin/pint --test` passed.
`npm run build` clean; `Dashboard-*.js` is 7.56 kB (2.25 kB gzipped) and, because the admin page
opts out of the public layout, it costs a visitor nothing.

What was built: `AdminDashboardController`, `AdminArticlePublicationController` (publish/unpublish),
`AdminArticleController@destroy`, `AdminArticleService`, and `resources/js/Pages/Admin/Dashboard.jsx`.
`Admin/SignedIn.jsx` — the placeholder from the auth step — is gone.

### The prefetch trap was avoided

Seventeen links on this site prefetch on hover. Had any state change been reachable by following a
link, articles would have deleted themselves under the owner's cursor. They are not:

| Probe (mine, not the suite's) | Result |
|---|---|
| `GET /admin/articles/{id}/publish` | **404**, article untouched |
| `GET .../unpublish`, `GET .../delete`, `GET .../destroy`, `GET /admin/articles/{id}` | **404**, article untouched |
| Article row after all five GETs | still present, `is_visible=0`, `published_at` still `NULL` |
| `Link` components in `Dashboard.jsx` | **zero** — every action goes through `router.patch` / `router.delete` |
| `prefetch` anywhere under `Pages/Admin/` | none |

State changes are `PATCH`/`DELETE` behind `web` + `auth`, so CSRF and the session both apply, and
unauthenticated attempts redirect to `/admin/login` before touching data.

### Unpublishing does not forget the date — checked in the database, not through the code that wrote it

Read straight from the `articles` row, travelling three days between each step:

| Step | `is_visible` | `published_at` | `updated_at` |
|---|---|---|---|
| start | 0 | NULL | — |
| published | 1 | **2026-09-12 11:39:13** | 2026-09-12 |
| unpublished (3 days later) | 0 | **2026-09-12 11:39:13** | 2026-09-15 |
| republished (3 more days later) | 1 | **2026-09-12 11:39:13** | 2026-09-18 |

And the consequence that actually matters, measured through the public feed: an article published
30 days ago, taken down and put back an hour ago, still sits **below** one published 2 days ago.
Public order reads `Νεότερο, Παλιό`. The historical fact survived.

`publish()` writes `published_at ?? now()`, so a first publication gets a date and a later one keeps
the original.

### The owner sees what the public cannot

Four distinct states, each with a Greek label and an explanation of *why* nobody can see it:
Κρυφό, Ορατό χωρίς ημερομηνία, Προγραμματισμένο, Ζωντανό. The public rule was not reused to build
this list and was not weakened: `readyForPublic()` is still called from exactly two places, both in
`ArticleFeed`.

One query for the whole list, with `withCount` for photographs and videos, and the `body` column is
never selected — verified by asserting on the SQL actually issued.

### Known limits, deliberately left

- **The list is not paginated.** Every article is sent on every dashboard load. At eighteen articles
  this is nothing; it is a real problem at several hundred, and pagination is the fix when it comes.
- **Deleting an article removes its database rows but not its files.** The cascade takes
  `article_images` and `article_videos` with it, but any webp left in `public/` stays. Nothing
  uploads files yet, so nothing is orphaned today — this must be solved in the upload step, not after.
- **A missing admin article id renders Laravel's English 404.** Only the owner can reach it, and
  error-page polish is its own step.
- Every button on the page is disabled while any one action is in flight. Honest, slightly blunt.

### Third sighting of the same weakness in the suite

`AdminDashboardTest` ends with a test that reads `Dashboard.jsx` as text and asserts it *contains*
`router.patch(...)` and does *not* contain `<Link`. That documents the intention; it does not check
the behaviour, and it would pass on a file that never renders. The behaviour is genuinely correct —
but I established that by firing GETs at the routes and reading the row back, not from that test.
This is the third time (after the shell tests and `DatabaseContainerTest`) that a string match has
stood in for a check. Worth a cleanup prompt before the suite gets any larger.

## Prompt 27 — uploaded photographs become real images (step 16 split, part one)

Step 16 was going to be "the article form with TipTap and uploads". I split it, because a check
before writing that prompt showed the form could not have worked: every article image goes through
`SiteImage`, which resolves names from a manifest generated at **build time**, and renders a `<span>`
of alt text when the name is unknown. An uploaded photograph arrives after the build. The owner would
have uploaded photographs and seen alt text.

`php artisan test` **107 passed (1869 assertions)**. `./vendor/bin/pint --test` passed. `npm run build` clean.

New: `UploadedArticleImageService`, `ArticleImageUploadException`, `ArticleImage.jsx`, an `uploads`
section in `config/images.php` with separate widths per role. `ArticleFeed` now hands the front end an
image descriptor that says whether a name is `static` or `upload`; uploaded ones carry their real
dimensions, read from the database, so the page cannot jump as photographs arrive.

### A real phone-sized photograph through the pipeline

Source: **775,838 bytes, 4032×3024** (12.2 megapixels).

| role | derivatives | largest single file | all of them together |
|---|---|---|---|
| cover | 7 (480 → 2400) | **71,878 bytes** (2400×1800) | 267,572 bytes |
| gallery | 6 (320 → 1600) | 45,204 bytes (1600×1200) | 148,758 bytes |

A browser downloads **one** of these, so the worst case a visitor pays is 72 KB where the original was
776 KB. Everything under `public/images/uploads` is webp — 13 files, 13 with a `.webp` extension, zero
without. The original never lands there.

### What I threw at it that its own tests do not

| Input | Result |
|---|---|
| PHP script named `.jpg`, declared `image/jpeg` | refused, Greek, **0 files left behind** |
| A real JPEG renamed `.png`, declared `image/png` | refused — content and declared type must agree |
| SVG (with a `<script>` in it) declared `image/jpeg` | refused |
| Valid 1×1 JPEG | accepted, one 1px derivative — silly but harmless |

### The debt from the last step is paid, and I checked it on disk

| | files under `public/images/uploads` |
|---|---|
| before | 14 |
| article created with a cover and one gallery photograph | 27 |
| **article deleted** | **14** — all thirteen of its files gone, none of the others touched |

And the case that would have been easy to get wrong: two articles pointing at the *same* photograph.
Deleting the first left all 7 files in place; deleting the second removed them. Rows and files cannot
disagree.

### A hole I found, not yet exploitable, that the next step would make live

`isUploadedName()` decides whether a name is safe to delete files for, and it only checks the prefix.
A name of `uploads/articles/../../static/hero-kickboxing` passes it, and the delete glob then matches
**8 files in `public/images/static/`** — the site's own hero. I confirmed this by running the glob.

Nothing can reach it today: only the service writes these names, and it writes UUIDs. But the article
form is the step that starts accepting image references from a request, so the guard has to hold
before that lands. This goes into the next prompt as an invariant, not as a patch.

Also open: a 1×1 upload is accepted; step 17 still owns error-page polish.

## Prompt 28 — writing an article: title, summary, body, date (step 16 part two)

`php artisan test` **112 passed (2001 assertions)**. `./vendor/bin/pint --test` passed. `npm run build` clean.

New: `AdminArticleContentController` (create/store/edit/update), `ArticleContentRequest`,
`ArticleBodyContract`, `ArticleBodyValidator`, `RichTextEditor.jsx`, `ArticleForm.jsx`. TipTap 3
added to `package.json`. The dashboard grew a «Νέο άρθρο» link, so the old test forbidding `<Link>`
there was correctly relaxed.

### The editor does not reach visitors

| chunk | bytes | prosemirror/tiptap occurrences |
|---|---|---|
| `app.js` | 449,328 | **0** |
| `News.js` | 6,361 | **0** |
| `Article.js` | 15,546 | **0** |
| `Home.js`, `ArticleGrid.js` | 15,189 / 3,264 | **0** |
| `RichTextEditor.js` | 394,470 | (all of it) |

The editor is a 394 kB chunk of its own, and the manifest confirms `/news` pulls only
`app.jsx` + `News` + `ArticleGrid`. `app.js` looks like it tripled, from 137 kB — it did not: the
previously separate `jsx-runtime` chunk (311.68 kB) was folded into it. 137 + 312 ≈ 449.

### The slug survived a rename — read from the database

| | slug | published_at |
|---|---|---|
| before | `agonas-stin-kavala` | 2026-09-02 12:22:01 |
| after retitling to «Εντελώς άλλος τίτλος» | **`agonas-stin-kavala`** | unchanged |

### Nothing the renderer cannot render gets stored silently

Every construct the renderer does not know is refused, in Greek, naming the offender:

| body sent | outcome |
|---|---|
| `underline` mark | REFUSED — «μορφοποίηση που δεν υποστηρίζεται (underline)» |
| heading level 1 | REFUSED — «μόνο επίπεδο 2 ή 3» |
| `codeBlock` | REFUSED |
| `image` node | REFUSED |
| link with `javascript:` href | REFUSED — «πρέπει να ξεκινούν με http ή https» |
| a body that is not a `doc` | REFUSED |
| the text `<script>alert(1)</script>` | accepted as **text**, and renders as `&lt;script&gt;…` |

So the drift I was worried about cannot lose a paragraph in silence.

### BUG — an edit can destroy a live article's publication date

The publication date is an editable `datetime-local` field, and it is `nullable`. Emptying it and
saving does this:

| | is_visible | published_at | on the public archive |
|---|---|---|---|
| before | 1 | 2026-09-02 | listed |
| after saving with the date field cleared | **1** | **NULL** | **gone** |

The article vanishes from the public site, and the date it originally went live is destroyed with no
way back. This is the exact fact the dashboard step went to trouble to protect: `published_at` is the
sole source of ordering and a historical fact about the article. The dashboard does at least label
the result «Ορατό χωρίς ημερομηνία», so the owner can see *that* something is wrong — but not what
the date used to be.

**Partly my prompt.** I wrote that saving an edit must not "disturb the date of something already
published", which reads as a ban on changing it behind the owner's back. An explicit field the owner
empties themselves is not behind their back, so the requirement was ambiguous where it needed to be
flat. The next prompt states it as an invariant with no room: a live article cannot leave the public
site as a side effect of editing its words.

### Smaller finding — the drift guard checks two declarations, not the editor

`contractMatches()` compares a hard-coded JS list against the server's contract. It never asks the
editor what its schema actually permits, so it cannot catch the case that is actually live: TipTap 3's
StarterKit ships `Underline` and headings 1–6, and neither is switched off. There is no underline
button and no h1 button, but `Cmd+U` and `Cmd+Alt+1` are bound and work. The owner can press a
keyboard shortcut everybody's fingers know, write for ten minutes, and only learn at save that it was
never allowed. Refused rather than silently lost — the invariant holds — but the wrong moment to find
out.

## Prompt 29 — the editing screen can no longer take a live article off the site

`php artisan test` **114 passed (2038 assertions)**. `./vendor/bin/pint --test` passed.
The fix is one line in `AdminArticleContentController::update()`: an absent or empty date leaves the
stored one alone.

I re-ran the scenario that caught it, plus the two ways around the form:

| what was sent for the date on a live article | published_at afterwards | public archive |
|---|---|---|
| the field, emptied (what the form sends) | **kept, 2026-09-02** | still listed |
| the field absent entirely | **kept** | still listed |
| an explicit `null` | **kept** | still listed |

No path through this screen destroys the date any more.

### Still true, and I am leaving it: a future date removes a live article, silently

Setting the date of a published article to next year and saving leaves `is_visible` at 1, writes
`2027-09-12`, and the article **vanishes from the public archive**. Nothing on the save says so; the
owner only learns it from the dashboard, which does label it «Προγραμματισμένο».

This is not the same defect. The owner typed that value, nothing is destroyed, and scheduling an
article forward is a legitimate thing to want. But «Το άρθρο αποθηκεύτηκε.» is the same message
whether the save left the article on the site or took it off, and that is the part worth changing
when the writing screen is next open — together with the fact that emptying the date field now does
nothing at all and still reports success, so the owner is told their change was saved when it was
ignored.

Neither is data loss. Both go on the list for the polish step rather than another round now.

## Prompt 30 — the editor's vocabulary now comes from one place, and the check is real

`php artisan test` **114 passed (2071 assertions)**. `./vendor/bin/pint --test` passed. `npm run build`
clean; `RichTextEditor` still its own 394 kB chunk, `app.js` unchanged at 449 kB.

The duplicate JavaScript list is gone. `articleEditorSchema.js` builds the editor's extensions *from*
the server's contract, and the check reads `editor.schema` — the schema ProseMirror actually built —
instead of comparing two hand-written descriptions of it.

### I asked the editor myself, in Node, outside their test

```
NODES: blockquote, bulletList, doc, hardBreak, heading, listItem, orderedList, paragraph, text
MARKS: bold, italic, link
heading levels: [2,3]
toggleUnderline exists: false     toggleStrike exists: false     toggleCodeBlock exists: false
toggleHeading({level:1}) applied: false      toggleHeading({level:2}) applied: true
```

Nine nodes and three marks — exactly `ArticleBodyContract`. Underline, strike and code block have no
command to bind a shortcut to, so `Cmd+U` has nothing to do, and a level-one heading cannot be made.
A mark that is not in the schema cannot survive a paste either: ProseMirror drops what it cannot
represent, so a Facebook post with underlined text now arrives as text rather than as a body the
server will reject ten minutes later.

### The check fails when it should — I broke it on purpose, twice

| mutation | result |
|---|---|
| removed `blockquote` from the PHP contract | **test failed**, diff naming the missing node |
| re-enabled `underline` in the editor's extensions | **test failed**, diff showing `+ 'underline'` |

Both restored; suite green again. This is the first test in the project that verifies the front end
by *running* it — it boots a real TipTap editor in Node and reads its schema — rather than matching
strings in a source file. It is the pattern the shell tests and `DatabaseContainerTest` should follow
when their cleanup step comes; part of the same test still string-matches the renderer's source for
each mark, which is the weaker half.

One consequence worth knowing: this test needs Node to run. That is fine locally and irrelevant on the
target host, which never runs the suite.

## Prompt 31 — the article's photographs: cover, gallery, ordering

**The suite is not green.** `php artisan test` → **1 failed, 113 passed (2058 assertions)**.
`./vendor/bin/pint --test` passed; `npm run build` clean. This was reported to me as done.

The failure is in the test, not the feature: `StylesheetTest` treats any `text-…` class as a colour
utility unless it appears in a hand-kept allow-list, and `ArticleForm.jsx` legitimately introduced
`text-center`, which is not in that list. It is the same list-of-exceptions design that keeps costing
us — but a red suite is a red suite, and I only found this by running it.

### What is genuinely right

The traversal hole is closed. `isUploadedName()` now matches a UUID, not a prefix:

| name | accepted? | files the delete glob reaches |
|---|---|---|
| `uploads/articles/../../static/hero-kickboxing` | **no** | **0** (it found 8 before) |
| a real UUID name | yes | its own |
| `uploads/articles/not-a-uuid` | no | 0 |

Files and rows still agree, measured on disk:

| | files |
|---|---|
| cover uploaded | 5 |
| cover **replaced** | 5 — the previous cover's files: **0 left** |
| four gallery photographs | 29 |
| the **second** removed | 23, and its own files: **0 left** |

Order survives that removal: `πρώτη, δεύτερη, τρίτη, τέταρτη` → `πρώτη, τρίτη, τέταρτη`, renumbered
0,1,2 with the relative order intact.

An upload larger than PHP's own `post_max_size` produces a redirect back to the editing screen and
«Η φωτογραφία είναι μεγαλύτερη από όσο δέχεται ο server. Ανεβάστε μικρότερη φωτογραφία.» — not the
419 or the bogus "field is required" that this failure normally causes.

### MY MISTAKE — the upload ceiling now rejects an ordinary phone photograph

`max_pixels` was lowered from 24 MP to **8 MP** and `max_dimension` from 7000 to **4000**. An iPhone
or Samsung photograph is 4032×3024 — 12.2 MP. I put one through:

```
REFUSED: Οι διαστάσεις της φωτογραφίας είναι εκτός ορίων. Ανεβάστε μικρότερη φωτογραφία.
```

The owner cannot upload a photograph from their phone. That is the only kind they have.

This came from my prompt, and from a claim in it I had not checked. I wrote that decoding consumes
several bytes per pixel against PHP's 128 MB `memory_limit`, so 24 MP could exhaust it and hand the
owner a blank page. That is false. GD allocates image buffers outside the Zend memory manager, so
they do not count against `memory_limit` at all. Measured:

```
decoded 6000x4000 (24.0 MP) under memory_limit=128M -> OK, zend peak 2.0 MB
resampled to 2400x1600 and encoded webp -> OK
```

A 24-megapixel photograph decodes, resizes and encodes fine on this configuration. I asserted a
technical fact I had not verified, framed the requirement as "make the two numbers agree", and the
number that moved was the wrong one. The ceiling has to come back up to somewhere that comfortably
admits a 12 MP phone photograph.

### For the owner

`2026_09_12_000001_add_cover_alt_text_to_articles` is **pending** on the development database —
`php artisan migrate` before using the editing screen.

## Prompt 32 — the ceiling comes back up, and the suite goes green

`php artisan test` **120 passed (2128 assertions)**. `./vendor/bin/pint --test` passed.

The limits are back to `max_pixels` 24,000,000 and `max_dimension` 7000, and I re-ran both
photographs through the real path:

| photograph | result |
|---|---|
| 4032×3024 — an ordinary phone photograph, 12.2 MP | **accepted**, 7 derivatives |
| 6000×4000 — 24 MP | **accepted**, 7 derivatives |

The regression I caused is gone.

### A new one, in the gap between two numbers

`max_bytes` is 12 MB. PHP's own `upload_max_filesize` here is **10 MB**. A photograph in between is
refused by PHP before any of our code runs — and because that is not the same failure as exceeding
`post_max_size`, the handler written last step does not catch it. What the owner gets is:

> The photo failed to upload.

English, from the framework, reaching a person who reads Greek — which `CLAUDE.md` names as a bug
outright — and it does not say the photograph is too large or what to do. On cPanel the host's own
limit is usually lower than ours, so this band is not a corner case there; it is the common case.

### The stylesheet check is narrower but still a list

The fix adds a regex naming `left|center|right|justify|start|end`, plus the existing hand-kept list of
tokens. It is green and text alignments are a closed set, so it will not rot the way the token list
does. But the underlying shape is unchanged: any ordinary Tailwind utility beginning with `bg-`,
`border-`, `fill-`, `decoration-` or `text-` and ending in a word — `bg-cover`, `border-collapse`,
`text-balance`, `fill-none` — is still read as an undefined colour unless somebody adds it to a list.
Not worth another round now; worth doing properly in the suite cleanup step, where the answer is
probably to ask the compiled stylesheet whether a class exists rather than to guess from its name.
