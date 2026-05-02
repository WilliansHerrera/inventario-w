<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupService
{
    protected string $backupPath;

    protected string $mysqlPath = 'C:\\xampp\\mysql\\bin\\';

    protected string $dbHost;

    protected string $dbPort;

    protected string $dbName;

    protected string $dbUser;

    protected string $dbPass;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');

        if (! File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }

        $this->dbHost = env('DB_HOST', '127.0.0.1');
        $this->dbPort = env('DB_PORT', '3306');
        $this->dbName = env('DB_DATABASE', 'inventario_w');
        $this->dbUser = env('DB_USERNAME', 'root');
        $this->dbPass = env('DB_PASSWORD', '');
    }

    /**
     * Create a new database backup.
     */
    public function createBackup(): array
    {
        $filename = 'backup_'.date('Y-m-d_H-i-s').'.sql';
        $filePath = $this->backupPath.DIRECTORY_SEPARATOR.$filename;

        // Construir comando mysqldump
        // mysqldump -h host -P port -u user -pPass dbname > file.sql
        $passwordPart = $this->dbPass ? "-p\"{$this->dbPass}\"" : '';
        $command = "\"{$this->mysqlPath}mysqldump.exe\" --no-tablespaces -h {$this->dbHost} -P {$this->dbPort} -u {$this->dbUser} {$passwordPart} {$this->dbName} > \"{$filePath}\" 2>&1";

        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            Log::error('Backup failed: '.implode("\n", $output));

            return ['success' => false, 'message' => 'Error al crear el backup: '.implode(' ', $output)];
        }

        return ['success' => true, 'filename' => $filename];
    }

    /**
     * Restore a database backup from file.
     */
    public function restoreBackup(string $filename): array
    {
        $filePath = $this->backupPath.DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($filePath)) {
            return ['success' => false, 'message' => 'El archivo de backup no existe.'];
        }

        // Construir comando mysql
        // mysql -h host -P port -u user -pPass dbname < file.sql
        $passwordPart = $this->dbPass ? "-p\"{$this->dbPass}\"" : '';
        $command = "\"{$this->mysqlPath}mysql.exe\" -h {$this->dbHost} -P {$this->dbPort} -u {$this->dbUser} {$passwordPart} {$this->dbName} < \"{$filePath}\" 2>&1";

        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            Log::error('Restore failed: '.implode("\n", $output));

            return ['success' => false, 'message' => 'Error al restaurar el backup: '.implode(' ', $output)];
        }

        return ['success' => true, 'message' => 'Base de datos restaurada correctamente.'];
    }

    /**
     * List all available backups.
     */
    public function listBackups(): array
    {
        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size' => round($file->getSize() / 1024 / 1024, 2).' MB',
                    'date' => date('d/m/Y H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime(),
                ];
            }
        }

        // Ordenar por fecha descendente
        usort($backups, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $filePath = $this->backupPath.DIRECTORY_SEPARATOR.$filename;

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Get absolute path for download.
     */
    public function getBackupPath(string $filename): ?string
    {
        $filePath = $this->backupPath.DIRECTORY_SEPARATOR.$filename;

        return File::exists($filePath) ? $filePath : null;
    }
}
