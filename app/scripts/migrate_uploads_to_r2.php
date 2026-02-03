<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
require __DIR__ . '/../lib/Db.php';
require __DIR__ . '/../lib/R2.php';

$driver = $config['storage_driver'] ?? 'local';
$r2 = new R2Client($config['r2'] ?? []);

if ($driver !== 'r2') {
    fwrite(STDERR, "storage_driver is not set to r2.\n");
    exit(1);
}

if (!$r2->isConfigured()) {
    fwrite(STDERR, "R2 config missing. Check R2_* env vars and public base URL.\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv, true);
$skipFiles = in_array('--skip-files', $argv, true);
$skipDb = in_array('--skip-db', $argv, true);

$uploadsDir = rtrim((string)($config['upload_dir'] ?? ''), '/');
if ($uploadsDir === '' || !is_dir($uploadsDir)) {
    fwrite(STDERR, "Uploads dir not found: {$uploadsDir}\n");
    exit(1);
}

if (!$skipFiles) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadsDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $filename = $file->getFilename();
        if ($filename === '' || $filename[0] === '.') {
            continue;
        }
        $fullPath = $file->getPathname();
        $relative = ltrim(substr($fullPath, strlen($uploadsDir)), '/');
        if ($relative === '') {
            continue;
        }
        $key = $r2->buildObjectKey($relative);
        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        if ($dryRun) {
            echo "DRY RUN upload: {$relative} -> {$key}\n";
            continue;
        }

        $body = file_get_contents($fullPath);
        if ($body === false) {
            fwrite(STDERR, "Read failed: {$fullPath}\n");
            continue;
        }

        if (!$r2->putObject($key, $body, $mime)) {
            fwrite(STDERR, "Upload failed: {$relative} ({$r2->lastError()})\n");
        }
    }
}

if ($skipDb) {
    exit(0);
}

$pdo = Db::pdo($config);
$baseUrl = rtrim((string)($config['r2']['public_base_url'] ?? ''), '/');

if ($baseUrl === '') {
    fwrite(STDERR, "public_base_url is empty.\n");
    exit(1);
}

function table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

function rewrite_upload_url(?string $value, string $baseUrl): ?string
{
    if ($value === null || $value === '') {
        return $value;
    }

    $baseUrl = rtrim($baseUrl, '/');
    $value = preg_replace('#https?://[^/]+/uploads/#i', $baseUrl . '/uploads/', $value);

    $escaped = preg_quote($baseUrl, '#');
    $value = preg_replace('#(?<!' . $escaped . ')/uploads/#', $baseUrl . '/uploads/', $value);

    if (str_starts_with($value, 'uploads/')) {
        $value = $baseUrl . '/' . $value;
    }

    return $value;
}

function update_table(PDO $pdo, string $table, string $primaryKey, array $columns, string $baseUrl, bool $dryRun): void
{
    if (!table_exists($pdo, $table)) {
        return;
    }

    $selectCols = array_map(fn($col) => "`{$col}`", $columns);
    $sql = 'SELECT `' . $primaryKey . '`, ' . implode(', ', $selectCols) . ' FROM `' . $table . '`';

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        return;
    }

    $updateCols = array_map(fn($col) => "`{$col}` = ?", $columns);
    $updateSql = 'UPDATE `' . $table . '` SET ' . implode(', ', $updateCols) . ' WHERE `' . $primaryKey . '` = ?';
    $updateStmt = $pdo->prepare($updateSql);

    foreach ($rows as $row) {
        $values = [];
        $changed = false;
        foreach ($columns as $col) {
            $original = $row[$col] ?? null;
            $updated = rewrite_upload_url(is_string($original) ? $original : null, $baseUrl);
            $values[] = $updated;
            if ($updated !== $original) {
                $changed = true;
            }
        }

        if (!$changed) {
            continue;
        }

        if ($dryRun) {
            echo "DRY RUN update: {$table}.{$primaryKey}={$row[$primaryKey]}\n";
            continue;
        }

        $values[] = $row[$primaryKey];
        $updateStmt->execute($values);
    }
}

update_table($pdo, 'uploads', 'id', ['file_path'], $baseUrl, $dryRun);
update_table($pdo, 'posts', 'id', ['featured_image', 'featured_card_image', 'content_md'], $baseUrl, $dryRun);
update_table($pdo, 'post_cards', 'id', ['image_url'], $baseUrl, $dryRun);
update_table($pdo, 'featured_cards', 'id', ['image_url'], $baseUrl, $dryRun);
update_table($pdo, 'users', 'id', ['avatar_url', 'verified_badge_url'], $baseUrl, $dryRun);
update_table($pdo, 'settings', 'setting_key', ['setting_value'], $baseUrl, $dryRun);

echo $dryRun ? "DRY RUN complete.\n" : "Migration complete.\n";
