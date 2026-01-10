<?php
$title = ($category['name'] ?? '');
$meta_description = $title . ' 下的最新文章';
$canonical_url = url_for($config, '/category/' . $category['slug']);
$json_ld = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
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
                'name' => $title,
                'item' => $canonical_url,
            ],
        ],
    ],
];
require __DIR__ . '/partials/header.php';
?>
<h1><?= e($title) ?></h1>
<?php if (empty($posts)): ?>
    <p>暂无文章。</p>
<?php else: ?>
    <div class="posts">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <div>
                    <h2><a href="/post/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
                    <p class="meta">
                        <span><?= e(date('Y-m-d', strtotime($post['published_at'] ?? $post['created_at']))) ?></span>
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
                    <p><?= e($post['excerpt'] ?: snippet($post['content_md'], 120) . '...') ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
