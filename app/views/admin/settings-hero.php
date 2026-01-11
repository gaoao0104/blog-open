<?php
$title = '站点配置 - 欢迎模块';
require __DIR__ . '/../partials/admin-header.php';
?>

<?php
$active_tab = $active_tab ?? 'hero';
require __DIR__ . '/../partials/admin-settings-nav.php';
?>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h2 class="card-title">欢迎模块</h2>
    </div>

    <form class="post-form" method="post" action="/admin/settings/hero" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div style="background: var(--admin-bg); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <div class="form-label mb-4">展示模式</div>
            <div class="form-group">
                <div class="flex gap-4">
                    <label class="radio-label flex items-center gap-2" style="cursor: pointer;">
                        <input type="radio" name="hero_mode" value="text" <?= ($settings['hero_mode'] ?? 'text') === 'text' ? 'checked' : '' ?> onclick="toggleHeroMode('text')">
                        <span>欢迎语模式</span>
                    </label>
                    <label class="radio-label flex items-center gap-2" style="cursor: pointer;">
                        <input type="radio" name="hero_mode" value="image" <?= ($settings['hero_mode'] ?? 'text') === 'image' ? 'checked' : '' ?> onclick="toggleHeroMode('image')">
                        <span>图片模式 (16:9)</span>
                    </label>
                </div>
            </div>

            <div id="hero-text-fields" style="<?= ($settings['hero_mode'] ?? 'text') === 'text' ? '' : 'display: none;' ?>">
                <div class="flex gap-4 mb-4">
                    <div style="flex: 1;">
                        <label class="form-label">欢迎标题</label>
                        <input type="text" name="hero_title" value="<?= e($settings['hero_title'] ?? '') ?>" class="form-control">
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">欢迎副标题</label>
                        <input type="text" name="hero_subtitle" value="<?= e($settings['hero_subtitle'] ?? '') ?>" class="form-control">
                    </div>
                </div>
            </div>

            <div id="hero-image-fields" style="<?= ($settings['hero_mode'] ?? 'text') === 'image' ? '' : 'display: none;' ?>">
                <div class="form-group">
                    <label class="form-label">欢迎图片 (建议 16:9)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="hero_image_url" value="<?= e($settings['hero_image_url'] ?? '') ?>" class="form-control" placeholder="输入图片地址" style="flex: 1;">
                        <input type="file" name="hero_image_file" accept="image/*" class="form-control" style="width: auto;">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">保存配置</button>
        <p style="margin-top: 8px; color: var(--admin-text-secondary); font-size: 0.85rem;">显示开关请在“显示开关”页面设置。</p>
    </form>
</div>

<script>
function toggleHeroMode(mode) {
    if (mode === 'text') {
        document.getElementById('hero-text-fields').style.display = 'block';
        document.getElementById('hero-image-fields').style.display = 'none';
    } else {
        document.getElementById('hero-text-fields').style.display = 'none';
        document.getElementById('hero-image-fields').style.display = 'block';
    }
}
</script>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
