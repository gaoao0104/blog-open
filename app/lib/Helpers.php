<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(array $config): string
{
    if (!empty($config['base_url'])) {
        return rtrim($config['base_url'], '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host;
}

function url_for(array $config, string $path): string
{
    return base_url($config) . $path;
}

function redirect_to(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function slugify(string $value): string
{
    $value = trim($value);
    if (function_exists('mb_strtolower')) {
        $value = mb_strtolower($value, 'UTF-8');
    } else {
        $value = strtolower($value);
    }
    $value = preg_replace('/[^\p{L}\p{N}\s-]+/u', '', $value);
    $value = preg_replace('/[\s-]+/u', '-', $value);
    return trim($value, '-');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (isset($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}

function snippet(string $text, int $length = 120): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $length);
    }

    return substr($text, 0, $length);
}

function markdown_plaintext(string $markdown): string
{
    $text = preg_replace('/```[\\s\\S]*?```/', ' ', $markdown);
    $text = preg_replace('/`[^`]+`/', ' ', $text);
    $text = preg_replace('/!\\[[^\\]]*\\]\\([^\\)]+\\)/', ' ', $text);
    $text = preg_replace('/\\[[^\\]]*\\]\\([^\\)]+\\)/', ' ', $text);
    $text = preg_replace('/[#>*_\\-]/', ' ', $text);
    $text = preg_replace('/\\s+/', ' ', $text);
    return trim($text);
}

function highlight(string $text, string $query): string
{
    $safe = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $query = trim($query);
    if ($query === '') {
        return $safe;
    }

    $pattern = '/' . preg_quote($query, '/') . '/iu';
    return preg_replace($pattern, '<mark>$0</mark>', $safe);
}

function track_page_view(int $post_id): void
{
    if (php_sapi_name() === 'cli') {
        return;
    }

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $uaLower = strtolower($ua);
    if ($uaLower !== '' && (str_contains($uaLower, 'bot') || str_contains($uaLower, 'spider') || str_contains($uaLower, 'slurp'))) {
        return;
    }

    $ip = '';
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($forwarded[0] ?? '');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    if ($ip === '') {
        $ip = '0.0.0.0';
    }

    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    if (is_string($referrer)) {
        $referrer = trim($referrer);
    } else {
        $referrer = '';
    }
    if ($referrer !== '' && strlen($referrer) > 2048) {
        $referrer = substr($referrer, 0, 2048);
    }
    $referrerHost = '';
    if ($referrer !== '') {
        $host = parse_url($referrer, PHP_URL_HOST);
        if (is_string($host)) {
            $referrerHost = strtolower($host);
        }
    }
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    if ($referrerHost !== '' && $currentHost !== '' && strtolower($currentHost) === $referrerHost) {
        $referrer = '';
        $referrerHost = '';
    }

    $pdo = $GLOBALS['pdo'] ?? null;
    if (!$pdo instanceof \PDO) {
        return;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO site_analytics (post_id, ip_address, user_agent, referrer, referrer_host, visit_date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $post_id,
            $ip,
            $ua,
            $referrer,
            $referrerHost,
            date('Y-m-d'),
        ]);
    } catch (\Throwable $e) {
        // Avoid breaking page render on analytics failure.
    }
}
