<?php
$title = '标签管理';
require __DIR__ . '/../partials/admin-header.php';
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">标签列表</h2>
        <form class="flex gap-2" method="post" action="/admin/tags/create" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <input type="text" name="name" placeholder="标签名称" required class="form-control" style="width: 200px;">
            <button type="submit" class="btn btn-primary">添加</button>
        </form>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>名称</th>
                    <th>Slug</th>
                    <th class="text-right">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td>
                            <span class="badge badge-gray" style="font-size: 0.9rem;"><?= e($tag['name']) ?></span>
                        </td>
                        <td><?= e($tag['slug']) ?></td>
                        <td class="text-right">
                            <a href="/admin/tags/delete?id=<?= e((string)$tag['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定删除?');">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
