# Karanikolis Laravel

Empty Laravel 13 skeleton for rebuilding the existing site as a Laravel application.

## Stack

- PHP 8.3+; local machine is using PHP 8.4
- Laravel 13
- MariaDB in Docker for development and tests
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

5. Generate the application key if you did not set `APP_KEY` manually:

```bash
php artisan key:generate
```

6. Start MariaDB:

```bash
docker compose up -d
```

7. Run the application migrations:

```bash
php artisan migrate:fresh
```

8. Start the local Laravel server:

```bash
php artisan serve
```

The app will be available at http://localhost:8000.

## Verification

Use these commands before handing off changes:

```bash
php artisan db:show
php artisan test
./vendor/bin/pint --test
```

Tests use the `karanikolis_test` database in the same MariaDB container. They do not use SQLite and do not read or write the development database.

## Database Configuration

The development database connection has one source of truth: `.env`.

- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` are used by Artisan and the application.
- `docker-compose.yml` reads the same `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` values when publishing and initializing MariaDB.

The test database name also has one source of truth: `DB_TEST_DATABASE` in `.env`.

- `php artisan test` reads `DB_HOST`, `DB_PORT`, `DB_USERNAME`, and `DB_PASSWORD` from `.env`.
- During tests only, Laravel uses `DB_TEST_DATABASE` instead of `DB_DATABASE`.
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
