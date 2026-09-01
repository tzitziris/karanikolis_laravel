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
