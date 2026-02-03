<?php
$title = '权限组';
require __DIR__ . '/../partials/admin-header.php';
$groups = $groups ?? [];
$permissions = $permissions ?? [];
$groupPermissions = $group_permissions ?? [];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; font-weight: 600; color: var(--admin-text-primary);">权限组管理</h1>
    <button type="button" class="btn btn-primary" onclick="openCreateModal()">
        <span style="margin-right: 6px;">+</span> 新建分组
    </button>
</div>

<div class="card" style="overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--admin-border); background: var(--admin-bg-light);">
                    <th style="padding: 12px 16px; font-weight: 600; color: var(--admin-text-secondary); width: 25%;">名称</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: var(--admin-text-secondary); width: 35%;">描述</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: var(--admin-text-secondary); width: 10%;">用户数</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: var(--admin-text-secondary); width: 10%;">类型</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: var(--admin-text-secondary); text-align: right;">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <?php
                        $groupId = (int)$group['id'];
                        $isSuper = !empty($group['is_super']);
                        $groupPermissionsList = $groupPermissions[$groupId] ?? [];
                    ?>
                    <tr style="border-bottom: 1px solid var(--admin-border);">
                        <td style="padding: 12px 16px; font-weight: 500;">
                            <?= e($group['name']) ?>
                        </td>
                        <td style="padding: 12px 16px; color: var(--admin-text-secondary);">
                            <?= e($group['description'] ?? '-') ?>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span class="badge" style="background: var(--admin-bg-light); color: var(--admin-text-primary);">
                                <?= e((string)($group['user_count'] ?? 0)) ?>
                            </span>
                        </td>
                        <td style="padding: 12px 16px;">
                            <?php if ($isSuper): ?>
                                <span class="badge" style="background: #e0f2fe; color: #0284c7;">超级管理员</span>
                            <?php else: ?>
                                <span class="badge" style="background: var(--admin-bg-light); color: var(--admin-text-secondary);">普通组</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <button type="button" class="btn btn-sm btn-secondary" 
                                        onclick='openEditModal(<?= json_encode($group) ?>)'>
                                    编辑
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" 
                                        onclick='openPermissionsModal(<?= $groupId ?>, "<?= e($group['name']) ?>", <?= $isSuper ? "true" : "false" ?>, <?= json_encode($groupPermissionsList) ?>)'>
                                    权限
                                </button>
                                <form method="post" action="/admin/groups/delete" onsubmit="return confirm('确定删除该分组吗？此操作不可恢复。');" style="margin:0;">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                    <input type="hidden" name="id" value="<?= e((string)$groupId) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">删除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Group -->
<div id="modal-create" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">新建分组</h2>
            <button type="button" onclick="closeModal('modal-create')" style="background:none; border:none; cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>
        <form method="post" action="/admin/groups/create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div style="padding: 24px;">
                <div style="margin-bottom: 16px;">
                    <label class="form-label">名称</label>
                    <input type="text" name="name" class="form-control" required placeholder="例如：编辑组">
                </div>
                <div style="margin-bottom: 16px;">
                    <label class="form-label">描述</label>
                    <input type="text" name="description" class="form-control" placeholder="可选说明">
                </div>
                <div>
                     <label class="inline-check flex items-center gap-2">
                        <input type="checkbox" name="is_super" value="1">
                        <span style="font-weight: 500;">设为超级管理员组</span>
                    </label>
                    <div style="font-size: 0.85rem; color: var(--admin-text-secondary); margin-top: 4px;">超级管理员拥有所有权限，无需配置。</div>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; text-align: right; background: var(--admin-bg-light); border-top: 1px solid var(--admin-border);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-create')" style="margin-right: 8px;">取消</button>
                <button type="submit" class="btn btn-primary">创建分组</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Group Info -->
<div id="modal-edit" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">编辑分组信息</h2>
            <button type="button" onclick="closeModal('modal-edit')" style="background:none; border:none; cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>
        <form method="post" action="/admin/groups/update-info">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <input type="hidden" name="id" id="edit-id">
            <div style="padding: 24px;">
                <div style="margin-bottom: 16px;">
                    <label class="form-label">名称</label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div style="margin-bottom: 16px;">
                    <label class="form-label">描述</label>
                    <input type="text" name="description" id="edit-description" class="form-control">
                </div>
                <div>
                     <label class="inline-check flex items-center gap-2">
                        <input type="checkbox" name="is_super" id="edit-is-super" value="1">
                        <span style="font-weight: 500;">设为超级管理员组</span>
                    </label>
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; text-align: right; background: var(--admin-bg-light); border-top: 1px solid var(--admin-border);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit')" style="margin-right: 8px;">取消</button>
                <button type="submit" class="btn btn-primary">保存修改</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Permissions -->
<div id="modal-permissions" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 700px; margin: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-height: 90vh; display: flex; flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">权限配置 <span id="perm-group-name" style="font-weight: 400; font-size: 0.9em; color: var(--admin-text-secondary);"></span></h2>
            <button type="button" onclick="closeModal('modal-permissions')" style="background:none; border:none; cursor:pointer; font-size:1.5rem; line-height:1;">&times;</button>
        </div>
        <form method="post" action="/admin/groups/update-permissions" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <input type="hidden" name="id" id="perm-id">
            
            <div id="perm-super-alert" style="padding: 16px 24px; background: #e0f2fe; color: #0284c7; font-size: 0.9rem; border-bottom: 1px solid #bae6fd; display: none;">
                当前为超级管理员组，拥有所有权限，无需配置。
            </div>

            <div style="padding: 24px; overflow-y: auto; flex: 1;">
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px;">
                    <?php foreach ($permissions as $key => $label): ?>
                        <label class="inline-check flex items-center gap-2" 
                               style="padding: 8px 12px; border: 1px solid var(--admin-border); border-radius: 6px; cursor: pointer; transition: background 0.1s;">
                            <input type="checkbox" name="permissions[]" value="<?= e($key) ?>" class="perm-checkbox">
                            <span style="font-size: 0.95rem;"><?= e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="card-footer" style="padding: 16px 24px; text-align: right; background: var(--admin-bg-light); border-top: 1px solid var(--admin-border);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-permissions')" style="margin-right: 8px;">取消</button>
                <button type="submit" class="btn btn-primary" id="perm-save-btn">保存权限</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modal-create').style.display = 'flex';
}

function openEditModal(group) {
    document.getElementById('edit-id').value = group.id;
    document.getElementById('edit-name').value = group.name;
    document.getElementById('edit-description').value = group.description || '';
    document.getElementById('edit-is-super').checked = group.is_super == 1;
    document.getElementById('modal-edit').style.display = 'flex';
}

function openPermissionsModal(groupId, groupName, isSuper, currentPerms) {
    document.getElementById('perm-id').value = groupId;
    document.getElementById('perm-group-name').textContent = ' - ' + groupName;
    
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = currentPerms.includes(cb.value);
        cb.disabled = isSuper;
    });

    const superAlert = document.getElementById('perm-super-alert');
    const saveBtn = document.getElementById('perm-save-btn');
    
    if (isSuper) {
        superAlert.style.display = 'block';
        saveBtn.disabled = true;
    } else {
        superAlert.style.display = 'none';
        saveBtn.disabled = false;
    }

    document.getElementById('modal-permissions').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = "none";
    }
}
</script>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
