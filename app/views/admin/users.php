<?php
$title = '用户管理';
require __DIR__ . '/../partials/admin-header.php';
<<<<<<< HEAD
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">用户管理</h2>
    </div>

    <!-- Create User Form -->
    <div style="background: var(--admin-bg); padding: 20px; border-radius: 8px; margin-bottom: 24px;">
        <h4 style="margin-top:0; margin-bottom:16px; font-size:1rem; font-weight: 600;">添加新用户</h4>
        <form method="post" enctype="multipart/form-data" action="/admin/users/create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" style="font-size: 0.85rem;">用户名 <span style="color:red">*</span></label>
                    <input type="text" name="username" placeholder="用户名" required class="form-control">
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.85rem;">密码 <span style="color:red">*</span></label>
                    <input type="password" name="password" placeholder="密码" required class="form-control">
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.85rem;">昵称 (可选)</label>
                    <input type="text" name="nickname" placeholder="昵称" class="form-control">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
                <div>
                    <label class="form-label" style="font-size: 0.85rem;">头像</label>
                    <input type="file" name="avatar_image" accept="image/*" class="form-control" style="padding: 8px;">
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.85rem;">认证图标 (V标)</label>
                    <input type="file" name="badge_image" accept="image/*" class="form-control" style="padding: 8px;">
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.85rem;">认证状态</label>
                    <div style="height: 38px; display: flex; align-items: center; border: 1px solid transparent;"> <!-- Spacer to match input height -->
                        <label class="inline-check flex items-center gap-2" style="cursor: pointer; user-select: none;">
                            <input type="checkbox" name="is_verified">
                            <span style="font-weight: 500;">官方认证账号</span>
                        </label>
                   </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">创建用户</button>
                </div>
            </div>
        </form>
    </div>
=======
$groups = $groups ?? [];
$defaultGroupId = 0;
foreach ($groups as $group) {
    if (empty($group['is_super'])) {
        $defaultGroupId = (int)$group['id'];
        break;
    }
}
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; font-weight: 600; color: var(--admin-text-primary);">用户管理</h1>
    <button type="button" class="btn btn-primary" onclick="openCreateUserModal()">
        <span style="margin-right: 6px;">+</span> 新建用户
    </button>
</div>

<div class="card">
    <!-- List starts here -->
>>>>>>> a3d11b8 (sync: update open-source release)

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">头像</th>
                    <th>用户名 / 昵称</th>
<<<<<<< HEAD
=======
                    <th>分组</th>
>>>>>>> a3d11b8 (sync: update open-source release)
                    <th>认证状态</th>
                    <th>创建时间</th>
                    <th class="text-right">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <?php if (!empty($user['avatar_url'])): ?>
                                <img src="<?= e($user['avatar_url']) ?>" alt="<?= e($user['username']) ?>" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1px solid var(--admin-border);">
                            <?php else: ?>
                                <span style="display:inline-block; width:36px; height:36px; border-radius:50%; background:var(--admin-border); text-align:center; line-height:36px; color:var(--admin-text-secondary); font-size: 12px;">
                                    <?= e(mb_substr($user['username'], 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);"><?= e($user['username']) ?></div>
                            <div style="font-size: 0.85rem; color: var(--admin-text-secondary);"><?= e($user['nickname'] ?? '-') ?></div>
                        </td>
                        <td>
<<<<<<< HEAD
=======
                            <?php if (!empty($user['group_name'])): ?>
                                <span class="badge <?= !empty($user['group_id']) && !empty($user['group_is_super']) ? 'badge-success' : 'badge-gray' ?>">
                                    <?= e($user['group_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-gray">未分组</span>
                            <?php endif; ?>
                        </td>
                        <td>
>>>>>>> a3d11b8 (sync: update open-source release)
                            <div class="flex items-center gap-2">
                                <?php if ($user['is_verified']): ?>
                                    <span class="badge badge-success">已认证</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">普通</span>
                                <?php endif; ?>
                                
                                <?php if (!empty($user['verified_badge_url'])): ?>
                                    <img src="<?= e($user['verified_badge_url']) ?>" alt="badge" style="width: 16px; height: 16px;" title="认证图标">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--admin-text-secondary);">
                            <?= e(date('Y-m-d', strtotime($user['created_at']))) ?>
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn btn-secondary btn-sm edit-user-btn" 
                                data-id="<?= e((string)$user['id']) ?>"
                                data-username="<?= e($user['username']) ?>"
                                data-nickname="<?= e($user['nickname'] ?? '') ?>"
                                data-verified="<?= $user['is_verified'] ? '1' : '0' ?>"
                                data-avatar="<?= e($user['avatar_url'] ?? '') ?>"
                                data-badge="<?= e($user['verified_badge_url'] ?? '') ?>"
<<<<<<< HEAD
=======
                                data-group="<?= e((string)($user['group_id'] ?? 0)) ?>"
>>>>>>> a3d11b8 (sync: update open-source release)
                            >编辑</button>
                            <a href="/admin/users/delete?id=<?= e((string)$user['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定删除该用户吗?');">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>编辑用户</h3>
            <span class="close" id="edit-modal-close">&times;</span>
        </div>
        <form id="edit-user-form" method="post" enctype="multipart/form-data" action="/admin/users/update">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="form-group">
                <label class="form-label">用户名</label>
                <input type="text" name="username" id="edit-username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">昵称</label>
                <input type="text" name="nickname" id="edit-nickname" class="form-control">
            </div>
<<<<<<< HEAD
=======

            <div class="form-group">
                <label class="form-label">权限分组</label>
                <select name="group_id" id="edit-group" class="form-control">
                    <option value="0">不设置</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= e((string)$group['id']) ?>">
                            <?= e($group['name']) ?><?= !empty($group['is_super']) ? '（超级）' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
>>>>>>> a3d11b8 (sync: update open-source release)
            
            <div class="form-group">
                <label class="form-label">密码 (留空则不修改)</label>
                <input type="password" name="password" class="form-control" placeholder="******">
            </div>
            
            <div class="form-group">
                 <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="is_verified" id="edit-verified">
                    <span style="font-weight: 500;">官方认证账号</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">更换头像</label>
                <div id="current-avatar-preview" style="margin-bottom: 8px;"></div>
                <input type="file" name="avatar_image" accept="image/*" class="form-control">
            </div>
            
            <div class="form-group">
                <label class="form-label">更换认证图标</label>
                <div id="current-badge-preview" style="margin-bottom: 8px;"></div>
                <input type="file" name="badge_image" accept="image/*" class="form-control">
            </div>
            
            <div class="form-actions text-right" style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary">保存修改</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal {
        display: none; 
        position: fixed; 
        z-index: 1000; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.5); 
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }
    .modal-content {
        background-color: var(--admin-surface);
        margin: auto;
        padding: 24px;
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        width: 90%;
        max-width: 450px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        top: 50px; /* Slight offset */
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--admin-border);
        padding-bottom: 12px;
    }
    .modal-header h3 { margin: 0; font-size: 1.25rem; }
    .close { font-size: 28px; cursor: pointer; color: var(--admin-text-secondary); line-height: 1; }
    .close:hover { color: var(--admin-text); }
</style>

<<<<<<< HEAD
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('edit-user-modal');
    const closeBtn = document.getElementById('edit-modal-close');
    const editBtns = document.querySelectorAll('.edit-user-btn');
    
    // Form fields
    const idInput = document.getElementById('edit-id');
    const usernameInput = document.getElementById('edit-username');
    const nicknameInput = document.getElementById('edit-nickname');
=======
<!-- Create User Modal -->
<div id="create-user-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>添加新用户</h3>
            <span class="close" id="create-modal-close">&times;</span>
        </div>
        <form method="post" enctype="multipart/form-data" action="/admin/users/create">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            
            <div class="form-group">
                <label class="form-label">用户名 <span style="color:red">*</span></label>
                <input type="text" name="username" placeholder="用户名" required class="form-control">
            </div>
            
            <div class="form-group">
                <label class="form-label">密码 <span style="color:red">*</span></label>
                <input type="password" name="password" placeholder="密码" required class="form-control">
            </div>
            
            <div class="form-group">
                <label class="form-label">昵称 (可选)</label>
                <input type="text" name="nickname" placeholder="昵称" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">权限分组</label>
                <select name="group_id" class="form-control">
                    <option value="0">不设置</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= e((string)$group['id']) ?>" <?= $defaultGroupId === (int)$group['id'] ? 'selected' : '' ?>>
                            <?= e($group['name']) ?><?= !empty($group['is_super']) ? '（超级）' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                 <label class="inline-check flex items-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" name="is_verified">
                    <span style="font-weight: 500;">官方认证账号</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">头像</label>
                <input type="file" name="avatar_image" accept="image/*" class="form-control" style="padding: 8px;">
            </div>
            
            <div class="form-group">
                <label class="form-label">认证图标 (V标)</label>
                <input type="file" name="badge_image" accept="image/*" class="form-control" style="padding: 8px;">
            </div>
            
            <div class="form-actions text-right" style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary">创建用户</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateUserModal() {
    document.getElementById('create-user-modal').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => {
    // Edit Modal Logic
    const editModal = document.getElementById('edit-user-modal');
    const editCloseBtn = document.getElementById('edit-modal-close');
    const editBtns = document.querySelectorAll('.edit-user-btn');
    
    // Create Modal Logic
    const createModal = document.getElementById('create-user-modal');
    const createCloseBtn = document.getElementById('create-modal-close');
    
    // Form fields for Edit
    const idInput = document.getElementById('edit-id');
    const usernameInput = document.getElementById('edit-username');
    const nicknameInput = document.getElementById('edit-nickname');
    const groupInput = document.getElementById('edit-group');
>>>>>>> a3d11b8 (sync: update open-source release)
    const verifiedCheck = document.getElementById('edit-verified');
    const avatarPrev = document.getElementById('current-avatar-preview');
    const badgePrev = document.getElementById('current-badge-preview');
    
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
             const data = btn.dataset;
             idInput.value = data.id;
             usernameInput.value = data.username;
             nicknameInput.value = data.nickname;
             verifiedCheck.checked = data.verified === '1';
<<<<<<< HEAD
=======
             if (groupInput) {
                 groupInput.value = data.group || '0';
             }
>>>>>>> a3d11b8 (sync: update open-source release)
             
             if (data.avatar) {
                 avatarPrev.innerHTML = `<img src="${data.avatar}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:1px solid var(--admin-border);"> <span style="font-size:0.8rem; color:var(--admin-text-secondary); margin-left:8px;">当前头像</span>`;
             } else {
                 avatarPrev.innerHTML = '';
             }
             
             if (data.badge) {
                 badgePrev.innerHTML = `<img src="${data.badge}" style="width:20px; height:20px;"> <span style="font-size:0.8rem; color:var(--admin-text-secondary); margin-left:8px;">当前图标</span>`;
             } else {
                 badgePrev.innerHTML = '';
             }
             
<<<<<<< HEAD
             modal.style.display = 'block';
        });
    });
    
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
=======
             editModal.style.display = 'block';
        });
    });
    
    // Close handlers
    editCloseBtn.addEventListener('click', () => {
        editModal.style.display = 'none';
    });
    
    createCloseBtn.addEventListener('click', () => {
        createModal.style.display = 'none';
    });
    
    window.onclick = function(event) {
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
        if (event.target == createModal) {
            createModal.style.display = "none";
>>>>>>> a3d11b8 (sync: update open-source release)
        }
    }
});
</script>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
