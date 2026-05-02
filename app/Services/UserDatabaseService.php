<?php

namespace App\Services;

class UserDatabaseService
{
    const BASE_PATH = 'databases/user_dbs';

    private string $mainDb;

    public function __construct()
    {
        $this->mainDb = database_path('database.sqlite');
    }

    public function getDatabasePath(int $userId): string
    {
        return base_path(self::BASE_PATH."/user_{$userId}.sqlite");
    }

    public function getMainDatabasePath(): string
    {
        return $this->mainDb;
    }

    public function setUserDatabase(int $userId): void
    {
        $dbPath = $this->getDatabasePath($userId);
        config(['database.connections.sqlite.database' => $dbPath]);
    }

    public function setMainDatabase(): void
    {
        config(['database.connections.sqlite.database' => $this->mainDb]);
    }

    public function createUserDatabase(int $userId): bool
    {
        $dbPath = $this->getDatabasePath($userId);

        if (file_exists($dbPath)) {
            return true;
        }

        touch($dbPath);

        return true;
    }

    public function deleteUserDatabase(int $userId): bool
    {
        $dbPath = $this->getDatabasePath($userId);

        if (file_exists($dbPath)) {
            unlink($dbPath);

            return true;
        }

        return false;
    }

    public function userDatabaseExists(int $userId): bool
    {
        return file_exists($this->getDatabasePath($userId));
    }

    public function getAllUserDatabases(): array
    {
        $path = base_path(self::BASE_PATH);

        if (! is_dir($path)) {
            return [];
        }

        $files = glob($path.'/*.sqlite');
        $userIds = [];

        foreach ($files as $file) {
            $filename = basename($file, '.sqlite');
            if (preg_match('/user_(\d+)/', $filename, $matches)) {
                $userIds[] = (int) $matches[1];
            }
        }

        return $userIds;
    }

    public function backupUserDatabase(int $userId): ?string
    {
        $dbPath = $this->getDatabasePath($userId);

        if (! file_exists($dbPath)) {
            return null;
        }

        $backupPath = str_replace('.sqlite', '_backup_'.date('Y-m-d').'.sqlite', $dbPath);

        if (copy($dbPath, $backupPath)) {
            return $backupPath;
        }

        return null;
    }
}
