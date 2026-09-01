# AI-CONTEXT — read this first

If you open a fresh session with zero context, this file alone should tell you what this project is,
where it stands, and how we work.

## What this is

A Greek-language site for the kickboxing school **Μαχητές Ελευθερούπολης** (Eleftheroupoli, Kavala;
head coach Παναγιώτης Καρανικολής). Public pages: Home, Coaches & Athletes, Schedule, News, About.
An admin area publishes news articles (rich text, images, YouTube links).

It is a **rebuild of `karanikolis_site`** — Next.js 16 + Supabase, live at
https://karanikolis-site.vercel.app — in **Laravel 13 + Inertia + React 19**, so it can be hosted on
**cPanel shared hosting with no terminal and no Node process**. Next.js cannot go there: it needs a
Node runtime for SSR/RSC and an external database.

The stack is copied from `queen-laravel`, another cPanel-hosted Laravel + Inertia + React project by
the same owner. Both `karanikolis_site` and `queen-laravel` are **read-only references**.

## Why we are doing it, beyond the hosting move

1. **The old site freezes between pages.** Measured on the live site: navigation itself is instant
   (payload prefetched, no long tasks). What freezes is the *content*, hidden until GSAP finishes
   setting up behind `document.fonts.ready` → `rAF` → `requestIdleCallback`, plus up to another
   550 ms of font waiting in `lib/hero-intro.ts`. Fonts come from Google Fonts, so a cold cache
   stretches that window. Invariant 1 exists to make this impossible to reintroduce.
2. **No image pipeline.** Uploads went to Supabase Storage untouched; optimisation was Vercel's
   image optimizer, which does not exist on cPanel. Without our own pipeline the site would serve
   original JPEGs. Invariant 2.
3. **Facebook.** The owner posts from a *personal profile*. The Graph API cannot read personal
   profiles and scraping breaks Facebook's terms, so **there is no automatic import**. Articles are
   published through the admin panel, as today.

## Status

**Step 1 of 18 done (2026-09-01).** Laravel 13 skeleton, MariaDB in Docker, Pest 4 running against
MariaDB, Pint clean. 2 tests pass. Nothing of the actual site exists yet.

The roadmap is 18 Codex prompts: foundations (1-3), animation layer and image pipeline (4-5), the
four static pages (6-9), news public side (10-13), admin (14-16), hardening and deploy packaging
(17-18). See `progress.md`.

## How we work

The owner is the product owner. **Claude is the architect**: discusses, writes ONE English prompt at
a time for **Codex in VSCode**, reviews what comes back by actually running things, records it here,
then writes the next prompt. Claude does not write application code. Claude never commits — it hands
the owner a command. The owner runs deploys himself.

## Local development

```bash
docker compose up -d      # MariaDB on host port 33070
php artisan migrate:fresh
php artisan serve
```

Verification, every time:

```bash
php artisan test && ./vendor/bin/pint --test
```

## Where to look next

- `decisions.md` — why things are the way they are. Read before questioning a choice.
- `progress.md` — what was built and found, step by step.
- `AGENTS.md` (repo root) — the rules Codex must follow on every run.
