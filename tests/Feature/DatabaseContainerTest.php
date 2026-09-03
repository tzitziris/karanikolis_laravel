<?php

use Illuminate\Support\Facades\File;

it('pins the local MariaDB container to the production version line', function () {
    $compose = File::get(base_path('docker-compose.yml'));
    $decisions = File::get(base_path('docs/decisions.md'));
    $readme = File::get(base_path('README.md'));

    expect($compose)->toContain('image: mariadb:10.11')
        ->and($compose)->not->toContain('mariadb:11')
        ->and($compose)->not->toContain('mariadb:latest')
        ->and($decisions)->toContain('MariaDB 10.11 in Docker locally')
        ->and($decisions)->toContain('matching the cPanel production line')
        ->and($readme)->toContain('MariaDB 10.11');
});

it('creates every local project database with an explicit charset and collation', function () {
    $compose = File::get(base_path('docker-compose.yml'));
    $initScript = File::get(base_path('docker/mariadb/init/01-create-test-database.sh'));

    expect($compose)->not->toContain('MARIADB_DATABASE:')
        ->and($compose)->toContain('MARIADB_APP_DATABASE: "${DB_DATABASE}"')
        ->and($compose)->toContain('--character-set-server=utf8mb4')
        ->and($compose)->toContain('--collation-server=utf8mb4_unicode_ci')
        ->and($initScript)->toContain('MARIADB_APP_DATABASE:?MARIADB_APP_DATABASE is required')
        ->and($initScript)->toContain('CREATE DATABASE IF NOT EXISTS ${app_database}')
        ->and($initScript)->toContain('CREATE DATABASE IF NOT EXISTS ${test_database}')
        ->and(substr_count($initScript, 'CHARACTER SET utf8mb4'))->toBe(2)
        ->and(substr_count($initScript, 'COLLATE utf8mb4_unicode_ci'))->toBe(2)
        ->and($initScript)->toContain('GRANT ALL PRIVILEGES ON ${app_database}.*')
        ->and($initScript)->toContain('GRANT ALL PRIVILEGES ON ${test_database}.*');
});
