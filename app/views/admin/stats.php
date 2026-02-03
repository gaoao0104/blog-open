<?php
$title = '站点统计';
require __DIR__ . '/../partials/admin-header.php';

// Auth Check
if (empty($_SESSION['user_id'])) {
    redirect_to('/admin/login');
}

$pdo = $GLOBALS['pdo'];

// Get Date Range (default to 7 days)
$range = isset($_GET['range']) && $_GET['range'] === '30' ? 30 : 7;
$intervalDays = $range - 1; // MySQL INTERVAL is 0-indexed logic relative to start date if inclusive, but usually we do >= DATE_SUB(CURDATE, INTERVAL X DAY)
// Actually, for "Last 7 Days" including today, we want 6 days ago.
// For "Last 30 Days", we want 29 days ago.

function formatSourceName(?string $host): string
{
    $host = strtolower(trim((string)$host));
    if ($host === '') {
        return '直接访问';
    }
    if (str_contains($host, 'google.')) {
        return 'Google 搜索';
    }
    if (str_contains($host, 'bing.com')) {
        return 'Bing 搜索';
    }
    if (str_contains($host, 'baidu.com')) {
        return '百度搜索';
    }
    if (str_contains($host, 'sogou.com')) {
        return '搜狗搜索';
    }
    if (str_contains($host, 'sm.cn')) {
        return '神马搜索';
    }
    if (str_contains($host, 'yahoo.')) {
        return 'Yahoo';
    }
    if (str_contains($host, 'duckduckgo.com')) {
        return 'DuckDuckGo';
    }
    if (str_contains($host, 'yandex.')) {
        return 'Yandex';
    }
    return $host;
}

try {
    // -------------------------------------------------------------------------
    // STATIC OVERVIEW STATS (Always Today / 7 Days / 30 Days)
    // -------------------------------------------------------------------------

    // 1. Today PV
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_analytics WHERE visit_date = CURDATE()");
    $todayPv = $stmt->fetchColumn();

    // 2. Today UV
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM site_analytics WHERE visit_date = CURDATE()");
    $todayUv = $stmt->fetchColumn();

    // 3. 7 Days PV
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_analytics WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
    $weekPv = $stmt->fetchColumn();

    // 4. 7 Days UV
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM site_analytics WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
    $weekUv = $stmt->fetchColumn();

    // 5. 30 Days PV
    $stmt = $pdo->query("SELECT COUNT(*) FROM site_analytics WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)");
    $monthPv = $stmt->fetchColumn();

    // 6. 30 Days UV
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM site_analytics WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)");
    $monthUv = $stmt->fetchColumn();

    // -------------------------------------------------------------------------
    // DYNAMIC RANGE STATS (Chart & Tables)
    // -------------------------------------------------------------------------

    // 7. Trend (Graph Data) based on $range
    $stmt = $pdo->prepare("
        SELECT 
            visit_date, 
            COUNT(*) AS pv,
            COUNT(DISTINCT ip_address) AS uv
        FROM site_analytics 
        WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL :interval DAY) 
        GROUP BY visit_date 
        ORDER BY visit_date ASC
    ");
    $stmt->execute(['interval' => $intervalDays]);
    $trendData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. Top 10 Pages based on $range
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug,
            COUNT(sa.id) AS pv,
            COUNT(DISTINCT sa.ip_address) AS uv
        FROM site_analytics sa
        JOIN posts p ON p.id = sa.post_id
        WHERE sa.visit_date >= DATE_SUB(CURDATE(), INTERVAL :interval DAY)
        AND sa.post_id <> 0
        GROUP BY sa.post_id, p.title, p.slug
        ORDER BY pv DESC
        LIMIT 10
    ");
    $stmt->execute(['interval' => $intervalDays]);
    $topPages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 9. Traffic Sources based on $range
    $stmt = $pdo->prepare("
        SELECT IFNULL(referrer_host, '') AS referrer_host,
            COUNT(*) AS pv,
            COUNT(DISTINCT ip_address) AS uv
        FROM site_analytics
        WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL :interval DAY)
        GROUP BY referrer_host
        ORDER BY pv DESC
        LIMIT 10
    ");
    $stmt->execute(['interval' => $intervalDays]);
    $sourceRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fill missing dates with 0
    $dates = [];
    $pvs = [];
    $uvs = []; 
    $current = new DateTime("-{$intervalDays} days");
    $end = new DateTime('now');
    
    // Create maps for easier lookup
    $trendMapPv = array_column($trendData, 'pv', 'visit_date');
    $trendMapUv = array_column($trendData, 'uv', 'visit_date');

    while ($current <= $end) {
        $dateStr = $current->format('Y-m-d');
        $dates[] = $current->format('m-d');
        $pvs[] = $trendMapPv[$dateStr] ?? 0;
        $uvs[] = $trendMapUv[$dateStr] ?? 0;
        $current->modify('+1 day');
    }

} catch (\PDOException $e) {
    // Fallback
    $todayPv = 0; $todayUv = 0;
    $weekPv = 0; $weekUv = 0;
    $monthPv = 0; $monthUv = 0;
    $dates = []; $pvs = []; $uvs = [];
    $topPages = [];
    $sourceRows = [];
}
?>

<div class="stats-container-enhanced">
    <!-- Header Section -->
    <div class="stats-header">
        <h1>站点统计</h1>
        <p>实时监控站点访问数据与趋势分析</p>
    </div>

    <!-- Overview Cards (Static Summary) -->
    <div class="stats-overview">
        <!-- Today -->
        <div class="stat-group">
            <div class="group-label">
                <span class="dot today"></span> 今日数据
            </div>
            <div class="stat-cards-row">
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper blue">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="stat-icon">
                            <path d="M15 10L11 14L17 20L21 4L3 11L7 13L9 19L12 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">今日 PV</span>
                        <div class="stat-number"><?= number_format($todayPv) ?></div>
                    </div>
                </div>
                <div class="stat-card-modern">
                    <div class="stat-icon-wrapper purple">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="stat-icon">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">今日 UV</span>
                        <div class="stat-number"><?= number_format($todayUv) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Week -->
        <div class="stat-group">
            <div class="group-label">
                <span class="dot week"></span>近7天数据
            </div>
            <div class="stat-cards-row">
                <div class="stat-card-modern">
                    <div class="stat-content">
                        <span class="stat-label">近7天 PV</span>
                        <div class="stat-number small"><?= number_format($weekPv) ?></div>
                    </div>
                </div>
                <div class="stat-card-modern">
                    <div class="stat-content">
                        <span class="stat-label">近7天 UV</span>
                        <div class="stat-number small"><?= number_format($weekUv) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month -->
        <div class="stat-group">
            <div class="group-label">
                <span class="dot month"></span>近30天数据
            </div>
            <div class="stat-cards-row">
                <div class="stat-card-modern">
                    <div class="stat-content">
                        <span class="stat-label">近30天 PV</span>
                        <div class="stat-number small"><?= number_format($monthPv) ?></div>
                    </div>
                </div>
                <div class="stat-card-modern">
                    <div class="stat-content">
                        <span class="stat-label">近30天 UV</span>
                        <div class="stat-number small"><?= number_format($monthUv) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section with Controls -->
    <div class="chart-section-modern">
        <div class="section-header">
            <div class="header-left">
                <h3>流量趋势 (近<?= $range ?>天)</h3>
            </div>
            <div class="header-right">
                <div class="range-selector">
                    <a href="?range=7" class="range-btn <?= $range === 7 ? 'active' : '' ?>">7天</a>
                    <a href="?range=30" class="range-btn <?= $range === 30 ? 'active' : '' ?>">30天</a>
                </div>
                <div class="legend-custom">
                    <span class="legend-item"><span class="color-dot ipv"></span>PV</span>
                    <span class="legend-item"><span class="color-dot iuv"></span>UV</span>
                </div>
            </div>
        </div>
        <div class="chart-canvas-wrapper">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <div class="data-grids">
        <!-- Top Pages Table -->
        <div class="data-card">
            <div class="section-header">
                <h3>热门文章 (TOP 10)</h3>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>标题</th>
                            <th width="120" class="text-right">PV</th>
                            <th width="120" class="text-right">UV</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topPages)): ?>
                            <tr><td colspan="3" class="empty-state">暂无数据</td></tr>
                        <?php else: ?>
                            <?php foreach ($topPages as $idx => $page): ?>
                                <tr>
                                    <td>
                                        <div class="page-title-cell">
                                            <span class="rank-badge rank-<?= $idx + 1 ?>"><?= $idx + 1 ?></span>
                                            <a href="/post/<?= e($page['slug']) ?>" target="_blank" class="page-link">
                                                <?= e($page['title']) ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-right font-mono"><?= number_format($page['pv']) ?></td>
                                    <td class="text-right font-mono"><?= number_format($page['uv']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Traffic Sources Table -->
        <div class="data-card">
            <div class="section-header">
                <h3>流量来源 (TOP 10)</h3>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>来源</th>
                            <th width="120" class="text-right">PV</th>
                            <th width="120" class="text-right">UV</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sourceRows)): ?>
                            <tr><td colspan="3" class="empty-state">暂无数据</td></tr>
                        <?php else: ?>
                            <?php foreach ($sourceRows as $idx => $source): 
                                $sourceName = formatSourceName($source['referrer_host'] ?? '');
                                $maxPv = $sourceRows[0]['pv'] ?? 1; // For percentage bar
                                $percent = ($source['pv'] / $maxPv) * 100;
                            ?>
                                <tr>
                                    <td>
                                        <div class="source-cell">
                                            <span><?= e($sourceName) ?></span>
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: <?= $percent ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right font-mono"><?= number_format($source['pv'] ?? 0) ?></td>
                                    <td class="text-right font-mono"><?= number_format($source['uv'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
<<<<<<< HEAD
=======

>>>>>>> a3d11b8 (sync: update open-source release)
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    
    // Modern gradients
    const pvGradient = ctx.createLinearGradient(0, 0, 0, 400);
    pvGradient.addColorStop(0, 'rgba(0, 122, 255, 0.2)');
    pvGradient.addColorStop(1, 'rgba(0, 122, 255, 0.0)');

    const uvGradient = ctx.createLinearGradient(0, 0, 0, 400);
    uvGradient.addColorStop(0, 'rgba(175, 82, 222, 0.2)');
    uvGradient.addColorStop(1, 'rgba(175, 82, 222, 0.0)');

    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.03)';
    const textColor = isDark ? '#98989d' : '#86868b';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [
                {
                    label: '访问量 (PV)',
                    data: <?= json_encode($pvs) ?>,
                    borderColor: '#007aff',
                    backgroundColor: pvGradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#007aff'
                },
                {
                    label: '访客 (UV)',
                    data: <?= json_encode($uvs) ?>,
                    borderColor: '#af52de',
                    backgroundColor: uvGradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#af52de'
                }
            ]
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
                    backgroundColor: isDark ? 'rgba(28, 28, 30, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                    titleColor: isDark ? '#fff' : '#000',
                    bodyColor: isDark ? '#fff' : '#000',
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    titleFont: { size: 13, weight: 600 },
                    bodyFont: { size: 13 },
                    displayColors: true,
                    boxWidth: 8,
                    usePointStyle: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        color: textColor,
                        font: { family: "SF Mono", size: 11 }
                    },
                    border: { display: false }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor,
                        maxTicksLimit: 10,
                        maxRotation: 0,
                        autoSkip: true
                    },
                    border: { display: false }
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
                location.reload(); 
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
});
</script>

<style>
/* Base Layout & Typography */
.stats-container-enhanced {
    max-width: 1200px;
    margin: 0 auto;
    padding-bottom: 40px;
}
.stats-header {
    margin-bottom: 32px;
}
.stats-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: var(--ink);
    letter-spacing: -0.02em;
}
.stats-header p {
    color: var(--ink-secondary);
    font-size: 0.95rem;
    margin: 0;
}

/* Stat Cards Overview */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}
.stat-group {
    background: var(--glass-surface);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(var(--glass-blur));
    border-radius: var(--radius-lg);
    padding: 20px;
}
.group-label {
    font-size: 0.8rem;
    color: var(--ink-secondary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dot { width: 6px; height: 6px; border-radius: 50%; opacity: 0.8; }
.dot.today { background: #007aff; }
.dot.week { background: #34c759; }
.dot.month { background: #ff9500; }

.stat-cards-row {
    display: flex;
    gap: 20px;
}
.stat-card-modern {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
}
.stat-icon-wrapper {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-icon-wrapper.blue {
    background: rgba(0, 122, 255, 0.1);
    color: #007aff;
}
.stat-icon-wrapper.purple {
    background: rgba(175, 82, 222, 0.1);
    color: #af52de;
}
.stat-content {
    display: flex;
    flex-direction: column;
}
.stat-label {
    font-size: 0.85rem;
    color: var(--ink-secondary);
    margin-bottom: 2px;
}
.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
    font-family: -apple-system, BlinkMacSystemFont, "SF Mono", monospace;
}
.stat-number.small {
    font-size: 1.25rem;
}

/* Chart Section */
.chart-section-modern {
    background: var(--glass-surface);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(var(--glass-blur));
    border-radius: var(--radius-lg);
    padding: 24px;
    margin-bottom: 32px;
}
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.section-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--ink);
}
.header-right {
    display: flex;
    align-items: center;
    gap: 24px;
}

/* Range Selector Buttons */
.range-selector {
    display: flex;
    background: var(--glass-border); /* Subtle bg for segment bg */
    border-radius: 8px;
    padding: 2px;
    gap: 2px;
}
.range-btn {
    text-decoration: none;
    font-size: 0.8rem;
    padding: 4px 12px;
    border-radius: 6px;
    color: var(--ink-secondary);
    font-weight: 500;
    transition: all 0.2s;
}
.range-btn:hover {
    color: var(--ink);
    background: rgba(0,0,0,0.05);
}
.range-btn.active {
    background: var(--card-bg); /* Use card-bg or white/black depending on theme logic usually handled by CSS vars */
    color: var(--ink);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
/* For dark mode active state specifically if vars are tricky */
:root[data-theme="dark"] .range-btn.active {
    background: #333;
}

.legend-custom {
    display: flex;
    gap: 16px;
    font-size: 0.85rem;
    color: var(--ink-secondary);
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.color-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.color-dot.ipv { background: #007aff; }
.color-dot.iuv { background: #af52de; }

.chart-canvas-wrapper {
    position: relative;
    height: 400px; /* Explicit height to prevent cut-off */
    min-height: 400px;
    width: 100%;
}

/* Data Tables */
.data-grids {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 32px;
}
.data-card {
    background: var(--glass-surface);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(var(--glass-blur));
    border-radius: var(--radius-lg);
    padding: 24px;
    display: flex;
    flex-direction: column;
}
.table-responsive {
    overflow-x: auto;
}
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.modern-table th {
    text-align: left;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: var(--ink-secondary);
    font-weight: 600;
    padding: 12px 8px;
    border-bottom: 1px solid var(--glass-border);
}
.modern-table td {
    padding: 14px 8px;
    border-bottom: 1px solid var(--glass-border);
    color: var(--ink);
    font-size: 0.95rem;
    vertical-align: middle;
}
.modern-table tr:last-child td {
    border-bottom: none;
}
.modern-table .text-right { text-align: right; }
.modern-table .font-mono { font-family: "SF Mono", monospace; letter-spacing: -0.5px; }
.empty-state { text-align: center; color: var(--ink-secondary); padding: 32px !important; }

/* Components within table */
.page-title-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.rank-badge {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    background: rgba(0,0,0,0.05);
    color: var(--ink-secondary);
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}
:root[data-theme="dark"] .rank-badge { background: rgba(255,255,255,0.1); }
.rank-1 { background: #FFD700 !important; color: #fff !important; }
.rank-2 { background: #C0C0C0 !important; color: #fff !important; }
.rank-3 { background: #cd7f32 !important; color: #fff !important; }

.page-link {
    color: var(--ink);
    text-decoration: none;
    font-weight: 500;
    display: block;
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.2s;
}
.page-link:hover { color: #007aff; }

.source-cell {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.progress-bar-bg {
    width: 100px;
    height: 4px;
    background: rgba(0,0,0,0.05);
    border-radius: 2px;
    overflow: hidden;
}
:root[data-theme="dark"] .progress-bar-bg { background: rgba(255,255,255,0.1); }
.progress-bar-fill {
    height: 100%;
    background: #34c759;
    border-radius: 2px;
}
</style>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
