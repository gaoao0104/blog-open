<?php
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $settings = $settings ?? [];
    $site_name = ($settings['site_name'] ?? '') ?: 'Gaoao Blog';
    $page_title = $title ?? $site_name;
    $title_tag = $page_title === $site_name ? $site_name : ($page_title . ' - ' . $site_name);
    $meta_robots = $meta_robots ?? null;
    if ($meta_robots === null) {
        if (str_starts_with($current, '/admin')) {
            $meta_robots = 'noindex,nofollow';
        } elseif (str_starts_with($current, '/search')) {
            $meta_robots = 'noindex,follow';
        }
    }
    $author_name = $author_name ?? null;
    $article_meta = $article_meta ?? [];

    if (!str_starts_with($current, '/admin') && isset($track_post_id)) {
        track_page_view((int)$track_post_id);
    }
?>
<!doctype html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title_tag) ?></title>
    <link rel="alternate" type="application/rss+xml" title="<?= e($site_name) ?> RSS Feed" href="/rss.xml">
    <?php if (!empty($meta_robots)): ?>
        <meta name="robots" content="<?= e($meta_robots) ?>">
    <?php endif; ?>
    <?php if (!empty($meta_description)): ?>
        <meta name="description" content="<?= e($meta_description) ?>">
    <?php endif; ?>
    <?php if (!empty($author_name)): ?>
        <meta name="author" content="<?= e($author_name) ?>">
    <?php endif; ?>
    <?php if (!empty($canonical_url)): ?>
        <link rel="canonical" href="<?= e($canonical_url) ?>">
        <link rel="alternate" hreflang="zh-CN" href="<?= e($canonical_url) ?>">
        <meta property="og:url" content="<?= e($canonical_url) ?>">
    <?php endif; ?>
    <link rel="alternate" type="application/rss+xml" title="<?= e($site_name) ?> RSS" href="<?= e(url_for($config, '/rss.xml')) ?>">
    <?php $og_type = $og_type ?? 'website'; ?>
    <?php $og_image = $og_image ?? ''; ?>
    <meta property="og:title" content="<?= e($page_title) ?>">
    <?php if (!empty($meta_description)): ?>
        <meta property="og:description" content="<?= e($meta_description) ?>">
    <?php endif; ?>
    <meta property="og:type" content="<?= e($og_type) ?>">
    <meta property="og:site_name" content="<?= e($site_name) ?>">
    <meta property="og:locale" content="zh_CN">
    <?php if (!empty($og_image)): ?>
        <meta property="og:image" content="<?= e($og_image) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?= !empty($og_image) ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= e($page_title) ?>">
    <?php if (!empty($meta_description)): ?>
        <meta name="twitter:description" content="<?= e($meta_description) ?>">
    <?php endif; ?>
    <?php if (!empty($og_image)): ?>
        <meta name="twitter:image" content="<?= e($og_image) ?>">
    <?php endif; ?>
    <?php if (!empty($article_meta['published_time'])): ?>
        <meta property="article:published_time" content="<?= e($article_meta['published_time']) ?>">
    <?php endif; ?>
    <?php if (!empty($article_meta['modified_time'])): ?>
        <meta property="article:modified_time" content="<?= e($article_meta['modified_time']) ?>">
    <?php endif; ?>
    <?php if (!empty($article_meta['author'])): ?>
        <meta property="article:author" content="<?= e($article_meta['author']) ?>">
    <?php endif; ?>
    <?php if (!empty($article_meta['section'])): ?>
        <meta property="article:section" content="<?= e($article_meta['section']) ?>">
    <?php endif; ?>
    <?php if (!empty($article_meta['tags']) && is_array($article_meta['tags'])): ?>
        <?php foreach ($article_meta['tags'] as $tag): ?>
            <meta property="article:tag" content="<?= e($tag) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($json_ld)): ?>
        <script type="application/ld+json"><?= json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/style.css?v=<?php echo time(); ?>">
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {
                console.log('Error accessing localStorage:', e);
            }
        })();

        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('theme-toggle');
            if (!toggleBtn) return;

            const sunIcon = toggleBtn.querySelector('.sun-icon');
            const moonIcon = toggleBtn.querySelector('.moon-icon');
            
            function updateIcons(isDark) {
                if (isDark) {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            }

            // Initial Icon State
            updateIcons(document.documentElement.getAttribute('data-theme') === 'dark');

            toggleBtn.addEventListener('click', () => {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                try {
                    if (isDark) {
                        document.documentElement.removeAttribute('data-theme');
                        localStorage.setItem('theme', 'light');
                        updateIcons(false);
                    } else {
                        document.documentElement.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        updateIcons(true);
                    }
                } catch (e) {
                    console.log('Error toggling theme:', e);
                }
            });
        });
    </script>
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="logo" href="/"><?= e(($settings['header_title'] ?? '') ?: 'Gaoao Blog') ?></a>
        <nav>
            <?php if (($settings['show_nav_home'] ?? '1') === '1'): ?>
                <a href="/" class="<?= $current === '/' ? 'active' : '' ?>">首页</a>
            <?php endif; ?>
            <?php if (($settings['show_nav_search'] ?? '1') === '1'): ?>
                <a href="/search" class="<?= str_starts_with($current, '/search') ? 'active' : '' ?>">搜索</a>
            <?php endif; ?>
            <?php if (($settings['show_nav_admin'] ?? '1') === '1'): ?>
                <a href="/admin" class="<?= str_starts_with($current, '/admin') ? 'active' : '' ?>">后台</a>
            <?php endif; ?>
        </nav>
        <?php if (($settings['show_search_form'] ?? '1') === '1'): ?>
            <form class="search-form" action="/search" method="get">
                <input type="search" name="q" placeholder="搜索文章" value="<?= e($_GET['q'] ?? '') ?>">
                <button type="submit">搜索</button>
            </form>
        <?php endif; ?>
        <button id="theme-toggle" type="button" class="button ghost" aria-label="Toggle Dark Mode" style="margin-left: 12px; padding: 8px;">
            <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
    </div>
</header>
<main class="container">
<?php if ($message = flash('success')): ?>
    <div class="alert success"><?= e($message) ?></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="alert error"><?= e($message) ?></div>
<?php endif; ?>
