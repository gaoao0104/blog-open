<?php
$title = $post['title'] ?? '文章';
$author_name = $post['author_name'] ?? '管理员';
$published_at = $post['published_at'] ?? $post['created_at'];
$updated_at = $post['updated_at'] ?? $published_at;
$meta_description = $post['excerpt'] ?: snippet(markdown_plaintext($post['content_md']), 50);
$canonical_url = url_for($config, '/post/' . $post['slug']);
$track_post_id = (int)($post['id'] ?? 0);
$og_type = 'article';
$og_image = !empty($post['featured_image']) ? url_for($config, $post['featured_image']) : url_for($config, '/og/image/' . $post['id']);
$article_meta = [
    'published_time' => $published_at ? date('c', strtotime($published_at)) : null,
    'modified_time' => $updated_at ? date('c', strtotime($updated_at)) : null,
    'author' => $author_name,
];
if (!empty($post['category_name'])) {
    $article_meta['section'] = $post['category_name'];
}
if (!empty($tags)) {
    $article_meta['tags'] = array_column($tags, 'name');
}
$json_ld = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post['title'],
    'description' => $meta_description,
    'datePublished' => $article_meta['published_time'],
    'dateModified' => $article_meta['modified_time'] ?: $article_meta['published_time'],
    'author' => [
        '@type' => 'Person',
        'name' => $author_name,
    ],
    'mainEntityOfPage' => $canonical_url,
];
if (!empty($post['featured_image'])) {
    $json_ld['image'] = url_for($config, $post['featured_image']);
}
$breadcrumb_items = [
    [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => '首页',
        'item' => url_for($config, '/'),
    ],
];
$position = 2;
if (!empty($post['category_name'])) {
    $breadcrumb_items[] = [
        '@type' => 'ListItem',
        'position' => $position,
        'name' => $post['category_name'],
        'item' => url_for($config, '/category/' . $post['category_slug']),
    ];
    $position++;
}
$breadcrumb_items[] = [
    '@type' => 'ListItem',
    'position' => $position,
    'name' => $title,
    'item' => $canonical_url,
];
$json_ld = [
    $json_ld,
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumb_items,
    ],
];
require __DIR__ . '/partials/header.php';
?>
<div class="post-wrapper">
    <article class="post-detail">
        <h1><?= e($post['title']) ?></h1>
        <p class="meta">
            <?php
                $showUpdated = false;
                if ($published_at && $updated_at) {
                    $publishedTs = strtotime($published_at);
                    $updatedTs = strtotime($updated_at);
                    if ($publishedTs && $updatedTs) {
                        $showUpdated = abs($updatedTs - $publishedTs) > 24 * 60 * 60;
                    }
                }
            ?>
            <span>发布于：<?= e(date('Y-m-d', strtotime($published_at))) ?></span>
            <?php if ($showUpdated): ?>
                <span>最后更新于：<?= e(date('Y-m-d', strtotime($updated_at))) ?></span>
            <?php endif; ?>
            <?php if (!empty($post['category_name'])): ?>
                <span>分类 <a href="/category/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a></span>
            <?php endif; ?>
            <span class="author">
                <?php if (!empty($post['author_avatar'])): ?>
                    <img class="avatar" src="<?= e($post['author_avatar']) ?>" alt="<?= e($author_name) ?>">
                <?php else: ?>
                    <span class="avatar placeholder"><?= e(snippet($author_name, 1)) ?></span>
                <?php endif; ?>
                <span><?= e($author_name) ?></span>
                <?php if (!empty($post['author_verified'])): ?>
                    <?php if (!empty($post['author_badge'])): ?>
                        <img class="verified-badge badge-image" src="<?= e($post['author_badge']) ?>" alt="认证作者">
                    <?php else: ?>
                        <span class="verified-badge" title="认证作者">V</span>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
        </p>
        <?php if (!empty($post['featured_image'])): ?>
            <img class="featured" src="<?= e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>">
        <?php endif; ?>
        <div class="post-body">
            <?= Markdown::render($post['content_md']) ?>
        </div>
        <div class="share">
            <span>分享：</span>
            <button type="button" class="button ghost" id="share-copy">复制链接</button>
            <a class="button ghost" target="_blank" rel="noopener" href="https://service.weibo.com/share/share.php?url=<?= e(url_for($config, '/post/' . $post['slug'])) ?>&title=<?= e($post['title']) ?>">微博</a>
            <a class="button ghost" target="_blank" rel="noopener" href="https://connect.qq.com/widget/shareqq/index.html?url=<?= e(url_for($config, '/post/' . $post['slug'])) ?>&title=<?= e($post['title']) ?>">QQ</a>
        </div>
        
        <!-- Post Cards Section -->
        <div id="post-cards-container" class="post-cards-container" style="display: none;">
            <div class="post-cards-grid" id="post-cards-grid"></div>
        </div>

        <script>
            (function() {
                const slug = <?= json_encode($post['slug']) ?>;
                const container = document.getElementById('post-cards-container');
                const grid = document.getElementById('post-cards-grid');

                fetch(`/post/${encodeURIComponent(slug)}/cards`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.cards && data.cards.length > 0) {
                            container.style.display = 'block';
                            data.cards.forEach(card => {
                                const tag = card.link_url ? 'a' : 'div';
                                const cardEl = document.createElement(tag);
                                if (card.link_url) {
                                    cardEl.href = card.link_url;
                                    cardEl.target = '_blank';
                                }
                                cardEl.className = 'post-card-item';
                                
                                let imageHtml = '';
                                if (card.image_url) {
                                    imageHtml = `<div class="card-image"><img src="${card.image_url}" alt="${card.title}" loading="lazy"></div>`;
                                }

                                cardEl.innerHTML = `
                                    ${imageHtml}
                                    <div class="card-info">
                                        <div class="card-title">${card.title}</div>
                                        <div class="card-desc">${card.description || ''}</div>
                                    </div>
                                `;
                                grid.appendChild(cardEl);
                            });
                        }
                    })
                    .catch(e => console.error('Failed to load cards:', e));
            })();
        </script>

        <?php if (!empty($tags)): ?>
            <div class="tags">
                <?php foreach ($tags as $tag): ?>
                    <a href="/tag/<?= e($tag['slug']) ?>">#<?= e($tag['name']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>

    <aside class="post-toc-container">
        <div class="post-toc">
            <h3>目录</h3>
            <ul id="toc-list"></ul>
        </div>
    </aside>
</div>

<section class="comments">
    <h2>评论</h2>
    <?php if (empty($comments)): ?>
        <p>暂无评论。</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <div class="comment-meta">
                    <strong><?= e($comment['author_name']) ?></strong>
                    <span><?= e(date('Y-m-d', strtotime($comment['created_at']))) ?></span>
                </div>
                <p><?= e($comment['content']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form class="comment-form" action="/post/<?= e($post['slug']) ?>/comment" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <label>
            昵称
            <input type="text" name="author" required>
        </label>
        <label>
            邮箱
            <input type="email" name="email" required>
        </label>
        <label>
            评论内容
            <textarea name="content" rows="4" required></textarea>
        </label>
        <button type="submit">提交评论</button>
    </form>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script>
    const copyBtn = document.getElementById('share-copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            const url = '<?= e(url_for($config, '/post/' . $post['slug'])) ?>';
            try {
                await navigator.clipboard.writeText(url);
                copyBtn.textContent = '已复制';
                setTimeout(() => copyBtn.textContent = '复制链接', 1500);
            } catch (error) {
                window.prompt('复制链接', url);
            }
        });
    }
</script>
