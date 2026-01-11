<?php
$title = '站点配置 - 显示开关';
require __DIR__ . '/../partials/admin-header.php';
?>

<?php
$active_tab = $active_tab ?? 'display';
require __DIR__ . '/../partials/admin-settings-nav.php';
?>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h2 class="card-title">显示开关</h2>
    </div>

    <form class="post-form" method="post" action="/admin/settings" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        
        <div class="form-group">
            <label class="form-label">站点名称</label>
            <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>" class="form-control">
        </div>
        
        <div class="form-group">
            <label class="form-label">左上角标题</label>
            <input type="text" name="header_title" value="<?= e($settings['header_title'] ?? '') ?>" class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Favicon 图标</label>
            <div style="display: flex; gap: 8px;">
                 <input type="text" name="favicon_url" value="<?= e($settings['favicon_url'] ?? '') ?>" class="form-control" placeholder="输入图片地址" style="flex: 1;">
                 <input type="file" name="favicon_file" accept=".ico,.png,.jpg,.jpeg,.gif" class="form-control" style="width: auto;">
            </div>
            <small style="color: var(--text-secondary); display: block; margin-top: 4px;">支持上传或输入 URL。建议上传 .ico 或 .png 格式。</small>
        </div>

        <div class="form-group">
            <label class="form-label">页脚版权文案</label>
            <input type="text" name="footer_copyright" value="<?= e($settings['footer_copyright'] ?? '') ?>" class="form-control">
            <small style="color: var(--text-secondary); display: block; margin-top: 4px;">留空则使用默认格式。</small>
        </div>
        
        <div style="background: var(--admin-bg); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <div class="form-label mb-4">显示设置</div>
            <div class="inline-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_nav_home" <?= ($settings['show_nav_home'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示首页导航</span>
                </label>
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_nav_search" <?= ($settings['show_nav_search'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示搜索导航</span>
                </label>
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_nav_admin" <?= ($settings['show_nav_admin'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示后台导航</span>
                </label>
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_search_form" <?= ($settings['show_search_form'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示顶部搜索框</span>
                </label>
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_hero" <?= ($settings['show_hero'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示欢迎模块</span>
                </label>
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_admin_card" <?= ($settings['show_admin_card'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示管理员名片</span>
                </label>
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_sidebar_categories" <?= ($settings['show_sidebar_categories'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示分类模块</span>
                </label>
                <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="show_sidebar_tags" <?= ($settings['show_sidebar_tags'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span>显示标签模块</span>
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">保存配置</button>
    </form>
</div>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
