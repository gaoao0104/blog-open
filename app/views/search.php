<?php
$title = $query === '' ? '搜索' : '搜索：' . $query;
$meta_description = '站内搜索文章与标签';
$meta_robots = 'noindex,follow';
$base_url = base_url($config);
if ($query === '') {
    $canonical_url = $base_url . '/search';
} else {
    $canonical_url = $base_url . '/search?q=' . urlencode($query);
    if (!empty($page) && $page > 1) {
        $canonical_url .= '&page=' . (int)$page;
    }
}
$json_ld = [
    '@context' => 'https://schema.org',
    '@type' => 'SearchResultsPage',
    'name' => $title,
    'url' => $canonical_url,
];
$json_ld = [
    $json_ld,
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => '首页',
                'item' => url_for($config, '/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => '搜索',
                'item' => $base_url . '/search',
            ],
        ],
    ],
];
require __DIR__ . '/partials/header.php';
?>
<section class="search-results">
    <h1><?= e($title) ?></h1>
    
    <div class="search-container" style="margin: 20px 0 30px;">
        <form action="/search" method="GET" style="display: flex; gap: 10px; max-width: 500px;">
            <input type="text" name="q" value="<?= e($query) ?>" placeholder="输入关键词搜索..." required
                   style="flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; outline: none;">
            <button type="submit" 
                    style="padding: 10px 20px; background-color: #007aff; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 500;">
                搜索
            </button>
        </form>
    </div>

    <?php if ($query === ''): ?>
        <p>请输入关键词进行搜索。</p>
    <?php elseif (empty($posts)): ?>
        <p>未找到相关内容。</p>
    <?php else: ?>
        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div>
                        <h2><a href="/post/<?= e($post['slug']) ?>"><?= highlight($post['title'], $query) ?></a></h2>
                        <p class="meta">
                            <span><?= e(date('Y-m-d', strtotime($post['published_at'] ?? $post['created_at']))) ?></span>
                            <?php if (!empty($post['category_name'])): ?>
                                <span>分类 <a href="/category/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a></span>
                            <?php endif; ?>
                            <span class="author">
                                <?php if (!empty($post['author_avatar'])): ?>
                                    <img class="avatar" src="<?= e($post['author_avatar']) ?>" alt="<?= e($post['author_name'] ?? '管理员') ?>">
                                <?php else: ?>
                                    <span class="avatar placeholder"><?= e(snippet($post['author_name'] ?? '管理员', 1)) ?></span>
                                <?php endif; ?>
                                <span><?= e($post['author_name'] ?? '管理员') ?></span>
                                <?php if (!empty($post['author_verified'])): ?>
                                    <?php if (!empty($post['author_badge'])): ?>
                                        <img class="verified-badge badge-image" src="<?= e($post['author_badge']) ?>" alt="认证作者">
                                    <?php else: ?>
                                        <span class="verified-badge" title="认证作者">V</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        </p>
                        <p><?= highlight($post['excerpt'] ?: snippet($post['content_md'], 120) . '...', $query) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
            $totalPages = (int)ceil($total / $per_page);
        ?>
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="/search?q=<?= e($query) ?>&page=<?= e((string)($page - 1)) ?>">上一页</a>
                <?php endif; ?>
                <span>第 <?= e((string)$page) ?> / <?= e((string)$totalPages) ?> 页</span>
                <?php if ($page < $totalPages): ?>
                    <a href="/search?q=<?= e($query) ?>&page=<?= e((string)($page + 1)) ?>">下一页</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
