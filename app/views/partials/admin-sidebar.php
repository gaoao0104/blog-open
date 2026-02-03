<?php
$current_uri = $_SERVER['REQUEST_URI'];
<<<<<<< HEAD
=======
$settings = $settings ?? [];
$can_stats = Auth::hasPermission('stats.view');
$can_featured = Auth::hasPermission('featured.manage');
$can_categories = Auth::hasPermission('categories.manage');
$can_tags = Auth::hasPermission('tags.manage');
$can_uploads = Auth::hasPermission('uploads.manage');
$can_cards = Auth::hasPermission('cards.manage');
$can_users = Auth::hasPermission('users.manage');
$can_groups = Auth::hasPermission('groups.manage');
$can_settings = Auth::hasPermission('settings.manage');
$can_maintenance = Auth::hasPermission('maintenance.manage');

$get_setting = static function (string $key, string $fallback) use ($settings): string {
    $value = trim((string)($settings[$key] ?? ''));
    return $value !== '' ? $value : $fallback;
};

$render_icon = static function (string $value): string {
    $value = trim($value);
    $isUrl = preg_match('~^(https?://|/)~', $value) === 1;
    if ($isUrl) {
        return '<img class="menu-icon-image" src="' . e($value) . '" alt="">';
    }
    return '<span class="icon-text">' . e($value) . '</span>';
};

$menu_title = $get_setting('admin_menu_title', '后台管理');
$menu_title_icon = $get_setting('admin_menu_title_icon', '⚙️');
$section_resources = $get_setting('admin_menu_section_resources_label', '资源管理');
$section_system = $get_setting('admin_menu_section_system_label', '系统');
>>>>>>> a3d11b8 (sync: update open-source release)
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="/admin" class="logo">
<<<<<<< HEAD
            <span style="font-size: 1.5rem;">⚙️</span>
            <span>后台管理</span>
=======
            <span style="font-size: 1.5rem;">
                <?= $render_icon($menu_title_icon) ?>
            </span>
            <span><?= e($menu_title) ?></span>
>>>>>>> a3d11b8 (sync: update open-source release)
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="/admin" class="nav-link <?= $current_uri === '/admin' ? 'active' : '' ?>">
<<<<<<< HEAD
            <span class="icon">📊</span> 仪表盘
        </a>
        <a href="/admin/stats" class="nav-link <?= str_contains($current_uri, '/admin/stats') ? 'active' : '' ?>">
            <span class="icon">📈</span> 站点统计
        </a>
        <a href="/admin/posts/new" class="nav-link <?= str_contains($current_uri, '/admin/posts/new') ? 'active' : '' ?>">
            <span class="icon">📝</span> 写文章
        </a>
        <a href="/admin/featured" class="nav-link <?= str_contains($current_uri, '/admin/featured') ? 'active' : '' ?>">
            <span class="icon">🌟</span> 推荐管理
        </a>
        <div style="height: 12px;"></div>
        <div style="padding: 0 12px 8px; font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-secondary); letter-spacing: 0.05em; font-weight: 600;">资源管理</div>
        <a href="/admin/categories" class="nav-link <?= str_contains($current_uri, '/admin/categories') ? 'active' : '' ?>">
            <span class="icon">📂</span> 分类
        </a>
        <a href="/admin/tags" class="nav-link <?= str_contains($current_uri, '/admin/tags') ? 'active' : '' ?>">
            <span class="icon">🏷️</span> 标签
        </a>
        <a href="/admin/uploads" class="nav-link <?= str_contains($current_uri, '/admin/uploads') ? 'active' : '' ?>">
            <span class="icon">🖼️</span> 素材
        </a>
        <a href="/admin/cards" class="nav-link <?= str_contains($current_uri, '/admin/cards') ? 'active' : '' ?>">
            <span class="icon">🗂️</span> 卡片
        </a>
        <div style="height: 12px;"></div>
        <div style="padding: 0 12px 8px; font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-secondary); letter-spacing: 0.05em; font-weight: 600;">系统</div>
        <a href="/admin/users" class="nav-link <?= str_contains($current_uri, '/admin/users') ? 'active' : '' ?>">
            <span class="icon">👥</span> 用户
        </a>
        <a href="/admin/comments" class="nav-link <?= str_contains($current_uri, '/admin/comments') ? 'active' : '' ?>">
            <span class="icon">💬</span> 评论
        </a>
        <a href="/admin/settings" class="nav-link <?= str_contains($current_uri, '/admin/settings') ? 'active' : '' ?>">
            <span class="icon">⚙️</span> 设置
=======
            <span class="icon"><?= $render_icon($get_setting('admin_menu_dashboard_icon', '📊')) ?></span>
            <?= e($get_setting('admin_menu_dashboard_label', '仪表盘')) ?>
        </a>
        <?php if ($can_stats): ?>
            <a href="/admin/stats" class="nav-link <?= str_contains($current_uri, '/admin/stats') ? 'active' : '' ?>">
                <span class="icon"><?= $render_icon($get_setting('admin_menu_stats_icon', '📈')) ?></span>
                <?= e($get_setting('admin_menu_stats_label', '站点统计')) ?>
            </a>
        <?php endif; ?>
        <a href="/admin/posts/new" class="nav-link <?= str_contains($current_uri, '/admin/posts/new') ? 'active' : '' ?>">
            <span class="icon"><?= $render_icon($get_setting('admin_menu_write_icon', '📝')) ?></span>
            <?= e($get_setting('admin_menu_write_label', '写文章')) ?>
        </a>
        <?php if ($can_featured): ?>
            <a href="/admin/featured" class="nav-link <?= str_contains($current_uri, '/admin/featured') ? 'active' : '' ?>">
                <span class="icon"><?= $render_icon($get_setting('admin_menu_featured_icon', '🌟')) ?></span>
                <?= e($get_setting('admin_menu_featured_label', '推荐管理')) ?>
            </a>
        <?php endif; ?>
        <?php if ($can_categories || $can_tags || $can_uploads || $can_cards): ?>
            <div style="height: 12px;"></div>
            <div style="padding: 0 12px 8px; font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-secondary); letter-spacing: 0.05em; font-weight: 600;"><?= e($section_resources) ?></div>
            <?php if ($can_categories): ?>
                <a href="/admin/categories" class="nav-link <?= str_contains($current_uri, '/admin/categories') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_categories_icon', '📂')) ?></span>
                    <?= e($get_setting('admin_menu_categories_label', '分类')) ?>
                </a>
            <?php endif; ?>
            <?php if ($can_tags): ?>
                <a href="/admin/tags" class="nav-link <?= str_contains($current_uri, '/admin/tags') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_tags_icon', '🏷️')) ?></span>
                    <?= e($get_setting('admin_menu_tags_label', '标签')) ?>
                </a>
            <?php endif; ?>
            <?php if ($can_uploads): ?>
                <a href="/admin/uploads" class="nav-link <?= str_contains($current_uri, '/admin/uploads') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_uploads_icon', '🖼️')) ?></span>
                    <?= e($get_setting('admin_menu_uploads_label', '素材')) ?>
                </a>
            <?php endif; ?>
            <?php if ($can_cards): ?>
                <a href="/admin/cards" class="nav-link <?= str_contains($current_uri, '/admin/cards') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_cards_icon', '🗂️')) ?></span>
                    <?= e($get_setting('admin_menu_cards_label', '卡片')) ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($can_users || $can_groups || $can_settings || $can_maintenance): ?>
            <div style="height: 12px;"></div>
            <div style="padding: 0 12px 8px; font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-secondary); letter-spacing: 0.05em; font-weight: 600;"><?= e($section_system) ?></div>
            <?php if (!empty($config['is_test']) && $can_maintenance): ?>
                <a href="/admin/maintenance" class="nav-link <?= str_contains($current_uri, '/admin/maintenance') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_maintenance_icon', '🛠️')) ?></span>
                    <?= e($get_setting('admin_menu_maintenance_label', '维护')) ?>
                </a>
            <?php endif; ?>
            <?php if ($can_users): ?>
                <a href="/admin/users" class="nav-link <?= str_contains($current_uri, '/admin/users') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_users_icon', '👥')) ?></span>
                    <?= e($get_setting('admin_menu_users_label', '用户')) ?>
                </a>
            <?php endif; ?>
            <?php if ($can_groups): ?>
                <a href="/admin/groups" class="nav-link <?= str_contains($current_uri, '/admin/groups') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_groups_icon', '🧩')) ?></span>
                    <?= e($get_setting('admin_menu_groups_label', '权限组')) ?>
                </a>
            <?php endif; ?>
            <?php if ($can_settings): ?>
                <a href="/admin/settings" class="nav-link <?= str_contains($current_uri, '/admin/settings') ? 'active' : '' ?>">
                    <span class="icon"><?= $render_icon($get_setting('admin_menu_settings_icon', '⚙️')) ?></span>
                    <?= e($get_setting('admin_menu_settings_label', '设置')) ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <a href="/admin/comments" class="nav-link <?= str_contains($current_uri, '/admin/comments') ? 'active' : '' ?>">
            <span class="icon"><?= $render_icon($get_setting('admin_menu_comments_icon', '💬')) ?></span>
            <?= e($get_setting('admin_menu_comments_label', '评论')) ?>
>>>>>>> a3d11b8 (sync: update open-source release)
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div style="font-size: 0.8rem; color: var(--admin-text-secondary);">
            Logged in as Admin
        </div>
    </div>
</aside>
