<?php
$title = '站点配置 - 管理员名片';
require __DIR__ . '/../partials/admin-header.php';
?>

<?php
$active_tab = $active_tab ?? 'admin_card';
require __DIR__ . '/../partials/admin-settings-nav.php';
?>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h2 class="card-title">管理员名片</h2>
    </div>

    <form class="post-form" method="post" action="/admin/settings/admin-card" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="form-group">
            <label class="form-label">头像</label>
            <div style="display: flex; gap: 8px;">
                 <input type="text" name="admin_card_avatar" value="<?= e($settings['admin_card_avatar'] ?? '') ?>" class="form-control" placeholder="输入图片地址" style="flex: 1;">
                 <input type="file" name="admin_card_avatar_file" accept="image/*" class="form-control" style="width: auto;">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">昵称 / 名称</label>
            <input type="text" name="admin_card_name" value="<?= e($settings['admin_card_name'] ?? '') ?>" class="form-control" placeholder="例如：Admin">
        </div>

        <div class="form-group">
            <label class="form-label">认证标识</label>
            <div style="display: flex; gap: 8px;">
                 <input type="text" name="admin_card_badge" value="<?= e($settings['admin_card_badge'] ?? '') ?>" class="form-control" placeholder="输入图片地址 (留空使用默认)" style="flex: 1;">
                 <input type="file" name="admin_card_badge_file" accept="image/*" class="form-control" style="width: auto;">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">个性签名</label>
            <textarea name="admin_card_bio" class="form-control" rows="3" placeholder="写一句话介绍自己..."><?= e($settings['admin_card_bio'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">社交按钮 (最多4个)</label>
            <?php for ($i = 1; $i <= 4; $i++): ?>
            <div style="border: 1px solid rgba(0,0,0,0.1); padding: 12px; border-radius: 8px; margin-bottom: 12px; background: rgba(255,255,255,0.5);">
                <div style="font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">按钮 <?= $i ?></div>
                <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                     <input type="text" name="social_icon_<?= $i ?>" value="<?= e($settings["social_icon_{$i}"] ?? '') ?>" class="form-control" placeholder="图标地址 (建议正方形)" style="flex: 1;">
                     <input type="file" name="social_icon_<?= $i ?>_file" accept="image/*" class="form-control" style="width: auto;">
                </div>
                <input type="text" name="social_link_<?= $i ?>" value="<?= e($settings["social_link_{$i}"] ?? '') ?>" class="form-control" placeholder="跳转链接 (例如: https://github.com/...)">
            </div>
            <?php endfor; ?>
        </div>

        <button type="submit" class="btn btn-primary">保存配置</button>
        <p style="margin-top: 8px; color: var(--admin-text-secondary); font-size: 0.85rem;">显示开关请在“显示开关”页面设置。</p>
    </form>
</div>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
