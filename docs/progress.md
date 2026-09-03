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
