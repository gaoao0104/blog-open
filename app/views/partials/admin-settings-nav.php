<?php
$active_tab = $active_tab ?? 'display';
?>
<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">站点配置</h2>
    </div>
    <div class="inline-form" style="display: flex; flex-wrap: wrap; gap: 8px;">
        <a class="btn btn-sm <?= $active_tab === 'display' ? 'btn-primary' : 'btn-secondary' ?>" href="/admin/settings">显示开关</a>
        <a class="btn btn-sm <?= $active_tab === 'hero' ? 'btn-primary' : 'btn-secondary' ?>" href="/admin/settings/hero">欢迎模块</a>
        <a class="btn btn-sm <?= $active_tab === 'admin_card' ? 'btn-primary' : 'btn-secondary' ?>" href="/admin/settings/admin-card">管理员名片</a>
<<<<<<< HEAD
=======
        <a class="btn btn-sm <?= $active_tab === 'share' ? 'btn-primary' : 'btn-secondary' ?>" href="/admin/settings/share">分享设置</a>
        <a class="btn btn-sm <?= $active_tab === 'menu' ? 'btn-primary' : 'btn-secondary' ?>" href="/admin/settings/menu">后台菜单</a>
>>>>>>> a3d11b8 (sync: update open-source release)
    </div>
</div>
