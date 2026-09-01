<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('serves the empty Greek application shell', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Κενή εφαρμογή Laravel');
});

it('runs the test suite against the dedicated MariaDB database', function () {
    $connection = DB::connection();
    $version = strtolower($connection->selectOne('select version() as version')->version);

    expect(config('database.default'))->toBe('mariadb')
        ->and($connection->getDatabaseName())->toBe('karanikolis_test')
        ->and($connection->getDriverName())->toBe('mariadb')
        ->and($version)->toContain('mariadb');
});
