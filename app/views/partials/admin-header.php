<?php
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$settings = $settings ?? [];
$site_name = ($settings['site_name'] ?? '') ?: 'Gaoao Blog';
$page_title = $title ?? 'Admin';
$title_tag = $page_title . ' - ' . $site_name . ' Admin';
?>
<!doctype html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= e($title_tag) ?></title>
    <link rel="stylesheet" href="/assets/admin.css?v=<?php echo time(); ?>">
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="admin-body">
<div class="admin-layout">
    <?php require __DIR__ . '/admin-sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-title"><?= e($title ?? 'Dashboard') ?></div>
            <div class="header-actions">
               <a href="/" target="_blank" class="btn btn-secondary btn-sm" title="View Site">
                   <span class="icon">👁️</span> 访问前台
               </a>
               <button id="theme-toggle" class="btn btn-secondary btn-sm" aria-label="Toggle Dark Mode">
                   <span class="sun-icon">☀️</span>
                   <span class="moon-icon" style="display:none">🌙</span>
               </button>
               <a href="/admin/logout" class="btn btn-secondary btn-sm">退出</a>
            </div>
        </header>
        
        <div class="admin-content">
            <?php if ($message = flash('success')): ?>
                <div class="badge badge-success mb-6" style="padding: 8px 16px; font-size: 0.9rem; display: block; border: 1px solid var(--admin-success); background: rgba(16, 185, 129, 0.1); color: var(--admin-success);">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($message = flash('error')): ?>
                <div class="badge badge-danger mb-6" style="padding: 8px 16px; font-size: 0.9rem; display: block; border: 1px solid var(--admin-danger); background: rgba(239, 68, 68, 0.1); color: var(--admin-danger);">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>
