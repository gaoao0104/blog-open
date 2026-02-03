<?php
$title = '站点配置 - 后台菜单';
require __DIR__ . '/../partials/admin-header.php';
$active_tab = $active_tab ?? 'menu';
require __DIR__ . '/../partials/admin-settings-nav.php';

$items = [
    ['key' => 'dashboard', 'label' => '仪表盘'],
    ['key' => 'stats', 'label' => '站点统计'],
    ['key' => 'write', 'label' => '写文章'],
    ['key' => 'featured', 'label' => '推荐管理'],
    ['key' => 'categories', 'label' => '分类'],
    ['key' => 'tags', 'label' => '标签'],
    ['key' => 'uploads', 'label' => '素材'],
    ['key' => 'cards', 'label' => '卡片'],
    ['key' => 'comments', 'label' => '评论'],
    ['key' => 'users', 'label' => '用户'],
    ['key' => 'groups', 'label' => '权限组'],
    ['key' => 'settings', 'label' => '设置'],
    ['key' => 'maintenance', 'label' => '维护'],
];
?>

<div class="card" style="max-width: 900px;">
    <div class="card-header">
        <h2 class="card-title">后台菜单配置</h2>
    </div>

    <form class="post-form" method="post" action="/admin/settings/menu">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="form-group">
            <label class="form-label">侧边栏标题</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <input type="text" name="admin_menu_title" value="<?= e($settings['admin_menu_title'] ?? '') ?>" class="form-control" placeholder="标题">
                <input type="text" name="admin_menu_title_icon" value="<?= e($settings['admin_menu_title_icon'] ?? '') ?>" class="form-control" placeholder="图标（emoji 或图片 URL）">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">分组标题</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <input type="text" name="admin_menu_section_resources_label" value="<?= e($settings['admin_menu_section_resources_label'] ?? '') ?>" class="form-control" placeholder="资源管理">
                <input type="text" name="admin_menu_section_system_label" value="<?= e($settings['admin_menu_section_system_label'] ?? '') ?>" class="form-control" placeholder="系统">
            </div>
            <small style="color: var(--admin-text-secondary); display: block; margin-top: 6px;">图标可输入 emoji，或填写图片 URL（可用素材库上传后复制链接）。</small>
        </div>

        <div style="background: var(--admin-bg); padding: 16px; border-radius: 8px;">
            <div class="form-label mb-4">菜单项配置</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <?php foreach ($items as $item): ?>
                    <?php
                        $labelKey = 'admin_menu_' . $item['key'] . '_label';
                        $iconKey = 'admin_menu_' . $item['key'] . '_icon';
                    ?>
                    <div style="border: 1px solid var(--admin-border); border-radius: 8px; padding: 12px;">
                        <div style="font-weight: 600; margin-bottom: 8px;"><?= e($item['label']) ?></div>
                        <input type="text" name="<?= e($labelKey) ?>" value="<?= e($settings[$labelKey] ?? '') ?>" class="form-control" placeholder="菜单名称" style="margin-bottom: 8px;">
                        <input type="text" name="<?= e($iconKey) ?>" value="<?= e($settings[$iconKey] ?? '') ?>" class="form-control" placeholder="图标（emoji 或图片 URL）">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 16px;">保存配置</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
