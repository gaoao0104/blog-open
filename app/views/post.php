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
        <?php
            $share_url = url_for($config, '/post/' . $post['slug']);
            $share_title = $post['title'] ?? '';
            $share_text = $meta_description;
            $share_image = !empty($post['featured_image']) ? url_for($config, $post['featured_image']) : '';
            $share_url_q = rawurlencode($share_url);
            $share_title_q = rawurlencode($share_title);
            $share_text_q = rawurlencode($share_text);
            $share_image_q = rawurlencode($share_image);
<<<<<<< HEAD
            $wechat_enabled = !empty($config['wechat_app_id']) && !empty($config['wechat_app_secret']);
        ?>
        <div class="share">
            <span>分享：</span>
            <button type="button" class="button ghost" id="share-system">系统分享</button>
            <button type="button" class="button ghost" id="share-copy">复制链接</button>
            <button type="button" class="button ghost" id="share-wechat">微信分享</button>
            <button type="button" class="button ghost" id="share-qq">QQ分享</button>
            <a class="button ghost" target="_blank" rel="noopener" href="https://service.weibo.com/share/share.php?url=<?= e($share_url_q) ?>&title=<?= e($share_title_q) ?>&pic=<?= e($share_image_q) ?>">微博</a>
            <a class="button ghost" target="_blank" rel="noopener" href="https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=<?= e($share_url_q) ?>&title=<?= e($share_title_q) ?>&summary=<?= e($share_text_q) ?>&pics=<?= e($share_image_q) ?>">QQ空间</a>
            <a class="button ghost" target="_blank" rel="noopener" href="https://t.me/share/url?url=<?= e($share_url_q) ?>&text=<?= e($share_title_q) ?>">Telegram</a>
=======
            $share_settings = $settings ?? [];
            $share_enabled = [
                'system' => ($share_settings['share_enable_system'] ?? '1') === '1',
                'copy' => ($share_settings['share_enable_copy'] ?? '1') === '1',
                'wechat' => ($share_settings['share_enable_wechat'] ?? '1') === '1',
                'qq' => ($share_settings['share_enable_qq'] ?? '1') === '1',
                'weibo' => ($share_settings['share_enable_weibo'] ?? '1') === '1',
                'qzone' => ($share_settings['share_enable_qzone'] ?? '1') === '1',
                'telegram' => ($share_settings['share_enable_telegram'] ?? '1') === '1',
            ];
            $share_icons = [
                'system' => $share_settings['share_icon_system'] ?? '',
                'copy' => $share_settings['share_icon_copy'] ?? '',
                'wechat' => $share_settings['share_icon_wechat'] ?? '',
                'qq' => $share_settings['share_icon_qq'] ?? '',
                'weibo' => $share_settings['share_icon_weibo'] ?? '',
                'qzone' => $share_settings['share_icon_qzone'] ?? '',
                'telegram' => $share_settings['share_icon_telegram'] ?? '',
            ];
            $wechat_configured = !empty($config['wechat_app_id']) && !empty($config['wechat_app_secret']);
            $wechat_enabled = $wechat_configured && $share_enabled['wechat'];
        ?>
        <div class="share share-icons">
            <span>分享：</span>
            <?php if ($share_enabled['system']): ?>
                <button type="button" class="share-icon" id="share-system" aria-label="系统分享">
                    <?php if (!empty($share_icons['system'])): ?>
                        <img src="<?= e($share_icons['system']) ?>" alt="系统分享">
                    <?php else: ?>
                        <span class="share-fallback">系统</span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
        <?php if ($share_enabled['copy']): ?>
            <button type="button" class="share-icon" id="share-copy" aria-label="复制链接">
                <?php if (!empty($share_icons['copy'])): ?>
                    <img src="<?= e($share_icons['copy']) ?>" alt="复制链接">
                <?php else: ?>
                    <span class="share-fallback">复制</span>
                <?php endif; ?>
                <span class="share-feedback" aria-hidden="true">已复制</span>
            </button>
        <?php endif; ?>
            <?php if ($share_enabled['wechat']): ?>
                <button type="button" class="share-icon" id="share-wechat" aria-label="微信分享">
                    <?php if (!empty($share_icons['wechat'])): ?>
                        <img src="<?= e($share_icons['wechat']) ?>" alt="微信分享">
                    <?php else: ?>
                        <span class="share-fallback">微信</span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
            <?php if ($share_enabled['qq']): ?>
                <button type="button" class="share-icon" id="share-qq" aria-label="QQ分享">
                    <?php if (!empty($share_icons['qq'])): ?>
                        <img src="<?= e($share_icons['qq']) ?>" alt="QQ分享">
                    <?php else: ?>
                        <span class="share-fallback">QQ</span>
                    <?php endif; ?>
                </button>
            <?php endif; ?>
            <?php if ($share_enabled['weibo']): ?>
                <a class="share-icon share-link" target="_blank" rel="noopener" aria-label="微博分享" href="https://service.weibo.com/share/share.php?url=<?= e($share_url_q) ?>&title=<?= e($share_title_q) ?>&pic=<?= e($share_image_q) ?>">
                    <?php if (!empty($share_icons['weibo'])): ?>
                        <img src="<?= e($share_icons['weibo']) ?>" alt="微博分享">
                    <?php else: ?>
                        <span class="share-fallback">微博</span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if ($share_enabled['qzone']): ?>
                <a class="share-icon share-link" target="_blank" rel="noopener" aria-label="QQ空间" href="https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=<?= e($share_url_q) ?>&title=<?= e($share_title_q) ?>&summary=<?= e($share_text_q) ?>&pics=<?= e($share_image_q) ?>">
                    <?php if (!empty($share_icons['qzone'])): ?>
                        <img src="<?= e($share_icons['qzone']) ?>" alt="QQ空间">
                    <?php else: ?>
                        <span class="share-fallback">QZ</span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if ($share_enabled['telegram']): ?>
                <a class="share-icon share-link" target="_blank" rel="noopener" aria-label="Telegram" href="https://t.me/share/url?url=<?= e($share_url_q) ?>&text=<?= e($share_title_q) ?>">
                    <?php if (!empty($share_icons['telegram'])): ?>
                        <img src="<?= e($share_icons['telegram']) ?>" alt="Telegram">
                    <?php else: ?>
                        <span class="share-fallback">TG</span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
>>>>>>> a3d11b8 (sync: update open-source release)
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
<script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
<script>
    (function() {
        const shareUrl = <?= json_encode($share_url) ?>;
        const shareTitle = <?= json_encode($share_title) ?>;
        const shareText = <?= json_encode($share_text) ?>;
        const shareImage = <?= json_encode($share_image) ?>;
        const qqShareUrl = <?= json_encode('https://connect.qq.com/widget/shareqq/index.html?url=' . $share_url_q . '&title=' . $share_title_q . '&summary=' . $share_text_q . '&pics=' . $share_image_q) ?>;
        const wechatEnabled = <?= json_encode($wechat_enabled) ?>;

        const ua = navigator.userAgent.toLowerCase();
        const isWeChat = ua.includes('micromessenger');
        const isQQ = ua.includes(' qq/') || ua.includes('mqqbrowser') || ua.includes('qqbrowser');

        const copyBtn = document.getElementById('share-copy');
        if (copyBtn) {
<<<<<<< HEAD
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(shareUrl);
                    copyBtn.textContent = '已复制';
                    setTimeout(() => copyBtn.textContent = '复制链接', 1500);
=======
            let copyTimer = null;
            const showCopyFeedback = () => {
                copyBtn.classList.add('is-copied');
                if (copyTimer) {
                    clearTimeout(copyTimer);
                }
                copyTimer = setTimeout(() => {
                    copyBtn.classList.remove('is-copied');
                }, 1500);
            };
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(shareUrl);
                    showCopyFeedback();
>>>>>>> a3d11b8 (sync: update open-source release)
                } catch (error) {
                    window.prompt('复制链接', shareUrl);
                }
            });
        }

        const systemBtn = document.getElementById('share-system');
        if (systemBtn) {
            if (navigator.share) {
                systemBtn.addEventListener('click', async () => {
                    try {
                        await navigator.share({
                            title: shareTitle,
                            text: shareText,
                            url: shareUrl,
                        });
                    } catch (error) {
                        // ignore
                    }
                });
            } else {
                systemBtn.style.display = 'none';
            }
        }

        const wechatBtn = document.getElementById('share-wechat');
        if (wechatBtn) {
            if (!wechatEnabled || !isWeChat) {
                wechatBtn.style.display = 'none';
            } else {
                wechatBtn.addEventListener('click', () => {
                    window.alert('请点击右上角分享');
                });
            }
        }

        if (wechatEnabled && isWeChat && window.wx) {
            const cleanUrl = window.location.href.split('#')[0];
            fetch(`/wechat/signature?url=${encodeURIComponent(cleanUrl)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data || !data.signature) {
                        if (wechatBtn) wechatBtn.style.display = 'none';
                        return;
                    }
                    window.wx.config({
                        debug: false,
                        appId: data.appId,
                        timestamp: data.timestamp,
                        nonceStr: data.nonceStr,
                        signature: data.signature,
                        jsApiList: ['updateAppMessageShareData', 'updateTimelineShareData'],
                    });
                    window.wx.ready(() => {
                        window.wx.updateAppMessageShareData({
                            title: shareTitle,
                            desc: shareText,
                            link: shareUrl,
                            imgUrl: shareImage,
                        });
                        window.wx.updateTimelineShareData({
                            title: shareTitle,
                            link: shareUrl,
                            imgUrl: shareImage,
                        });
                    });
                    window.wx.error(() => {
                        if (wechatBtn) wechatBtn.style.display = 'none';
                    });
                })
                .catch(() => {
                    if (wechatBtn) wechatBtn.style.display = 'none';
                });
        }

        if (isQQ && window.mqq && window.mqq.data && typeof window.mqq.data.setShareInfo === 'function') {
            window.mqq.data.setShareInfo({
                title: shareTitle,
                desc: shareText,
                share_url: shareUrl,
                image_url: shareImage,
            });
        }

        const qqBtn = document.getElementById('share-qq');
        if (qqBtn) {
            qqBtn.addEventListener('click', () => {
                if (window.mqq) {
                    if (window.mqq.ui && typeof window.mqq.ui.showShareMenu === 'function') {
                        window.mqq.ui.showShareMenu();
                        return;
                    }
                    if (typeof window.mqq.invoke === 'function') {
                        window.mqq.invoke('ui', 'shareMessage', {
                            title: shareTitle,
                            desc: shareText,
                            share_url: shareUrl,
                            image_url: shareImage,
                        });
                        return;
                    }
                }
                if (navigator.share) {
                    navigator.share({ title: shareTitle, text: shareText, url: shareUrl }).catch(() => {});
                    return;
                }
                window.open(qqShareUrl, '_blank', 'noopener');
            });
        }
    })();
</script>
