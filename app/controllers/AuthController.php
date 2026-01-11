<?php

declare(strict_types=1);

final class AuthController
{
    public static function loginForm(array $config): void
    {
        View::render('admin/login', [
            'config' => $config,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function login(\PDO $pdo): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            flash('error', '请输入账号和密码。');
            redirect_to('/admin/login');
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', '账号或密码不正确。');
            redirect_to('/admin/login');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Remember Me Logic
        if (!empty($_POST['remember'])) {
            $token = bin2hex(random_bytes(32));
            // Store token in DB (overwriting any previous token)
            $pdo->prepare('UPDATE users SET remember_token = ? WHERE id = ?')
                ->execute([$token, $user['id']]);
            
            // Set cookie for 30 days
            setcookie('remember_token', $token, time() + 3600 * 24 * 30, '/', '', false, true);
        }

        redirect_to('/admin');
    }

    public static function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            global $pdo;
            if ($pdo) {
                // Clear token from DB
                $pdo->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')->execute([$_SESSION['user_id']]);
            }
        }
        
        session_destroy();
        // Clear cookie
        setcookie('remember_token', '', time() - 3600, '/');
        redirect_to('/');
    }
}
