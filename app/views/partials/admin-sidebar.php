<?php
$current_uri = $_SERVER['REQUEST_URI'];
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="/admin" class="logo">
            <span style="font-size: 1.5rem;">⚙️</span>
            <span>后台管理</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="/admin" class="nav-link <?= $current_uri === '/admin' ? 'active' : '' ?>">
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
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div style="font-size: 0.8rem; color: var(--admin-text-secondary);">
            Logged in as Admin
        </div>
    </div>
</aside>
