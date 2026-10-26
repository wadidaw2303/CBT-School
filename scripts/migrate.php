<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Core\App;
use Core\Config;
use Core\Database;

// Bootstrap
$app = new App();
Config::getInstance($app->basePath('.env'));

// Handle CLI arguments
$args = $argv;
array_shift($args); // Remove script name

$command = $args[0] ?? 'migrate';
$steps = 1;

if (in_array('--step', $args)) {
    $idx = array_search('--step', $args);
    $steps = (int) ($args[$idx + 1] ?? 1);
}

$db = Database::getInstance();

switch ($command) {
    case 'migrate':
        echo "Running migrations...\n";
        $count = $db->migrate();
        echo "{$count} migration(s) executed.\n";
        break;

    case 'rollback':
        echo "Rolling back {$steps} migration(s)...\n";
        $count = $db->rollback($steps);
        echo "{$count} migration(s) rolled back.\n";
        break;

    case 'fresh':
        echo "Dropping all tables and re-running migrations...\n";
        // Get all tables
        $tables = $db->select("SHOW TABLES");
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $db->statement("DROP TABLE IF EXISTS `{$tableName}`");
        }
        echo "All tables dropped.\n";
        $count = $db->migrate();
        echo "{$count} migration(s) executed.\n";
        break;

    case 'status':
        $ran = $db->raw("SELECT migration, batch FROM migrations ORDER BY batch, id");
        $path = $app->basePath(config('database.migrations.path', 'database/migrations'));
        $files = glob($path . '/*.sql');
        sort($files);

        echo "Migration Status:\n";
        echo str_repeat('-', 80) . "\n";
        printf("%-50s %-10s %s\n", 'Migration', 'Batch', 'Status');
        echo str_repeat('-', 80) . "\n";

        foreach ($files as $file) {
            $filename = basename($file);
            $isRan = in_array($filename, array_column($ran, 'migration'));
            $batch = '';
            foreach ($ran as $r) {
                if ($r['migration'] === $filename) {
                    $batch = $r['batch'];
                    break;
                }
            }
            printf("%-50s %-10s %s\n", $filename, $batch, $isRan ? 'Ran' : 'Pending');
        }
        break;

    case 'create':
        $name = $args[1] ?? '';
        if (!$name) {
            echo "Usage: php scripts/migrate.php create <name>\n";
            exit(1);
        }
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.sql";
        $filepath = $app->basePath(config('database.migrations.path', 'database/migrations')) . '/' . $filename;
        $content = "-- Migration: {$filename}\n\n";
        file_put_contents($filepath, $content);
        echo "Created: {$filepath}\n";
        break;

    default:
        echo "Usage: php scripts/migrate.php [migrate|rollback|fresh|status|create]\n";
        break;
}