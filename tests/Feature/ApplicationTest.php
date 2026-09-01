<?php

use App\Models\User;
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
    $testDatabase = config('database.connections.mariadb.database');
    $version = strtolower($connection->selectOne('select version() as version')->version);

    expect(config('database.default'))->toBe('mariadb')
        ->and($connection->getDatabaseName())->toBe($testDatabase)
        ->and($connection->getDriverName())->toBe('mariadb')
        ->and($version)->toContain('mariadb');
});

it('creates a user through the model factory on the test database', function () {
    $user = User::factory()->create();
    $testDatabase = config('database.connections.mariadb.database');

    expect($user->exists)->toBeTrue()
        ->and($user->getConnection()->getDatabaseName())->toBe($testDatabase);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
    ]);
});
