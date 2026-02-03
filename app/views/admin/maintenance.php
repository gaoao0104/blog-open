<?php
$title = '系统维护';
require __DIR__ . '/../partials/admin-header.php';
$stats = $stats ?? [];
$posts = $posts ?? [];
$cleanupLogs = $cleanup_logs ?? [];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">数据概览</h2>
    </div>
    <div style="display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div style="padding:16px; border-radius:10px; border:1px solid var(--admin-border); background: var(--admin-bg);">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">总文章</div>
            <div style="font-size: 1.5rem; font-weight: 600;"><?= e((string)($stats['total_posts'] ?? 0)) ?></div>
        </div>
        <div style="padding:16px; border-radius:10px; border:1px solid var(--admin-border); background: var(--admin-bg);">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">已发布</div>
            <div style="font-size: 1.5rem; font-weight: 600;"><?= e((string)($stats['published_posts'] ?? 0)) ?></div>
        </div>
        <div style="padding:16px; border-radius:10px; border:1px solid var(--admin-border); background: var(--admin-bg);">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">推荐文章</div>
            <div style="font-size: 1.5rem; font-weight: 600;"><?= e((string)($stats['featured_posts'] ?? 0)) ?></div>
        </div>
        <div style="padding:16px; border-radius:10px; border:1px solid var(--admin-border); background: var(--admin-bg);">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">未推荐文章</div>
            <div style="font-size: 1.5rem; font-weight: 600;"><?= e((string)($stats['non_featured_posts'] ?? 0)) ?></div>
        </div>
        <div style="padding:16px; border-radius:10px; border:1px solid var(--admin-border); background: var(--admin-bg);">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">推荐卡片</div>
            <?php if (!empty($stats['has_featured_cards'])): ?>
                <div style="font-size: 1.5rem; font-weight: 600;"><?= e((string)($stats['featured_cards'] ?? 0)) ?></div>
            <?php else: ?>
                <div style="font-size: 1.1rem; font-weight: 600; color: var(--admin-text-secondary);">未创建</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">维护操作</h2>
    </div>
    <p style="color: var(--admin-text-secondary); font-size: 0.9rem; margin-bottom: 12px;">
        将旧的“推荐文章”同步到新的推荐卡片表中，已迁移的文章会被自动跳过。
    </p>
    <form method="post" action="/admin/maintenance/migrate" onsubmit="return confirm('确定执行迁移吗？已迁移的记录会被跳过。');">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">
        <button type="submit" class="btn btn-primary">迁移推荐文章到推荐卡片</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">R2 清理记录</h2>
    </div>
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>时间</th>
                    <th>环境</th>
                    <th>模式</th>
                    <th>前缀</th>
                    <th>候选/删除</th>
                    <th>失败</th>
                    <th>状态</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$cleanupLogs): ?>
                    <tr>
                        <td colspan="7" style="color: var(--admin-text-secondary);">暂无清理记录</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cleanupLogs as $log): ?>
                        <tr>
                            <td><?= e((string)($log['created_at'] ?? '')) ?></td>
                            <td><?= e((string)($log['env'] ?? '')) ?></td>
                            <td><?= e((string)($log['mode'] ?? '')) ?></td>
                            <td><?= e((string)($log['prefix'] ?? '')) ?></td>
                            <td><?= e((string)($log['candidate'] ?? 0)) ?>/<?= e((string)($log['deleted'] ?? 0)) ?></td>
                            <td><?= e((string)($log['failed'] ?? 0)) ?></td>
                            <td><?= e((string)($log['status'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">最近文章预览</h2>
    </div>
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>标题</th>
                    <th>状态</th>
                    <th>推荐</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$posts): ?>
                    <tr>
                        <td colspan="4" style="color: var(--admin-text-secondary);">暂无数据</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= e((string)$post['id']) ?></td>
                            <td><?= e($post['title']) ?></td>
                            <td><?= e($post['status']) ?></td>
                            <td><?= !empty($post['is_featured']) ? '✅' : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
