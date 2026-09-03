# Decisions (locked)

ADR-style log. Check here before asking "why is it done this way".

## Scope

- **No Facebook auto-import.** The owner posts from a personal Facebook profile. The Graph API reads
  Pages, not personal profiles (`user_posts` does not allow polling someone else's profile), and
  scraping violates the terms and breaks constantly. Articles are published through the admin panel.
  *Rejected:* scraping; `user_posts`. *If a Page is ever created*, import becomes a separate feature
  keyed on a unique `facebook_post_id` with a **tombstone on delete**, so a post the owner removed
  from the site is not silently re-imported on the next poll.
  Note: the old renderer already converts `fbcdn.net` emoji images back into characters, because the
  owner pastes Facebook text into the editor. That behaviour must survive the port.
- **Schedule, coaches/athletes, About and the home-page copy stay hardcoded in components.** Only
  news lives in the database. *Rejected:* CMS tables for all of it — the owner does not need to edit
  them, and it would add tables, CRUD and uploads for content that changes once a year.

## Stack

- **Laravel 13 + Inertia + React 19, client-side, no SSR.** The host has no Node process.
- **Tailwind CSS v4 is kept**, unlike `queen-laravel`, which deliberately removed Tailwind. The whole
  design of `karanikolis_site` is expressed as Tailwind utilities over `@theme inline` tokens;
  rewriting ~6,400 lines of JSX to plain CSS is large, risky and buys nothing. Tailwind is
  build-time only — Vite compiles it locally and the server receives finished CSS.
  *Rejected:* porting to plain CSS for symmetry with `queen-laravel`.
- **TipTap JSON is rendered to HTML in PHP, not in the browser**, so the editor ships only in the
  admin bundle and never to visitors. *Rejected:* client-side rendering.
- **Fonts are self-hosted.** Google Fonts was part of the freezing cause, and a strict CSP is simpler
  without a third-party font origin.

## Database

- **MariaDB in Docker locally**, MariaDB on cPanel in production. *Rejected:* SQLite — it silently
  accepts what MariaDB rejects (enums, ALTER TABLE limits, case-insensitive comparison), and the
  difference would only surface after deploying.
- **Tests run against MariaDB**, in their own database in the same container. `queen-laravel` uses
  SQLite `:memory:` for tests; we do not, for the same reason. Cost: slower tests. Worth it.
- **Uploads go directly into `public/`**, no `storage:link` — there is no terminal on the host to
  create the symlink. Copied from `queen-laravel`.

## Correctness rules carried over from reviewing the old site

- **A slug is stored data, not a derived value.** The old `app/news/[slug]/page.tsx` recomputed
  `generateSlug(title)` and redirected when it disagreed with the stored slug — the same question
  answered in two places. Renaming an article's title broke every existing link to it. The slug is
  computed once, at creation, and is then the only truth.
- **`published` does not prove when something was published.** Ordering comes from the
  `published_at` timestamp only; the boolean says whether it is visible, nothing more.

## Typography (2026-09-01)

- **The display font is Roboto Condensed 900, not Bebas Neue.** Bebas Neue is served only in `latin`
  and `latin-ext` — it contains no Greek glyphs. Verified twice: the live site's loaded `@font-face`
  unicode ranges exclude U+0370–U+03FF, and the Google Fonts API refuses to emit a `greek` subset for
  it. Every Greek heading on the old site, including the school's own name, is therefore drawn by the
  CSS fallback chain (`Impact`, `Arial Narrow`, or whatever the OS provides), so headings look
  different on macOS, Windows and Android and the intended condensed display voice never appears.
  Roboto Condensed carries full `greek` and `greek-ext` subsets and is the closest match to that
  intent. *Rejected:* Fira Sans Condensed (softer, less athletic); a single Inter family (loses the
  contrast between headings and body); keeping Bebas (the bug is invisible to whoever built it and
  permanent for everyone else).
- **Fonts are self-hosted and must cover Greek.** A font that cannot render Greek may not be used as
  a text face anywhere on this site.

## Generated webp derivatives are build output, not source

`resources/images/static` holds the 14 source photographs and is the only copy that matters.
`public/images/static` holds the 84 derivatives, which `php artisan images:build-static` reproduces
byte-for-byte from those sources — the command is deterministic and idempotent, and a test fails if
any derivative is missing. They are therefore treated exactly like `public/build`: generated
locally, kept out of git, and included in the deployment archive.

*Consequence to remember:* `scripts/pack-deploy.sh` must include `public/images/static` alongside
`public/build`, and a fresh clone must run `images:build-static` before the suite passes.
