<?php

declare(strict_types=1);

$config = require __DIR__ . '/../app/config/config.php';

require __DIR__ . '/../app/lib/Db.php';
require __DIR__ . '/../app/lib/Helpers.php';
require __DIR__ . '/../app/lib/Auth.php';
require __DIR__ . '/../app/lib/Settings.php';
require __DIR__ . '/../app/lib/View.php';

session_name($config['session_name']);
session_start();

$pdo = Db::pdo($config);
$GLOBALS['pdo'] = $pdo;
$GLOBALS['settings'] = Settings::all($pdo);

Auth::requireLogin();

$today = date('Y-m-d');
$start7 = date('Y-m-d', strtotime('-6 days'));
$start30 = date('Y-m-d', strtotime('-29 days'));

$stmt = $pdo->prepare('SELECT COUNT(*) FROM site_analytics WHERE visit_date = ?');
$stmt->execute([$today]);
$todayPv = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(DISTINCT ip_address) FROM site_analytics WHERE visit_date = ?');
$stmt->execute([$today]);
$todayUv = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM site_analytics WHERE visit_date >= ?');
$stmt->execute([$start7]);
$pv7 = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM site_analytics WHERE visit_date >= ?');
$stmt->execute([$start30]);
$pv30 = (int)$stmt->fetchColumn();

$topStmt = $pdo->prepare('SELECT p.id, p.title, COUNT(sa.id) AS pv, COUNT(DISTINCT sa.ip_address) AS uv FROM site_analytics sa INNER JOIN posts p ON p.id = sa.post_id WHERE sa.visit_date >= ? AND sa.post_id <> 0 GROUP BY sa.post_id, p.title ORDER BY pv DESC LIMIT 10');
$topStmt->execute([$start30]);
$topPosts = $topStmt->fetchAll();

$trendStmt = $pdo->prepare('SELECT visit_date, COUNT(*) AS pv FROM site_analytics WHERE visit_date >= ? GROUP BY visit_date ORDER BY visit_date ASC');
$trendStmt->execute([$start30]);
$trendRows = $trendStmt->fetchAll();

$pvMap = [];
foreach ($trendRows as $row) {
    $pvMap[$row['visit_date']] = (int)$row['pv'];
}

$labels = [];
$data = [];
$cursor = new DateTime($start30);
$end = new DateTime($today);
while ($cursor <= $end) {
    $label = $cursor->format('Y-m-d');
    $labels[] = $label;
    $data[] = $pvMap[$label] ?? 0;
    $cursor->modify('+1 day');
}

$chartLabels = json_encode($labels, JSON_UNESCAPED_UNICODE);
$chartData = json_encode($data, JSON_UNESCAPED_UNICODE);

$title = '流量统计';
require __DIR__ . '/../app/views/partials/admin-header.php';
?>

<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">访问概览</h2>
    </div>
    <div class="table-container" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
        <div class="card" style="padding: 16px;">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">今日 PV / UV</div>
            <div style="font-size: 1.6rem; font-weight: 600; margin-top: 4px;"><?= e((string)$todayPv) ?> / <?= e((string)$todayUv) ?></div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">近 7 天总 PV</div>
            <div style="font-size: 1.6rem; font-weight: 600; margin-top: 4px;"><?= e((string)$pv7) ?></div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="color: var(--admin-text-secondary); font-size: 0.85rem;">近 30 天总 PV</div>
            <div style="font-size: 1.6rem; font-weight: 600; margin-top: 4px;"><?= e((string)$pv30) ?></div>
        </div>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">近 30 天热门文章 Top 10</h2>
    </div>
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>文章 ID</th>
                    <th>标题</th>
                    <th>PV</th>
                    <th>UV</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topPosts)): ?>
                    <tr>
                        <td colspan="4">暂无数据</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topPosts as $post): ?>
                        <tr>
                            <td><?= e((string)$post['id']) ?></td>
                            <td><?= e($post['title']) ?></td>
                            <td><?= e((string)$post['pv']) ?></td>
                            <td><?= e((string)$post['uv']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">近 30 天流量趋势</h2>
    </div>
    <div style="padding: 16px;">
        <canvas id="pvChart" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels = <?= $chartLabels ?>;
    const data = <?= $chartData ?>;

    const ctx = document.getElementById('pvChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'PV',
                data,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.15)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
</script>

<?php require __DIR__ . '/../app/views/partials/admin-footer.php'; ?>
