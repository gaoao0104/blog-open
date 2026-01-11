<?php

declare(strict_types=1);

final class Auth
{
    public static function check(): bool
    {
        if (!empty($_SESSION['user_id'])) {
            return true;
        }

        if (!empty($_COOKIE['remember_token'])) {
            global $pdo; // Use global PDO instance from index.php
            if ($pdo) {
                $stmt = $pdo->prepare('SELECT id, username FROM users WHERE remember_token = ?');
                $stmt->execute([$_COOKIE['remember_token']]);
                $user = $stmt->fetch();
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    return true;
                }
            }
        }

        return false;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            // flash('error', '请先登录。');
            redirect_to('/admin/login');
        }
    }
}
