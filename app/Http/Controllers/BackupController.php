<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackupFiles();

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        try {
            $filename = 'backup_'.date('Y-m-d_His').'.sqlite';
            $backupPath = database_path('backups/'.$filename);

            if (! File::isDirectory(database_path('backups'))) {
                File::makeDirectory(database_path('backups'), 0755, true);
            }

            DB::statement('VACUUM INTO ?', [$backupPath]);

            return redirect()->route('backups.index')
                ->with('success', 'Backup created successfully: '.$filename);
        } catch (\Exception $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    public function download($filename)
    {
        $sanitizedFilename = basename($filename);

        if (empty($sanitizedFilename) || ! preg_match('/^[\w\-\.]+\.sqlite$/', $sanitizedFilename)) {
            return redirect()->route('backups.index')
                ->with('error', 'Invalid backup filename.');
        }

        $backupPath = database_path('backups/'.$sanitizedFilename);

        if (! File::exists($backupPath)) {
            return redirect()->route('backups.index')
                ->with('error', 'Backup file not found');
        }

        return response()->download($backupPath);
    }

    public function restore(Request $request)
    {
        if (! $request->hasFile('backup_file') || ! $request->file('backup_file')->isValid()) {
            return redirect()->route('backups.index')
                ->with('error', 'The backup file failed to upload.');
        }

        $request->validate([
            'backup_file' => 'required|file|mimes:sqlite,db,sqbpro|max:51200',
        ]);

        try {
            $uploadedFile = $request->file('backup_file');
            $tempFilename = 'temp_restore_'.time().'.sqlite';
            $tempPath = storage_path('app/'.$tempFilename);

            $uploadedFile->move(storage_path('app'), $tempFilename);

            $mimeType = File::mimeType($tempPath);
            if (! in_array($mimeType, ['application/x-sqlite3', 'application/octet-stream', 'inode/x-empty'])) {
                File::delete($tempPath);

                return redirect()->route('backups.index')
                    ->with('error', 'Invalid file type. The uploaded file is not a valid SQLite database.');
            }

            $mainDbPath = database_path('database.sqlite');
            $backupPath = $mainDbPath.'.restore_backup.'.time();

            File::copy($mainDbPath, $backupPath);
            File::copy($tempPath, $mainDbPath);
            File::delete($tempPath);

            return redirect()->route('backups.index')
                ->with('success', 'Database restored successfully. Backup of current data saved.');
        } catch (\Exception $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Restore failed: '.$e->getMessage());
        }
    }

    public function destroy($filename)
    {
        try {
            $sanitizedFilename = basename($filename);

            if (empty($sanitizedFilename) || ! preg_match('/^[\w\-\.]+\.sqlite$/', $sanitizedFilename)) {
                return redirect()->route('backups.index')
                    ->with('error', 'Invalid backup filename.');
            }

            $backupPath = database_path('backups/'.$sanitizedFilename);

            if (! File::exists($backupPath)) {
                return redirect()->route('backups.index')
                    ->with('error', 'Backup file not found');
            }

            File::delete($backupPath);

            return redirect()->route('backups.index')
                ->with('success', 'Backup deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Delete failed: '.$e->getMessage());
        }
    }

    public function clear($userId = null)
    {
        try {
            $tables = ['transactions', 'payment_allocations', 'payments', 'receipts', 'invoices', 'expenses', 'landings', 'loans'];

            if ($userId) {
                foreach ($tables as $table) {
                    DB::table($table)->where('user_id', $userId)->delete();
                }
                DB::table('boats')->where('user_id', $userId)->delete();
                DB::table('buyers')->where('user_id', $userId)->delete();

                $user = User::find($userId);
                $message = $user ? "All data for user '{$user->username}' has been cleared." : 'Data for the specified user has been cleared.';
            } else {
                foreach ($tables as $table) {
                    DB::table($table)->delete();
                }
                DB::table('boats')->delete();
                DB::table('buyers')->delete();

                $message = 'All business data cleared. User accounts preserved.';
            }

            return redirect()->route('backups.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Clear failed: '.$e->getMessage());
        }
    }

    private function getBackupFiles()
    {
        $backupsDir = database_path('backups');

        if (! File::isDirectory($backupsDir)) {
            return [];
        }

        $files = File::files($backupsDir);

        return array_map(function ($file) {
            return [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
            ];
        }, $files);
    }
}
