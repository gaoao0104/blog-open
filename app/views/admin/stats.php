<?php
$title = '站点统计';
require __DIR__ . '/../partials/admin-header.php';

// Auth Check
if (empty($_SESSION['user_id'])) {
    redirect_to('/admin/login');
}

$pdo = $GLOBALS['pdo'];

try {
    // 1. Today PV
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_analytics WHERE visit_date = CURDATE()");
    $todayPv = $stmt->fetchColumn();

    // 2. Today UV
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM site_analytics WHERE visit_date = CURDATE()");
    $todayUv = $stmt->fetchColumn();

    // 3. 7 Days PV
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_analytics WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
    $weekPv = $stmt->fetchColumn();

    // 4. 30 Days PV
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_analytics WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)");
    $monthPv = $stmt->fetchColumn();

    // 5. 30 Days Trend (Graph Data)
    $stmt = $pdo->query("
        SELECT visit_date, COUNT(*) AS pv 
        FROM site_analytics 
        WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) 
        GROUP BY visit_date 
        ORDER BY visit_date ASC
    ");
    $trendData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Top 10 Pages (30 Days)
    $stmt = $pdo->query("
        SELECT p.id, p.title, p.slug,
            COUNT(sa.id) AS pv,
            COUNT(DISTINCT sa.ip_address) AS uv
        FROM site_analytics sa
        JOIN posts p ON p.id = sa.post_id
        WHERE sa.visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
        AND sa.post_id <> 0
        GROUP BY sa.post_id, p.title, p.slug
        ORDER BY pv DESC
        LIMIT 10
    ");
    $topPages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fill missing dates with 0
    $dates = [];
    $pvs = [];
    $current = new DateTime('-29 days');
    $end = new DateTime('now');
    $trendMap = array_column($trendData, 'pv', 'visit_date');

    while ($current <= $end) {
        $dateStr = $current->format('Y-m-d');
        $dates[] = $current->format('m-d');
        $pvs[] = $trendMap[$dateStr] ?? 0;
        $current->modify('+1 day');
    }

} catch (\PDOException $e) {
    // If table doesn't exist or other DB error, use defaults to show page
    $todayPv = 0;
    $todayUv = 0;
    $weekPv = 0;
    $monthPv = 0;
    $dates = [];
    $pvs = [];
    $topPages = [];
}

?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>今日 PV</h3>
        <div class="stat-value"><?= number_format($todayPv) ?></div>
    </div>
    <div class="stat-card">
        <h3>今日 UV</h3>
        <div class="stat-value"><?= number_format($todayUv) ?></div>
    </div>
    <div class="stat-card">
        <h3>近7天 PV</h3>
        <div class="stat-value"><?= number_format($weekPv) ?></div>
    </div>
    <div class="stat-card">
        <h3>近30天 PV</h3>
        <div class="stat-value"><?= number_format($monthPv) ?></div>
    </div>
</div>

<div class="chart-container" style="margin-top: 32px; background: var(--glass-surface); padding: 24px; border-radius: var(--radius-md); border: 1px solid var(--glass-border);">
    <canvas id="trendChart" style="width: 100%; height: 400px;"></canvas>
</div>

<div class="card-manager" style="margin-top: 32px;">
    <h3>热门文章 (近30天)</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>标题</th>
                <th width="100">PV</th>
                <th width="100">UV</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($topPages)): ?>
                <tr><td colspan="3" style="text-align:center;color:var(--ink-secondary);">暂无数据</td></tr>
            <?php else: ?>
                <?php foreach ($topPages as $page): ?>
                    <tr>
                        <td>
                            <a href="/post/<?= e($page['slug']) ?>" target="_blank" style="color:var(--ink);">
                                <?= e($page['title']) ?>
                            </a>
                        </td>
                        <td><?= number_format($page['pv']) ?></td>
                        <td><?= number_format($page['uv']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
    const textColor = isDark ? '#a1a1a6' : '#86868b';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: '访问量 (PV)',
                data: <?= json_encode($pvs) ?>,
                borderColor: '#007aff',
                backgroundColor: 'rgba(0, 122, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: isDark ? 'rgba(28, 28, 30, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                    titleColor: isDark ? '#fff' : '#000',
                    bodyColor: isDark ? '#fff' : '#000',
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Handle Theme Switch
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === "data-theme") {
                location.reload(); // Simple reload to re-render chart with correct colors
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
});
</script>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
}
.stat-card {
    background: var(--glass-surface);
    backdrop-filter: blur(var(--glass-blur));
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-md);
    padding: 24px;
    box-shadow: var(--shadow-sm);
}
.stat-card h3 {
    margin: 0 0 8px 0;
    font-size: 0.9rem;
    color: var(--ink-secondary);
    font-weight: 500;
}
.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--ink);
    font-family: -apple-system, BlinkMacSystemFont, "SF Mono", monospace;
}
</style>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
