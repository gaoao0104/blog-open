<?php
$title = '站点配置 - 分享设置';
require __DIR__ . '/../partials/admin-header.php';
?>

<?php
$active_tab = $active_tab ?? 'share';
require __DIR__ . '/../partials/admin-settings-nav.php';

$shareItems = [
    ['key' => 'system', 'label' => '系统分享'],
    ['key' => 'copy', 'label' => '复制链接'],
    ['key' => 'wechat', 'label' => '微信分享'],
    ['key' => 'qq', 'label' => 'QQ分享'],
    ['key' => 'weibo', 'label' => '微博分享'],
    ['key' => 'qzone', 'label' => 'QQ空间'],
    ['key' => 'telegram', 'label' => 'Telegram'],
];
?>

<div class="card" style="max-width: 860px;">
    <div class="card-header">
        <h2 class="card-title">分享设置</h2>
    </div>

    <form class="post-form" method="post" action="/admin/settings/share" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div style="background: var(--admin-bg); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <div class="form-label mb-4">启用开关</div>
            <div class="inline-form" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <?php foreach ($shareItems as $item): ?>
                    <?php $enableKey = 'share_enable_' . $item['key']; ?>
                    <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                        <input type="checkbox" name="<?= e($enableKey) ?>" <?= ($settings[$enableKey] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span><?= e($item['label']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">分享图标配置</label>
            <p style="color: var(--text-secondary); margin: 0 0 16px;">支持上传或填写图标 URL，建议使用正方形 PNG/SVG。</p>
            <div style="display: grid; gap: 16px;">
                <?php foreach ($shareItems as $item): ?>
                    <?php
                        $iconKey = 'share_icon_' . $item['key'];
                        $iconValue = $settings[$iconKey] ?? '';
                    ?>
                    <div class="card" style="padding: 16px; border-radius: 10px; border: 1px solid var(--admin-border);">
                        <div class="form-label mb-2"><?= e($item['label']) ?> 图标</div>
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <div style="width: 42px; height: 42px; border-radius: 50%; border: 1px solid var(--admin-border); display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--admin-surface);">
                                <?php if (!empty($iconValue)): ?>
                                    <img src="<?= e($iconValue) ?>" alt="<?= e($item['label']) ?>" style="width: 70%; height: 70%; object-fit: contain;">
                                <?php else: ?>
                                    <span style="font-size: 12px; color: var(--admin-text-secondary);"><?= e($item['label']) ?></span>
                                <?php endif; ?>
                            </div>
                            <input type="text" name="<?= e($iconKey) ?>" value="<?= e($iconValue) ?>" class="form-control" placeholder="输入图标 URL" style="flex: 1; min-width: 240px;">
                            <input type="file" name="<?= e($iconKey . '_file') ?>" accept=".png,.jpg,.jpeg,.gif,.svg" class="form-control" style="width: auto;">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">保存配置</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
