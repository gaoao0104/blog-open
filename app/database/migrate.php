<?php

declare(strict_types=1);

require __DIR__ . '/../lib/Db.php';

function argValue(array $argv, string $key): ?string
{
    $prefix = $key . '=';
    foreach ($argv as $arg) {
        if (str_starts_with($arg, $prefix)) {
            return substr($arg, strlen($prefix));
        }
    }
    return null;
}

function hasFlag(array $argv, string $flag): bool
{
    return in_array($flag, $argv, true);
}

$argv = $_SERVER['argv'] ?? [];
$configPath = argValue($argv, '--config') ?: __DIR__ . '/../config/config.php';
$migrationsDir = argValue($argv, '--migrations') ?: __DIR__ . '/migrations';
$dryRun = hasFlag($argv, '--dry-run');

if (!is_file($configPath)) {
    fwrite(STDERR, "Config not found: {$configPath}\n");
    exit(1);
}

if (!is_dir($migrationsDir)) {
    fwrite(STDERR, "Migrations dir not found: {$migrationsDir}\n");
    exit(1);
}

$config = require $configPath;
$pdo = Db::pdo($config);

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255) NOT NULL UNIQUE, applied_at DATETIME NOT NULL)');

$applied = $pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN, 0);
$applied = $applied ?: [];

$files = glob(rtrim($migrationsDir, '/') . '/*.sql') ?: [];
$files = array_map('strval', $files);
$files = array_values(array_filter($files, 'is_file'));
sort($files, SORT_STRING);

$pending = [];
foreach ($files as $file) {
    $name = basename($file);
    if (!in_array($name, $applied, true)) {
        $pending[] = $file;
    }
}

if ($dryRun) {
    if (empty($pending)) {
        echo "No pending migrations.\n";
        exit(0);
    }
    echo "Pending migrations:\n";
    foreach ($pending as $file) {
        echo "- " . basename($file) . "\n";
    }
    exit(2);
}

if (empty($pending)) {
    echo "No pending migrations.\n";
    exit(0);
}

$insert = $pdo->prepare('INSERT INTO schema_migrations (filename, applied_at) VALUES (?, NOW())');

foreach ($pending as $file) {
    $sql = trim((string)file_get_contents($file));
    if ($sql === '') {
        echo "Skip empty migration: " . basename($file) . "\n";
        $insert->execute([basename($file)]);
        continue;
    }

    $pdo->exec($sql);
    $insert->execute([basename($file)]);
    echo "Applied: " . basename($file) . "\n";
}

exit(0);
