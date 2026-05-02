<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MultiTenantServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Set up dynamic database connections
        $this->app['db']->extend('tenant', function ($config, $name) {
            $userId = auth()->id();
            if (!$userId) {
                // Fallback to admin DB for unauthenticated requests
                $config['database'] = database_path('admin.sqlite');
            } else {
                $config['database'] = database_path("user_{$userId}.sqlite");
            }

            return $this->app['db.factory']->make($config, $name);
        });

        // Switch to user DB after authentication
        if (auth()->check()) {
            $userId = auth()->id();
            $this->switchToUserDatabase($userId);
        }
    }

    public function switchToUserDatabase($userId)
    {
        $databasePath = database_path("user_{$userId}.sqlite");

        // Create database file if it doesn't exist
        if (!file_exists($databasePath)) {
            touch($databasePath);
        }

        // Configure the connection
        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ]);

        // Set as default connection for this request
        DB::setDefaultConnection('tenant');

        // Run migrations if database is empty
        $this->ensureDatabaseMigrated($userId);
    }

    private function ensureDatabaseMigrated($userId)
    {
        $databasePath = database_path("user_{$userId}.sqlite");

        // Check if migrations table exists
        try {
            $migrationCount = DB::table('migrations')->count();
        } catch (\Exception $e) {
            // Database not migrated, run migrations
            $this->runMigrationsForUser($userId);
        }
    }

    private function runMigrationsForUser($userId)
    {
        // Temporarily switch back to admin DB to get migration status
        DB::setDefaultConnection('admin');

        // Get all migration files
        $migrationFiles = glob(database_path('migrations/*.php'));

        // Switch back to user DB
        $this->switchToUserDatabase($userId);

        // Run migrations for user DB
        foreach ($migrationFiles as $file) {
            $migration = include $file;
            if (method_exists($migration, 'up')) {
                try {
                    $migration->up();
                } catch (\Exception $e) {
                    // Migration might already exist, continue
                }
            }
        }
    }
}