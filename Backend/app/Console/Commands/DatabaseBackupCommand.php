<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\AuditLog;
use Carbon\Carbon;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:backup {--retention=30 : Number of days to retain backups}';

    /**
     * The console command description.
     */
    protected $description = 'Perform automated MySQL database backup, compress and enforce 30-day retention policy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automated database backup for kenan_cake_db...');

        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "kenan_cake_db_backup_{$timestamp}.sql";
        $filePath = "{$backupDir}/{$filename}";

        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database', 'kenan_cake_db');
        $dbUser = config('database.connections.mysql.username', 'root');
        $dbPass = config('database.connections.mysql.password', '');

        // Locate mysqldump binary
        $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (!file_exists($mysqldump)) {
            $mysqldump = 'mysqldump';
        }

        $passArg = !empty($dbPass) ? "--password=\"{$dbPass}\"" : "";
        $cmd = "\"{$mysqldump}\" --host={$dbHost} --port={$dbPort} --user={$dbUser} {$passArg} {$dbName} > \"{$filePath}\"";

        $resultCode = null;
        $output = [];
        exec($cmd, $output, $resultCode);

        // Fallback: If mysqldump CLI failed, perform lightweight PDO schema & table dump
        if ($resultCode !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
            $this->warn('mysqldump CLI exited with code ' . $resultCode . ', falling back to PDO streaming export...');
            $this->pdoBackup($filePath);
        }

        $sizeKb = round(filesize($filePath) / 1024, 2);
        $this->info("Backup successfully written: {$filename} ({$sizeKb} KB)");

        // Enforce 30-day retention policy
        $retentionDays = (int) $this->option('retention');
        $cutoff = Carbon::now()->subDays($retentionDays);
        $deletedCount = 0;

        $files = File::files($backupDir);
        foreach ($files as $file) {
            $fileTime = Carbon::createFromTimestamp(File::lastModified($file));
            if ($fileTime->lt($cutoff)) {
                File::delete($file);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("Purged {$deletedCount} old backup file(s) exceeding {$retentionDays}-day retention policy.");
        }

        // Record Audit Log
        AuditLog::create([
            'user_id' => null,
            'user_name' => 'System / Cron',
            'action' => 'Database backup executed',
            'record' => $filename,
            'entity_type' => 'Database',
            'entity_id' => (string) Carbon::now()->timestamp,
            'old_value' => null,
            'new_value' => "Size: {$sizeKb} KB, Retention: {$retentionDays} days, Purged: {$deletedCount}",
            'created_at' => now(),
        ]);

        return Command::SUCCESS;
    }

    /**
     * PDO based streaming dump fallback
     */
    protected function pdoBackup(string $filePath)
    {
        $pdo = new \PDO(
            'mysql:host=127.0.0.1;dbname=kenan_cake_db',
            'root',
            '',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );

        $tablesStmt = $pdo->query('SHOW TABLES');
        $tables = $tablesStmt->fetchAll(\PDO::FETCH_COLUMN);

        $handle = fopen($filePath, 'w');
        fwrite($handle, "-- Automated Database Backup for kenan_cake_db\n");
        fwrite($handle, "-- Created: " . Carbon::now()->toIso8601String() . "\n\n");

        foreach ($tables as $table) {
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createStmt['Create Table'] ?? '';
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n");

            $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
            while ($row = $rowsStmt->fetch(\PDO::FETCH_ASSOC)) {
                $cols = array_keys($row);
                $escapedVals = array_map(function ($val) use ($pdo) {
                    return $val === null ? 'NULL' : $pdo->quote($val);
                }, array_values($row));
                $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $escapedVals) . ");\n";
                fwrite($handle, $sql);
            }
            fwrite($handle, "\n");
        }
        fclose($handle);
    }
}
