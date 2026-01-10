<?php

declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify(?string $token): bool
    {
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
