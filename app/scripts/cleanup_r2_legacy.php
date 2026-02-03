<?php

declare(strict_types=1);

require __DIR__ . '/../lib/Db.php';
require __DIR__ . '/../lib/R2.php';

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

function argValues(array $argv, string $key): array
{
    $values = [];
    $prefix = $key . '=';
    foreach ($argv as $arg) {
        if (str_starts_with($arg, $prefix)) {
            $raw = substr($arg, strlen($prefix));
            $parts = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $part) {
                $values[] = $part;
            }
        }
    }
    return $values;
}

function hasFlag(array $argv, string $flag): bool
{
    return in_array($flag, $argv, true);
}

function normalizePrefix(string $prefix): string
{
    return trim($prefix, '/');
}

function keyMatchesPrefix(string $key, string $prefix): bool
{
    if ($prefix === '') {
        return true;
    }
    if ($key === $prefix) {
        return true;
    }
    return str_starts_with($key, $prefix . '/');
}

$argv = $_SERVER['argv'] ?? [];
$configPath = argValue($argv, '--config') ?: __DIR__ . '/../config/config.php';
$prefix = normalizePrefix(argValue($argv, '--prefix') ?: 'uploads');
$exclude = argValues($argv, '--exclude');
$delete = hasFlag($argv, '--delete');
$dryRun = hasFlag($argv, '--dry-run') || !$delete;
$verbose = hasFlag($argv, '--verbose');
$limitArg = argValue($argv, '--limit');
$limit = $limitArg !== null ? max(0, (int)$limitArg) : 0;

if (!is_file($configPath)) {
    fwrite(STDERR, "Config not found: {$configPath}\n");
    exit(1);
}

$config = require $configPath;
$driver = $config['storage_driver'] ?? 'local';
$r2 = new R2Client($config['r2'] ?? []);
$pdo = Db::pdo($config);

if ($driver !== 'r2') {
    fwrite(STDERR, "storage_driver is not set to r2.\n");
    exit(1);
}

if (!$r2->isConfigured()) {
    fwrite(STDERR, "R2 config missing. Check R2_* env vars and public base URL.\n");
    exit(1);
}

if ($exclude === []) {
    $exclude = ['uploads/prod', 'uploads/test'];
}

$normalizedExclude = [];
foreach ($exclude as $item) {
    $item = normalizePrefix($item);
    if ($item !== '') {
        $normalizedExclude[] = $item;
    }
}

$listPrefix = $prefix === '' ? '' : $prefix . '/';

$stats = [
    'scanned' => 0,
    'candidate' => 0,
    'deleted' => 0,
    'failed' => 0,
    'skipped' => 0,
];

$startedAt = date('Y-m-d H:i:s');
$status = 'success';
$errorMessage = null;

$createLogTable = static function (\PDO $pdo): void {
    $pdo->exec('CREATE TABLE IF NOT EXISTS r2_cleanup_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        env VARCHAR(20) NOT NULL,
        mode VARCHAR(20) NOT NULL,
        prefix VARCHAR(255) NOT NULL,
        exclude_prefixes TEXT NULL,
        scanned INT NOT NULL DEFAULT 0,
        candidate INT NOT NULL DEFAULT 0,
        deleted INT NOT NULL DEFAULT 0,
        skipped INT NOT NULL DEFAULT 0,
        failed INT NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT "success",
        error_message TEXT NULL,
        started_at DATETIME NOT NULL,
        finished_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (env),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
};

$createLogTable($pdo);

$nextToken = null;
$stop = false;
$processed = 0;

do {
    $result = $r2->listObjects($listPrefix, $nextToken);
    $keys = $result['keys'] ?? [];
    $nextToken = $result['next_token'] ?? null;

    if ($keys === [] && $nextToken === null && $r2->lastError() !== '') {
        $status = 'failed';
        $errorMessage = 'list_failed:' . $r2->lastError();
        fwrite(STDERR, "List failed: " . $r2->lastError() . "\n");
        $stop = true;
        break;
    }

    foreach ($keys as $key) {
        $key = ltrim($key, '/');
        if ($key === '') {
            continue;
        }

        $stats['scanned']++;
        $excluded = false;
        foreach ($normalizedExclude as $ex) {
            if (keyMatchesPrefix($key, $ex)) {
                $excluded = true;
                break;
            }
        }

        if ($excluded) {
            $stats['skipped']++;
            continue;
        }

        $stats['candidate']++;
        if ($limit > 0 && $processed >= $limit) {
            $stop = true;
            break;
        }

        if ($dryRun) {
            if ($verbose) {
                echo "DELETE {$key}\n";
            }
            $processed++;
            continue;
        }

        if ($r2->deleteObject($key)) {
            $stats['deleted']++;
            if ($verbose) {
                echo "DELETED {$key}\n";
            }
        } else {
            $stats['failed']++;
            if ($verbose) {
                echo "FAILED {$key} ({$r2->lastError()})\n";
            }
        }
        $processed++;
    }
} while ($nextToken !== null && !$stop);

$finishedAt = date('Y-m-d H:i:s');
$envLabel = !empty($config['is_test']) ? 'test' : 'prod';
$mode = $dryRun ? 'dry-run' : 'delete';
$insert = $pdo->prepare('INSERT INTO r2_cleanup_logs (env, mode, prefix, exclude_prefixes, scanned, candidate, deleted, skipped, failed, status, error_message, started_at, finished_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$insert->execute([
    $envLabel,
    $mode,
    $prefix,
    json_encode($normalizedExclude, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    $stats['scanned'],
    $stats['candidate'],
    $stats['deleted'],
    $stats['skipped'],
    $stats['failed'],
    $status,
    $errorMessage,
    $startedAt,
    $finishedAt,
]);

$mode = $dryRun ? 'dry-run' : 'delete';
echo "Cleanup complete ({$mode}). scanned={$stats['scanned']} candidate={$stats['candidate']} deleted={$stats['deleted']} skipped={$stats['skipped']} failed={$stats['failed']}\n";
