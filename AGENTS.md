# Project instructions

Greek-language website for a kickboxing school ("Μαχητές Ελευθερούπολης", Eleftheroupoli, Kavala).
This is a rebuild of an existing Next.js + Supabase site as Laravel + Inertia + React, so it can be
hosted on **cPanel shared hosting with no terminal and no Node process on the server**.

Read `docs/AI-CONTEXT.md` first. Locked decisions are in `docs/decisions.md` — do not re-litigate them.

## Hard rules

- **Never run git commands.** No `init`, no `add`, no `commit`, no `checkout`, no `branch`. The owner
  commits by hand.
- **Never create, modify or delete `.env`.** It exists and holds real local credentials. New settings
  go in `.env.example`, and say in your summary what the owner must add to `.env`.
- **These directories are read-only references. Never write to them:**
  - `/Users/sophianostzitzires/cursor_ai/karanikolis_site` — the Next.js original being ported
  - `/Users/sophianostzitzires/cursor_ai/queen-laravel` — the Laravel project this stack copies
  Read from them when a step says to port something. Otherwise leave them alone.
- **No Node on the production server.** Assets are built locally with Vite and uploaded. Never add
  anything that needs a Node process at runtime — no SSR, no server-side rendering entry point.
- **Everything a visitor can see is in Greek**, including validation and error messages. An English
  framework message reaching a visitor is a bug.

## The three invariants

1. **Content is never hidden waiting for asynchronous work.** The moment a page's component
   renders, its content is visible. Animation may only enrich a page that already reads correctly.
   An element may start hidden **only** when JavaScript itself applied that hidden state and has
   already committed to revealing it, with a watchdog that reveals everything after a fixed time
   regardless of fonts, network, or errors. Static CSS must never hide content that only JavaScript
   can reveal, and no animation setup may depend on `document.fonts.ready`.
   This is the whole reason for the rebuild — the old site hid its hero behind font loading and felt
   frozen. Note what this rule is **not**: this is a client-rendered SPA with no SSR, so pages do not
   work with JavaScript disabled, and we do not hand-write a second copy of any page's content to
   pretend otherwise. Content lives in exactly one place.
2. **Every bitmap sent to a browser is webp, sized for where it is used.** Static images and uploads
   alike. Whatever asks for an image gets it from one place that guarantees both; there is no second
   path that can serve an original file.
3. **Navigation never hits the network for the page shell.** Inertia client-side visits with
   prefetch. Every GSAP context and ScrollTrigger is destroyed when the page that created it unmounts.

## Conventions

- Laravel 13, PHP 8.3+ (8.4 locally). Pest 4 for tests, Pint for style.
- MariaDB in Docker locally, MariaDB on cPanel in production. Tests run against MariaDB, never SQLite.
- Thin controllers, Form Requests for validation, Services for real work, static Support helpers.
  No Actions, no Repositories unless a step asks for them.
- Uploads go straight into `public/`, never `storage/app/public` — there is no way to run
  `storage:link` on the target host.

## Before you finish

```bash
php artisan test
./vendor/bin/pint --test
```

Both must pass. Report the real output, and say plainly what you did not do.
