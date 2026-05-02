<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupUserDatabase extends Command
{
    protected $signature = 'db:setup-user {user_id}';
    protected $description = 'Set up database for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $databasePath = database_path("user_{$userId}.sqlite");

        $this->info("Setting up database for user {$userId}");

        // Create database file if it doesn't exist
        if (!file_exists($databasePath)) {
            touch($databasePath);
            $this->info("Created database file: {$databasePath}");
        }

        // Switch to user database
        config(['database.connections.user_db' => [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        DB::setDefaultConnection('user_db');

        // Run migrations for user database
        $this->call('migrate', [
            '--database' => 'user_db',
            '--path' => 'database/migrations/user',
        ]);

        $this->info("Database setup complete for user {$userId}");
    }
}