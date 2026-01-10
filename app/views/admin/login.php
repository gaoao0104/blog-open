<?php
$title = '后台登录';
require __DIR__ . '/../partials/header.php';
?>
<div class="auth-card">
    <h1>后台登录</h1>
    <form method="post" action="/admin/login">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <label>
            用户名
            <input type="text" name="username" required>
        </label>
        <label>
            密码
            <input type="password" name="password" required>
        </label>
        <button type="submit">登录</button>
    </form>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
