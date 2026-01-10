<?php
$title = '仪表盘';
require __DIR__ . '/../partials/admin-header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">最近文章</h2>
        <a href="/admin/posts/new" class="btn btn-primary btn-sm">+ 新建文章</a>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>标题</th>
                    <th>分类</th>
                    <th>状态</th>
                    <th>推荐</th>
                    <th>更新时间</th>
                    <th class="text-right">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500;"><?= e($post['title']) ?></div>
                        </td>
                        <td>
                            <span class="badge badge-gray"><?= e($post['category_name'] ?? '未分类') ?></span>
                        </td>
                        <td>
                            <?php if ($post['status'] === 'published'): ?>
                                <span class="badge badge-success">已发布</span>
                            <?php else: ?>
                                <span class="badge badge-gray"><?= e($post['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= !empty($post['is_featured']) ? '✅' : '-' ?></td>
                        <td style="color: var(--admin-text-secondary); font-size: 0.85rem;">
                            <?= e(date('Y-m-d', strtotime($post['updated_at']))) ?>
                        </td>
                        <td class="text-right">
                            <a href="/admin/posts/edit?id=<?= e((string)$post['id']) ?>" class="btn btn-secondary btn-sm" style="margin-right: 4px;">编辑</a>
                            <a href="/admin/posts/delete?id=<?= e((string)$post['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定删除?');">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
