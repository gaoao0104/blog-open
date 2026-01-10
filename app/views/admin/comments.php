<?php
$title = '评论管理';
require __DIR__ . '/../partials/admin-header.php';
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">评论列表</h2>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>文章</th>
                    <th>作者</th>
                    <th>内容</th>
                    <th>状态</th>
                    <th class="text-right">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 500; font-size: 0.9rem;"><?= e($comment['post_title']) ?></div>
                        </td>
                        <td>
                            <div style="font-size: 0.9rem;"><?= e($comment['author_name']) ?></div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; color: var(--admin-text-secondary); max-width: 300px;"><?= e(snippet($comment['content'], 60)) ?></div>
                        </td>
                        <td>
                            <?php if ($comment['status'] === 'approved'): ?>
                                <span class="badge badge-success">通过</span>
                            <?php else: ?>
                                <span class="badge badge-gray"><?= e($comment['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($comment['status'] !== 'approved'): ?>
                                <a href="/admin/comments/approve?id=<?= e((string)$comment['id']) ?>" class="btn btn-primary btn-sm">通过</a>
                            <?php endif; ?>
                            <a href="/admin/comments/delete?id=<?= e((string)$comment['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定删除?');">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
