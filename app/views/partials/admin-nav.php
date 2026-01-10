<?php
$current_uri = $_SERVER['REQUEST_URI'];
?>
<div class="admin-actions" style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--glass-border);">
    <a class="button <?= $current_uri === '/admin' ? '' : 'ghost' ?>" href="/admin">📊 仪表盘</a>
    <a class="button <?= str_contains($current_uri, '/admin/posts/new') ? '' : 'ghost' ?>" href="/admin/posts/new">📝 写文章</a>
    <a class="button <?= str_contains($current_uri, '/admin/featured') ? '' : 'ghost' ?>" href="/admin/featured">🌟 推荐管理</a>
    <a class="button <?= str_contains($current_uri, '/admin/categories') ? '' : 'ghost' ?>" href="/admin/categories">📂 分类</a>
    <a class="button <?= str_contains($current_uri, '/admin/tags') ? '' : 'ghost' ?>" href="/admin/tags">🏷️ 标签</a>
    <a class="button <?= str_contains($current_uri, '/admin/users') ? '' : 'ghost' ?>" href="/admin/users">👥 用户</a>
    <a class="button <?= str_contains($current_uri, '/admin/comments') ? '' : 'ghost' ?>" href="/admin/comments">💬 评论</a>
    <a class="button <?= str_contains($current_uri, '/admin/uploads') ? '' : 'ghost' ?>" href="/admin/uploads">🖼️ 素材</a>
    <a class="button <?= str_contains($current_uri, '/admin/cards') ? '' : 'ghost' ?>" href="/admin/cards">🗂️ 卡片</a>
    <a class="button <?= str_contains($current_uri, '/admin/settings') ? '' : 'ghost' ?>" href="/admin/settings">⚙️ 设置</a>
    <a class="button ghost" href="/admin/logout">🚪 退出</a>
</div>
