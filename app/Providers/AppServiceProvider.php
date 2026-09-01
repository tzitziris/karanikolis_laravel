<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->environment('testing')) {
            return;
        }

        $connectionName = config('database.default');
        $developmentDatabase = env('DB_DATABASE');

        if ($connectionName !== 'mariadb') {
            throw new RuntimeException(
                "Test database misconfiguration: DB_CONNECTION must be 'mariadb' during tests; '{$connectionName}' is configured."
            );
        }

        $configuredTestDatabase = env('DB_TEST_DATABASE');

        if (! is_string($configuredTestDatabase) || $configuredTestDatabase === '') {
            throw new RuntimeException(
                'Test database misconfiguration: DB_TEST_DATABASE must be set for the test suite.'
            );
        }

        config(["database.connections.{$connectionName}.database" => $configuredTestDatabase]);

        $testDatabase = config("database.connections.{$connectionName}.database");

        if ($testDatabase === $developmentDatabase) {
            throw new RuntimeException(
                "Test database misconfiguration: DB_TEST_DATABASE must not equal DB_DATABASE ('{$developmentDatabase}')."
            );
        }

        $host = config("database.connections.{$connectionName}.host");
        $port = (int) config("database.connections.{$connectionName}.port");
        $socket = @fsockopen($host, $port, $errorCode, $errorMessage, 1.0);

        if (! is_resource($socket)) {
            throw new RuntimeException(
                "Test database misconfiguration: DB_HOST={$host} DB_PORT={$port} is not reachable for the MariaDB test connection. ".
                "Docker Compose publishes MariaDB from DB_PORT, so the environment used by 'docker compose up -d' and 'php artisan test' must match. ".
                "Socket error {$errorCode}: {$errorMessage}"
            );
        }

        fclose($socket);
    }
}
