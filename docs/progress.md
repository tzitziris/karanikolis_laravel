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
