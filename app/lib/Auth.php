<?php

declare(strict_types=1);

final class Auth
{
<<<<<<< HEAD
    public static function check(): bool
    {
        if (!empty($_SESSION['user_id'])) {
=======
    private static ?array $permissionCache = null;
    private static ?bool $superGroupCache = null;
    private static ?int $cacheUserId = null;

    public static function check(): bool
    {
        if (!empty($_SESSION['user_id'])) {
            if (empty($_SESSION['role']) || !array_key_exists('group_id', $_SESSION)) {
                global $pdo;
                if ($pdo) {
                    $stmt = $pdo->prepare('SELECT role, group_id FROM users WHERE id = ?');
                    $stmt->execute([$_SESSION['user_id']]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $_SESSION['role'] = $row['role'] ?? 'admin';
                    $_SESSION['group_id'] = $row['group_id'] ?? null;
                }
            }
>>>>>>> a3d11b8 (sync: update open-source release)
            return true;
        }

        if (!empty($_COOKIE['remember_token'])) {
            global $pdo; // Use global PDO instance from index.php
            if ($pdo) {
<<<<<<< HEAD
                $stmt = $pdo->prepare('SELECT id, username FROM users WHERE remember_token = ?');
=======
                $stmt = $pdo->prepare('SELECT id, username, role, group_id FROM users WHERE remember_token = ?');
>>>>>>> a3d11b8 (sync: update open-source release)
                $stmt->execute([$_COOKIE['remember_token']]);
                $user = $stmt->fetch();
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
<<<<<<< HEAD
=======
                    $_SESSION['role'] = $user['role'] ?? 'admin';
                    $_SESSION['group_id'] = $user['group_id'] ?? null;
>>>>>>> a3d11b8 (sync: update open-source release)
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
<<<<<<< HEAD
=======

    public static function userId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public static function groupId(): ?int
    {
        $groupId = $_SESSION['group_id'] ?? null;
        return $groupId === null ? null : (int)$groupId;
    }

    public static function userRole(): string
    {
        return (string)($_SESSION['role'] ?? 'editor');
    }

    public static function isAdmin(): bool
    {
        if (self::groupId() !== null) {
            return self::isSuperGroup();
        }
        return self::userRole() === 'admin';
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            flash('error', '当前账号无权限访问该页面。');
            redirect_to('/admin');
        }
    }

    public static function hasPermission(string $key): bool
    {
        if ($key === '*') {
            return self::isAdmin();
        }

        if (self::groupId() === null && self::userRole() === 'admin') {
            return true;
        }

        $permissions = self::permissions();
        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($key, $permissions, true);
    }

    public static function requirePermission(string $key, string $redirect = '/admin'): void
    {
        self::requireLogin();
        if (!self::hasPermission($key)) {
            flash('error', '当前账号无权限访问该页面。');
            redirect_to($redirect);
        }
    }

    private static function permissions(): array
    {
        $userId = self::userId();
        if (self::$cacheUserId !== $userId) {
            self::$cacheUserId = $userId;
            self::$permissionCache = null;
            self::$superGroupCache = null;
        }

        if (self::$permissionCache !== null) {
            return self::$permissionCache;
        }

        $groupId = self::groupId();
        if ($groupId === null) {
            self::$permissionCache = [];
            return self::$permissionCache;
        }

        global $pdo;
        if (!$pdo) {
            self::$permissionCache = [];
            return self::$permissionCache;
        }

        $stmt = $pdo->prepare('SELECT is_super FROM admin_groups WHERE id = ?');
        $stmt->execute([$groupId]);
        $isSuper = (int)$stmt->fetchColumn() === 1;
        self::$superGroupCache = $isSuper;

        if ($isSuper) {
            self::$permissionCache = ['*'];
            return self::$permissionCache;
        }

        $stmt = $pdo->prepare('SELECT perm_key FROM admin_group_permissions WHERE group_id = ?');
        $stmt->execute([$groupId]);
        $perms = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        self::$permissionCache = $perms ?: [];
        return self::$permissionCache;
    }

    private static function isSuperGroup(): bool
    {
        $groupId = self::groupId();
        if ($groupId === null) {
            return false;
        }

        $userId = self::userId();
        if (self::$cacheUserId !== $userId) {
            self::$cacheUserId = $userId;
            self::$permissionCache = null;
            self::$superGroupCache = null;
        }

        if (self::$superGroupCache !== null) {
            return self::$superGroupCache;
        }

        global $pdo;
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT is_super FROM admin_groups WHERE id = ?');
        $stmt->execute([$groupId]);
        $isSuper = (int)$stmt->fetchColumn() === 1;
        self::$superGroupCache = $isSuper;
        return $isSuper;
    }
>>>>>>> a3d11b8 (sync: update open-source release)
}
