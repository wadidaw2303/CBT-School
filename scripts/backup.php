<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Core\App;
use Core\Config;
use Core\Database;

$app = new App();
Config::getInstance($app->basePath('.env'));

$db = Database::getInstance();

$timestamp = date('Ymd_His');
$backupDir = $app->basePath('storage/backups');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupFile = $backupDir . "/backup_{$timestamp}.sql";
$compressedFile = $backupDir . "/backup_{$timestamp}.tar.gz";

echo "Starting backup...\n";
echo "Database: " . config('database.database') . "\n";
echo "Output: {$backupFile}\n";

$tables = $db->select("SHOW TABLES");
$tableNames = array_column($tables, array_key_first($tables[0]));

$output = "-- CBT School Database Backup\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Database: " . config('database.database') . "\n\n";
$output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tableNames as $table) {
    echo "Backing up table: {$table} ... ";

    // Get table structure
    $createTable = $db->selectOne("SHOW CREATE TABLE `{$table}`");
    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $output .= $createTable['Create Table'] . ";\n\n";

    // Get data
    $rows = $db->select("SELECT * FROM `{$table}`");
    if (!empty($rows)) {
        $columns = array_keys($rows[0]);
        $output .= "INSERT INTO `{$table}` (" . implode('`, `', $columns) . ") VALUES\n";

        $values = [];
        foreach ($rows as $row) {
            $escaped = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $escaped[] = 'NULL';
                } elseif (is_bool($val)) {
                    $escaped[] = $val ? '1' : '0';
                } elseif (is_numeric($val)) {
                    $escaped[] = $val;
                } else {
                    $escaped[] = "'" . $db->getPdo()->quote($val) . "'";
                }
            }
            $values[] = "(" . implode(', ', $escaped) . ")";
        }
        $output .= implode(",\n", $values) . ";\n\n";
    }
    echo "Done\n";
}

$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents($backupFile, $output);
echo "SQL dump created: {$backupFile}\n";

// Compress
echo "Compressing... ";
$cmd = "tar -czf " . escapeshellarg($compressedFile) . " -C " . escapeshellarg($backupDir) . " " . basename($backupFile);
exec($cmd, $out, $returnCode);
if ($returnCode === 0) {
    unlink($backupFile);
    $size = filesize($compressedFile);
    echo "Done (" . format_bytes($size) . ")\n";
} else {
    echo "Failed, keeping SQL file\n";
}

// Log backup
$db->table('backup_logs')->insert([
    'type' => 'database',
    'filename' => basename($compressedFile),
    'size' => filesize($compressedFile),
    'status' => 'completed',
    'started_at' => date('Y-m-d H:i:s', strtotime('-1 minute')),
    'completed_at' => date('Y-m-d H:i:s'),
]);

echo "Backup completed: {$compressedFile}\n";

function format_bytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}