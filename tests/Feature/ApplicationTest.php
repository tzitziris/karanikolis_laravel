<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('serves the empty Greek application shell', function () {
    $this->get('/')
        ->assertOk();
});

it('serves the root page through Inertia', function () {
    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Placeholder')
            ->where('message', 'Η εφαρμογή Laravel είναι έτοιμη.')
        );
});

it('runs the test suite against the dedicated MariaDB database', function () {
    $connection = DB::connection();
    $testDatabase = config('database.connections.mariadb.database');
    $developmentDatabase = config('database.connections.mariadb.development_database');
    $version = strtolower($connection->selectOne('select version() as version')->version);

    expect(config('database.default'))->toBe('mariadb')
        ->and($connection->getDatabaseName())->not->toBe($developmentDatabase)
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

it('keeps server-side rendering out of the frontend pipeline', function () {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $scripts = implode(' ', $package['scripts'] ?? []);
    $dependencies = array_keys($package['dependencies'] ?? []);
    $devDependencies = array_keys($package['devDependencies'] ?? []);
    $ssrEntries = File::glob(resource_path('js/ssr.*'));

    expect(config('inertia.ssr.enabled'))->toBeFalse()
        ->and($scripts)->not->toContain('ssr')
        ->and($dependencies)->not->toContain('@inertiajs/server')
        ->and($devDependencies)->not->toContain('@inertiajs/server')
        ->and($ssrEntries)->toBe([]);
});
