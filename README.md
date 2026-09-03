# Karanikolis Laravel

Empty Laravel 13 skeleton for rebuilding the existing site as a Laravel application.

## Stack

- PHP 8.3+; local machine is using PHP 8.4
- Laravel 13
- MariaDB 10.11 in Docker for development and tests, matching cPanel production
- Inertia + React 19 in the browser
- Vite-built assets
- Tailwind CSS v4, configured from CSS
- Pest 4 for tests
- Laravel Pint for code style
- Database-backed sessions, cache, and queue
- Greek application locale with English fallback

## Cold Checkout Setup

1. Install PHP 8.3+, Composer, and Docker Desktop.
2. Copy the example environment file:

```bash
cp .env.example .env
```

3. Open `.env` and set local-only values for `APP_KEY`, `DB_PASSWORD`, and `DB_ROOT_PASSWORD`. Keep `DB_PORT=33070`; this project maps MariaDB to host port 33070 so it does not collide with a local MySQL or MariaDB server on 3306.
4. Install PHP dependencies:

```bash
composer install
```

5. Install frontend dependencies:

```bash
npm install
```

6. Generate the application key if you did not set `APP_KEY` manually:

```bash
php artisan key:generate
```

7. Start MariaDB:

```bash
docker compose up -d
```

On a fresh Docker volume, the init script creates both `DB_DATABASE` and `DB_TEST_DATABASE` with
`CHARACTER SET utf8mb4` and `COLLATE utf8mb4_unicode_ci`. The compose file deliberately does not use
the image's `MARIADB_DATABASE` shortcut, because that path inherits server defaults.

8. Run the application migrations:

```bash
php artisan migrate:fresh
```

9. For day-to-day development, start the Laravel server and Vite dev server together:

```bash
composer run dev
```

The app will be available at http://localhost:8000.

For a production-style local check, build the static assets first and then run only the PHP server:

```bash
npm run build
php artisan serve
```

## Verification

Use these commands before handing off changes:

```bash
php artisan db:show
npm run build
php artisan test
./vendor/bin/pint --test
```

Tests use the `karanikolis_test` database in the same MariaDB 10.11 container. They do not use SQLite and do not read or write the development database.

## Database Configuration

The Docker image is pinned to `mariadb:10.11` because production is MariaDB 10.11 on cPanel. Do not
bump it to MariaDB 11.x: local dumps must restore on production, and 11.x can create collations that
10.11 does not know.

The development database connection has one source of truth: `.env`.

- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` are used by Artisan and the application.
- `docker-compose.yml` reads the same `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` values when publishing and initializing MariaDB.
- The init script creates `DB_DATABASE` explicitly with `CHARACTER SET utf8mb4` and `COLLATE utf8mb4_unicode_ci`.

The test database name also has one source of truth: `DB_TEST_DATABASE` in `.env`.

- `php artisan test` reads `DB_HOST`, `DB_PORT`, `DB_USERNAME`, and `DB_PASSWORD` from `.env`.
- During tests only, Laravel uses `DB_TEST_DATABASE` instead of `DB_DATABASE`.
- The init script creates `DB_TEST_DATABASE` explicitly with `CHARACTER SET utf8mb4` and `COLLATE utf8mb4_unicode_ci`.
- `phpunit.xml` intentionally does not hardcode the host port or database name.
- If the test runner cannot reach `DB_HOST:DB_PORT`, or if `DB_TEST_DATABASE` is empty or equals `DB_DATABASE`, the application fails before running migrations with a message naming the bad setting.

To change the MariaDB host port, update `DB_PORT` in `.env`, then recreate the container mapping:

```bash
docker compose up -d
```

To change the test database name, update `DB_TEST_DATABASE` in `.env`. On a brand-new Docker volume, the init script creates that database automatically. On an existing Docker volume, create and grant the new database manually inside the container, or recreate the local database volume if you can discard local data:

```bash
docker compose down -v
docker compose up -d
```
