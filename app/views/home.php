<?php
$settings = $settings ?? [];
$title = ($settings['site_name'] ?? '') ?: '博客首页';
$meta_description = (($settings['site_name'] ?? '') ?: 'Gaoao Blog') . '，分享技术与生活。';
$canonical_url = url_for($config, '/');
$track_post_id = 0;
$json_ld = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => ($settings['site_name'] ?? '') ?: 'Gaoao Blog',
    'url' => $canonical_url,
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => $canonical_url . 'search?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
];
require __DIR__ . '/partials/header.php';
?>
<?php if (($settings['show_hero'] ?? '1') === '1'): ?>
    <?php if (($settings['hero_mode'] ?? 'text') === 'text'): ?>
        <div class="hero hero-section hero-text">
            <h1><?= e($settings['hero_title'] ?? '欢迎来到 Gaoao Blog') ?></h1>
            <p><?= e($settings['hero_subtitle'] ?? '记录技术与生活的点滴。') ?></p>
        </div>
    <?php elseif (($settings['hero_mode'] ?? 'text') === 'image' && !empty($settings['hero_image_url'])): ?>
        <div class="hero hero-section hero-image" style="margin-bottom: 2rem;">
            <div style="position: relative; width: 100%; padding-top: 16.875%; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <img src="<?= e($settings['hero_image_url']) ?>" alt="Welcome" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php if (!empty($featured_posts) || ($settings['show_admin_card'] ?? '1') === '1'): ?>
    <div class="featured-admin-grid<?= empty($featured_posts) ? ' is-single' : '' ?>">
        <?php if (!empty($featured_posts)): ?>
            <section class="featured">
                <div class="featured-track">
                    <?php foreach ($featured_posts as $index => $post): ?>
                        <?php
                            // Array keys normalized in PublicController
                            $featImage = $post['image'];
                            $featLink = $post['link'];
                        ?>
                        <article class="featured-slide <?= $index === 0 ? 'active' : '' ?>">
                            <?php if (!empty($featImage)): ?>
                                <div class="featured-image-wrapper">
                                    <img src="<?= e($featImage) ?>" alt="<?= e($post['title']) ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="featured-recommend-tag">推荐</div>

                            <!-- Full Clickable Overlay -->
                            <a href="<?= e($featLink) ?>" class="featured-overlay-link" aria-label="<?= e($post['title']) ?>"></a>
                            
                            <div class="featured-content">
                                <h2><a href="<?= e($featLink) ?>"><?= e($post['title']) ?></a></h2>
                                
                                <?php if (empty($post['is_custom'])): ?>
                                <p class="meta">
                                    <span><?= e(date('Y-m-d', strtotime($post['date']))) ?></span>
                                    <?php if (!empty($post['category_name'])): ?>
                                        <span><a href="/category/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a></span>
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
                                                <!-- Mac Style Verified Badge (Starburst) -->
                                                <svg class="verified-badge" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 2L14.85 4.5L18.5 4.2L19.5 7.8L22.5 9.5L21.5 13L22.8 16.5L19.5 18.5L18.8 22.2L15 21.5L12 23.5L9 21.5L5.2 22.2L4.5 18.5L1.2 16.5L2.5 13L1.5 9.5L4.5 7.8L5.5 4.2L9.15 4.5L12 2Z" fill="#007aff"/>
                                                    <path d="M8 12L11 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </span>
                                </p>
                                <?php endif; ?>
                                
                                <p class="featured-excerpt"><?= e(($post['excerpt'] ?? '') ?: snippet($post['content_md'] ?? '', 120) . '...') ?></p>
                                
                                <?php if (!empty($post['tags'])): ?>
                                    <div class="tags">
                                        <?php foreach ($post['tags'] as $tag): ?>
                                            <a href="/tag/<?= e($tag['slug']) ?>">#<?= e($tag['name']) ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="featured-dots">
                    <?php foreach ($featured_posts as $index => $post): ?>
                        <button type="button" class="<?= $index === 0 ? 'active' : '' ?>" data-slide="<?= e((string)$index) ?>"></button>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (($settings['show_admin_card'] ?? '1') === '1'): ?>
            <div class="admin-card">
                <div class="admin-card-avatar-wrapper">
                    <?php if (!empty($settings['admin_card_avatar'])): ?>
                        <img src="<?= e($settings['admin_card_avatar']) ?>" alt="Admin" class="admin-card-avatar">
                    <?php else: ?>
                        <div class="admin-card-avatar" style="background: #ddd;"></div>
                    <?php endif; ?>
                    
                    <div class="admin-card-badge">
                        <?php if (!empty($settings['admin_card_badge'])): ?>
                            <img src="<?= e($settings['admin_card_badge']) ?>" alt="Verified">
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L14.85 4.5L18.5 4.2L19.5 7.8L22.5 9.5L21.5 13L22.8 16.5L19.5 18.5L18.8 22.2L15 21.5L12 23.5L9 21.5L5.2 22.2L4.5 18.5L1.2 16.5L2.5 13L1.5 9.5L4.5 7.8L5.5 4.2L9.15 4.5L12 2Z" fill="#007aff"/>
                                <path d="M8 12L11 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                </div>
                <h3><?= e($settings['admin_card_name'] ?? '管理员') ?></h3>
                <p><?= nl2br(e($settings['admin_card_bio'] ?? '')) ?></p>
                
                <div class="admin-social-links">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php if (!empty($settings["social_icon_{$i}"])): ?>
                            <a href="<?= e($settings["social_link_{$i}"] ?? '#') ?>" target="_blank" class="social-btn">
                                <img src="<?= e($settings["social_icon_{$i}"]) ?>" alt="Social">
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
    $showSidebarCategories = ($settings['show_sidebar_categories'] ?? '1') === '1';
    $showSidebarTags = ($settings['show_sidebar_tags'] ?? '1') === '1';
?>
<section class="content-grid<?= (!$showSidebarCategories && !$showSidebarTags) ? ' no-sidebar' : '' ?>">
    <div class="posts">
        <?php if (empty($posts)): ?>
            <p>暂无文章。</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?= e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>">
                    <?php endif; ?>
                    <div>
                        <h2><a href="/post/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
                        <p class="meta">
                            <span><?= e(date('Y-m-d', strtotime($post['published_at'] ?? $post['created_at']))) ?></span>
                            <?php if (!empty($post['category_name'])): ?>
                                <span><a href="/category/<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a></span>
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
                                        <!-- Mac Style Verified Badge (Starburst) -->
                                        <svg class="verified-badge" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2L14.85 4.5L18.5 4.2L19.5 7.8L22.5 9.5L21.5 13L22.8 16.5L19.5 18.5L18.8 22.2L15 21.5L12 23.5L9 21.5L5.2 22.2L4.5 18.5L1.2 16.5L2.5 13L1.5 9.5L4.5 7.8L5.5 4.2L9.15 4.5L12 2Z" fill="#007aff"/>
                                            <path d="M8 12L11 15L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        </p>
                        <p><?= e($post['excerpt'] ?: snippet($post['content_md'] ?? '', 120) . '...') ?></p>
                        <?php if (!empty($post['tags'])): ?>
                            <div class="tags">
                                <?php foreach ($post['tags'] as $tag): ?>
                                    <a href="/tag/<?= e($tag['slug']) ?>">#<?= e($tag['name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php if ($showSidebarCategories || $showSidebarTags): ?>
        <aside class="sidebar">
            <?php if ($showSidebarCategories): ?>
                <div class="sidebar-card">
                    <h3>分类</h3>
                    <?php if (empty($categories)): ?>
                        <p>暂无分类</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($categories as $category): ?>
                                <li><a href="/category/<?= e($category['slug']) ?>"><?= e($category['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($showSidebarTags): ?>
                <div class="sidebar-card">
                    <h3>标签</h3>
                    <?php if (empty($tags)): ?>
                        <p>暂无标签</p>
                    <?php else: ?>
                        <div class="tag-cloud">
                            <?php foreach ($tags as $tag): ?>
                                <a href="/tag/<?= e($tag['slug']) ?>">#<?= e($tag['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>
    <?php endif; ?>
</section>

<?php if (!empty($featured_posts)): ?>
<script>
    const track = document.querySelector('.featured-track');
    const slides = Array.from(document.querySelectorAll('.featured-slide'));
    const dots = Array.from(document.querySelectorAll('.featured-dots button'));
    let currentIndex = 0;
    let timer = null;

    function goToSlide(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        
        track.style.transform = `translateX(-${index * 100}%)`;
        
        dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
        currentIndex = index;
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            goToSlide(i);
            clearInterval(timer);
            timer = setInterval(nextSlide, 4000);
        });
    });

    if (slides.length > 1) {
        timer = setInterval(nextSlide, 4000);
    }
</script>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
