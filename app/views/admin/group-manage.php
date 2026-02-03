<?php
$title = '管理 ' . e($group['name']) . ' 权限';
require __DIR__ . '/../partials/admin-header.php';
$isSuper = !empty($group['is_super']);
$selected = $selected_permissions ?? [];
?>

<div style="margin-bottom: 24px;">
    <a href="/admin/groups" class="btn btn-secondary">&larr; 返回权限组列表</a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">配置 <?= e($group['name']) ?> 权限</h2>
        <?php if ($isSuper): ?>
            <div style="color: var(--secondary); margin-top:4px;">
                注意：该组为超级管理员组，默认拥有所有权限且不可更改。
            </div>
        <?php endif; ?>
    </div>
    
    <form method="post" action="/admin/groups/update-permissions">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="id" value="<?= e((string)$group['id']) ?>">
        
        <div style="padding: 24px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                <?php foreach ($permissions as $key => $label): ?>
                    <label class="inline-check flex items-center gap-2" 
                           style="padding: 12px; border: 1px solid var(--admin-border); border-radius: 8px; cursor: pointer; transition: all 0.2s; background: var(--admin-bg-light); opacity: <?= $isSuper ? '0.6' : '1' ?>;">
                        <input type="checkbox" name="permissions[]" value="<?= e($key) ?>" 
                               <?= in_array($key, $selected, true) ? 'checked' : '' ?> 
                               <?= $isSuper ? 'disabled' : '' ?>>
                        <span style="font-weight: 500;"><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: var(--admin-bg-light); display:flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" <?= $isSuper ? 'disabled' : '' ?>>保存权限配置</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
