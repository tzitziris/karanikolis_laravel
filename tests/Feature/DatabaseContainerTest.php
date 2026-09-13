<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * These tests used to read docker-compose.yml and the docs and check that the
 * right strings were written there. That passed green while a different MariaDB
 * was actually running — which is the only failure the file could have caught.
 * Everything here asks the connection the suite is really using.
 */
it('runs the suite against the MariaDB line the production host runs', function () {
    expect(DB::connection()->getDriverName())->toBe('mariadb', 'The suite is not running on MariaDB.');

    $version = DB::selectOne('select version() as version')->version;

    // cPanel runs the 10.11 line. A newer server accepts syntax and collations
    // that the host would reject, and the suite would never say so.
    expect($version)->toContain('MariaDB');
    expect(str_starts_with($version, '10.11.'))->toBeTrue("The database is {$version}, not the 10.11 line.");
});

it('stores every table in the one charset and collation the site is written for', function () {
    $database = DB::selectOne('select database() as name')->name;

    $wrongTables = collect(DB::select(
        'select table_name as name, table_collation as collation_name
         from information_schema.tables
         where table_schema = ?',
        [$database],
    ))
        ->reject(fn (object $table): bool => $table->collation_name === 'utf8mb4_unicode_ci')
        ->map(fn (object $table): string => "{$table->name} ({$table->collation_name})")
        ->all();

    expect($wrongTables)->toBe([], 'These tables are not utf8mb4_unicode_ci.');
});

it('leaves every text column in the site collation, apart from the JSON that MariaDB owns', function () {
    $database = DB::selectOne('select database() as name')->name;

    // MariaDB has no JSON type: it is LONGTEXT with a json_valid constraint, and
    // it insists on utf8mb4_bin. Rather than write down which columns those are
    // and let the list rot, ask the constraints which ones they are.
    $jsonColumns = collect(DB::select(
        'select table_name as table_name, check_clause as clause
         from information_schema.check_constraints
         where constraint_schema = ?',
        [$database],
    ))
        ->filter(fn (object $row): bool => str_starts_with($row->clause, 'json_valid('))
        ->map(fn (object $row): string => $row->table_name.'.'.trim((string) preg_replace('/^json_valid\\(`(.+)`\\)$/', '$1', $row->clause)))
        ->all();

    $columns = collect(DB::select(
        'select table_name as table_name, column_name as column_name, collation_name as collation_name
         from information_schema.columns
         where table_schema = ? and collation_name is not null',
        [$database],
    ))->map(fn (object $column): array => [
        'collation' => $column->collation_name,
        'name' => $column->table_name.'.'.$column->column_name,
    ]);

    // One column left behind by a migration is enough to mangle a Greek title
    // or to sort the archive differently from every other list.
    $wrong = $columns
        ->reject(fn (array $column): bool => in_array($column['name'], $jsonColumns, true))
        ->reject(fn (array $column): bool => $column['collation'] === 'utf8mb4_unicode_ci')
        ->map(fn (array $column): string => "{$column['name']} ({$column['collation']})")
        ->all();

    expect($wrong)->toBe([], 'These columns are not utf8mb4_unicode_ci.');

    // And the exception is not a free pass: the JSON columns must be the ones
    // MariaDB made binary, not somewhere a migration quietly went its own way.
    $jsonCollations = $columns
        ->filter(fn (array $column): bool => in_array($column['name'], $jsonColumns, true))
        ->reject(fn (array $column): bool => $column['collation'] === 'utf8mb4_bin')
        ->map(fn (array $column): string => "{$column['name']} ({$column['collation']})")
        ->all();

    expect($jsonColumns)->not->toBeEmpty();
    expect($jsonCollations)->toBe([], 'These JSON columns are not utf8mb4_bin.');
});

it('keeps Greek text and four-byte characters exactly as they were written', function () {
    // utf8mb3 accepts Greek and silently loses anything above the basic plane,
    // so a Greek-only check would pass on the wrong charset.
    $title = 'Αγωνιστική ημέρα — Καβάλα ⚡ 🥊';

    $article = Article::factory()->create(['title' => $title]);

    expect(Article::query()->whereKey($article->id)->value('title'))->toBe($title);
    expect(DB::table('articles')->where('id', $article->id)->value('title'))->toBe($title);
});

it('compares Greek the way utf8mb4_unicode_ci promises, so a search is not case sensitive', function () {
    Article::factory()->create(['title' => 'ΠΡΟΠΟΝΗΣΗ ΣΤΟ ΡΙΝΓΚ']);

    // This is what the collation buys: the owner searching in lower case finds
    // a title written in capitals.
    expect(Article::query()->where('title', 'προπονηση στο ρινγκ')->exists())->toBeTrue();
});
