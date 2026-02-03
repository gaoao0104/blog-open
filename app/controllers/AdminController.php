<?php

declare(strict_types=1);

final class AdminController
{
    public static function dashboard(\PDO $pdo, array $config): void
    {
        Auth::requireLogin();

<<<<<<< HEAD
        $stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
        $posts = $stmt->fetchAll();
=======
        if (Auth::hasPermission('posts.manage_all')) {
            $stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
            $posts = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE p.user_id = ? ORDER BY p.created_at DESC');
            $stmt->execute([Auth::userId()]);
            $posts = $stmt->fetchAll();
        }
>>>>>>> a3d11b8 (sync: update open-source release)

        View::render('admin/dashboard', [
            'config' => $config,
            'posts' => $posts,
        ]);
    }
    
    public static function stats(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
        View::render('admin/stats', ['config' => $config]);
    }

=======
        Auth::requirePermission('stats.view');
        View::render('admin/stats', ['config' => $config]);
    }

    public static function maintenance(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('maintenance.manage');
        self::requireTestEnv($config);

        $stats = [
            'total_posts' => (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
            'published_posts' => (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
            'featured_posts' => (int)$pdo->query('SELECT COUNT(*) FROM posts WHERE is_featured = 1')->fetchColumn(),
            'non_featured_posts' => (int)$pdo->query('SELECT COUNT(*) FROM posts WHERE is_featured = 0')->fetchColumn(),
            'featured_cards' => 0,
            'has_featured_cards' => false,
        ];

        $hasFeaturedCards = (bool)$pdo->query("SHOW TABLES LIKE 'featured_cards'")->fetchColumn();
        if ($hasFeaturedCards) {
            $stats['featured_cards'] = (int)$pdo->query('SELECT COUNT(*) FROM featured_cards')->fetchColumn();
            $stats['has_featured_cards'] = true;
        }

        $posts = $pdo->query('SELECT id, title, is_featured, status FROM posts ORDER BY id DESC LIMIT 10')->fetchAll();
        $cleanupLogs = [];
        if (self::tableExists($pdo, 'r2_cleanup_logs')) {
            $cleanupLogs = $pdo->query('SELECT * FROM r2_cleanup_logs ORDER BY created_at DESC LIMIT 20')->fetchAll();
        }

        View::render('admin/maintenance', [
            'config' => $config,
            'stats' => $stats,
            'posts' => $posts,
            'cleanup_logs' => $cleanupLogs,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function migrateFeaturedCards(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('maintenance.manage');
        self::requireTestEnv($config);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/maintenance');
        }

        try {
            $pdo->exec('CREATE TABLE IF NOT EXISTS featured_cards (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NULL,
                title VARCHAR(255) NULL,
                link_url VARCHAR(255) NULL,
                image_url VARCHAR(255) NULL,
                sort_order INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (post_id),
                INDEX (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

            $stmt = $pdo->query('SELECT id, featured_link_url, featured_card_image, featured_order, featured_at FROM posts WHERE is_featured = 1');
            $posts = $stmt->fetchAll();

            $insert = $pdo->prepare('INSERT INTO featured_cards (post_id, title, link_url, image_url, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?)');
            $check = $pdo->prepare('SELECT id FROM featured_cards WHERE post_id = ? LIMIT 1');

            $count = 0;
            $skipped = 0;
            foreach ($posts as $post) {
                $check->execute([$post['id']]);
                if ($check->fetch()) {
                    $skipped++;
                    continue;
                }

                $insert->execute([
                    $post['id'],
                    null,
                    $post['featured_link_url'] ?: null,
                    $post['featured_card_image'] ?: null,
                    (int)($post['featured_order'] ?? 0),
                    $post['featured_at'] ?: date('Y-m-d H:i:s'),
                ]);
                $count++;
            }

            flash('success', "迁移完成：新增 {$count} 条，跳过 {$skipped} 条。");
        } catch (Throwable $e) {
            flash('error', '迁移失败：' . $e->getMessage());
        }

        redirect_to('/admin/maintenance');
    }

>>>>>>> a3d11b8 (sync: update open-source release)
    public static function newPostForm(\PDO $pdo, array $config): void
    {
        Auth::requireLogin();

        $categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

        View::render('admin/post-form', [
            'config' => $config,
            'categories' => $categories,
            'post' => null,
            'tags' => '',
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function createPost(\PDO $pdo, array $config): void
    {
        Auth::requireLogin();

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/posts/new');
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $status = $_POST['status'] ?? 'draft';
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $tagsRaw = trim($_POST['tags'] ?? '');

        if ($title === '' || $content === '') {
            flash('error', '标题和内容不能为空。');
            redirect_to('/admin/posts/new');
        }

        if ($slug === '') {
            $slug = slugify($title);
        }
        if ($slug === '') {
            $slug = 'post-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
        }

        $featuredImage = self::handleUpload($config);
        if ($excerpt === '') {
            $excerpt = Summary::generate($config, $content, 200);
        }

        $featuredAt = null;
        $featuredOrder = 0;
        if ($isFeatured) {
            $featuredAt = date('Y-m-d H:i:s');
            $featuredOrder = self::nextFeaturedOrder($pdo);
        }

        $stmt = $pdo->prepare('INSERT INTO posts (title, slug, content_md, excerpt, status, category_id, featured_image, is_featured, featured_order, featured_at, user_id, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ? )');
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
        $stmt->execute([
            $title,
            $slug,
            $content,
            $excerpt,
            $status,
            $categoryId ?: null,
            $featuredImage,
            $isFeatured,
            $featuredOrder,
            $featuredAt,
            $_SESSION['user_id'],
            $publishedAt,
        ]);

        $postId = (int)$pdo->lastInsertId();
        self::syncTags($pdo, $postId, $tagsRaw);

        flash('success', '文章已保存。');
        redirect_to('/admin');
    }

    public static function editPostForm(\PDO $pdo, array $config, int $id): void
    {
        Auth::requireLogin();
<<<<<<< HEAD
=======
        self::requirePostOwnerOrAdmin($pdo, $id);
>>>>>>> a3d11b8 (sync: update open-source release)

        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post) {
            flash('error', '文章不存在。');
            redirect_to('/admin');
        }

        $categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
        $tags = $pdo->prepare('SELECT t.name FROM tags t INNER JOIN post_tags pt ON pt.tag_id = t.id WHERE pt.post_id = ?');
        $tags->execute([$id]);
        $tagNames = array_column($tags->fetchAll(), 'name');

        View::render('admin/post-form', [
            'config' => $config,
            'categories' => $categories,
            'post' => $post,
            'tags' => implode(', ', $tagNames),
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function updatePost(\PDO $pdo, array $config, int $id): void
    {
        Auth::requireLogin();
<<<<<<< HEAD
=======
        self::requirePostOwnerOrAdmin($pdo, $id);
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/posts/edit?id=' . $id);
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $status = $_POST['status'] ?? 'draft';
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $tagsRaw = trim($_POST['tags'] ?? '');

        if ($title === '' || $content === '') {
            flash('error', '标题和内容不能为空。');
            redirect_to('/admin/posts/edit?id=' . $id);
        }

        if ($slug === '') {
            $slug = slugify($title);
        }
        if ($slug === '') {
            $slug = 'post-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
        }

        $featuredImage = self::handleUpload($config);
        if ($excerpt === '') {
            $excerpt = Summary::generate($config, $content, 200);
        }

        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
        $featuredAt = null;
        $featuredOrder = 0;

        $currentStmt = $pdo->prepare('SELECT is_featured, featured_order, featured_at FROM posts WHERE id = ?');
        $currentStmt->execute([$id]);
        $currentRow = $currentStmt->fetch();

        if ($isFeatured) {
            $wasFeatured = !empty($currentRow) && !empty($currentRow['is_featured']);
            if ($wasFeatured) {
                $featuredAt = $currentRow['featured_at'] ?: date('Y-m-d H:i:s');
                $featuredOrder = (int)($currentRow['featured_order'] ?? 0);
                if ($featuredOrder <= 0) {
                    $featuredOrder = self::nextFeaturedOrder($pdo);
                }
            } else {
                $featuredAt = date('Y-m-d H:i:s');
                $featuredOrder = self::nextFeaturedOrder($pdo);
            }
        }

        if ($featuredImage) {
            $stmt = $pdo->prepare('UPDATE posts SET title = ?, slug = ?, content_md = ?, excerpt = ?, status = ?, category_id = ?, featured_image = ?, is_featured = ?, featured_order = ?, featured_at = ?, updated_at = NOW(), published_at = ? WHERE id = ?');
            $stmt->execute([$title, $slug, $content, $excerpt, $status, $categoryId ?: null, $featuredImage, $isFeatured, $featuredOrder, $featuredAt, $publishedAt, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE posts SET title = ?, slug = ?, content_md = ?, excerpt = ?, status = ?, category_id = ?, is_featured = ?, featured_order = ?, featured_at = ?, updated_at = NOW(), published_at = ? WHERE id = ?');
            $stmt->execute([$title, $slug, $content, $excerpt, $status, $categoryId ?: null, $isFeatured, $featuredOrder, $featuredAt, $publishedAt, $id]);
        }

        self::syncTags($pdo, $id, $tagsRaw);

        flash('success', '文章已更新。');
        redirect_to('/admin');
    }

    public static function featuredPosts(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('featured.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        // Fetch Featured Cards (Joined with Posts for fallback data)
        // LEFT JOIN posts allows accessing post details if linked, but also supports cards without posts
        $featuredStmt = $pdo->prepare('
            SELECT 
                fc.id as card_id, 
                fc.post_id,
                fc.title as custom_title,
                fc.link_url as custom_link,
                fc.image_url as custom_image,
                fc.sort_order,
                p.id as original_post_id,
                p.title as post_title,
                p.slug as post_slug,
                p.featured_image as post_image,
                p.published_at as post_date
            FROM featured_cards fc
            LEFT JOIN posts p ON fc.post_id = p.id
            ORDER BY fc.sort_order DESC, fc.created_at DESC
        ');
        $featuredStmt->execute();
        $featuredRaw = $featuredStmt->fetchAll();
        
        // Normalize for frontend
        $featured = [];
        foreach ($featuredRaw as $row) {
             $featured[] = [
                 'id' => $row['card_id'], // This is now the CARD ID, not POST ID
                 'post_id' => $row['post_id'],
                 'title' => $row['custom_title'] ?: $row['post_title'],
                 'image' => $row['custom_image'] ?: $row['post_image'],
                 'link' => $row['custom_link'] ?: ($row['post_slug'] ? '/post/' . $row['post_slug'] : ''),
                 'is_custom' => empty($row['post_id']),
                 'published_at' => $row['post_date']
             ];
        }

        // Available Posts (Exclude those already in featured_cards with a post_id)
        $availableStmt = $pdo->prepare('
            SELECT id, title, slug, published_at, featured_image 
            FROM posts 
            WHERE status = ? 
            AND id NOT IN (SELECT post_id FROM featured_cards WHERE post_id IS NOT NULL)
            ORDER BY published_at DESC
        ');
        $availableStmt->execute(['published']);
        $available = $availableStmt->fetchAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['featured' => $featured, 'available' => $available], JSON_UNESCAPED_UNICODE);
    }
    
    // Create/Add Featured Item
    public static function addFeaturedItem(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('featured.manage');
>>>>>>> a3d11b8 (sync: update open-source release)
        $data = $_POST;
        
        $postId = !empty($data['post_id']) ? (int)$data['post_id'] : null;
        $title = trim($data['title'] ?? '');
        $link = trim($data['link_url'] ?? '');
        $file = $_FILES['featured_card_image'] ?? null;
        
        // Validation: If no post, must have generic info? 
        // Actually user wants to add "Image + Link" OR "Article".
        // If article, title/link/image fallback to article.
        // If no article, Title is somewhat required for admin usage? Or optional?
        // Let's assume Title is useful for Admin UI at least.
        
        $imagePath = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
             $imagePath = self::handleUpload($file);
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO featured_cards (post_id, title, link_url, image_url, sort_order) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([
                $postId,
                $title ?: null,
                $link ?: null,
                $imagePath
            ]);
            

            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'ok']);
        } catch (\PDOException $e) {
             header('Content-Type: application/json; charset=utf-8');
             http_response_code(500);
             echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Update Featured Item
    public static function updateFeaturedItem(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('featured.manage');
>>>>>>> a3d11b8 (sync: update open-source release)
        $cardId = (int)($_POST['card_id'] ?? 0);
        if ($cardId <= 0) {
            echo json_encode(['error' => 'invalid_id']); return;
        }

        // Fetch existing
        $currStmt = $pdo->prepare("SELECT * FROM featured_cards WHERE id = ?");
        $currStmt->execute([$cardId]);
        $current = $currStmt->fetch();
        if (!$current) { echo json_encode(['error' => 'not_found']); return; }

        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link_url'] ?? '');
        
        $imagePath = $current['image_url'];
        if (isset($_FILES['featured_card_image']) && $_FILES['featured_card_image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = self::handleUpload($_FILES['featured_card_image']);
        } elseif (!empty($_POST['clear_image'])) {
            $imagePath = null;
        }
        
        // Logic for clear link
        if (!empty($_POST['clear_link'])) {
            $link = null;
        }
        
        // If updating title, we set it. If empty string from form, do we set NULL?
        // If it's a post-linked card, empty string usually means "inherit". 
        // If it's custom, empty string is just empty.
        // Let's allow nullable.
        $titleToSave = $title !== '' ? $title : null;
        $linkToSave = $link !== '' ? $link : null;

        $stmt = $pdo->prepare("UPDATE featured_cards SET title = ?, link_url = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$titleToSave, $linkToSave, $imagePath, $cardId]);

        echo json_encode(['status' => 'ok']);
    }

    // Delete/Remove Featured Item
    public static function removeFeaturedItem(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('featured.manage');
>>>>>>> a3d11b8 (sync: update open-source release)
        $data = self::readJsonBody();
        $id = (int)($data['id'] ?? $_POST['id'] ?? 0);
        $csrfToken = $data['csrf_token'] ?? $_POST['csrf_token'] ?? null;

        header('Content-Type: application/json; charset=utf-8');

        if (!Csrf::verify($csrfToken)) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM featured_cards WHERE id = ?');
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'not_found'], JSON_UNESCAPED_UNICODE);
                return;
            }
            echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_id'], JSON_UNESCAPED_UNICODE);
        }
    }
    
    // Reorder
    public static function reorderFeaturedItems(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('featured.manage');
>>>>>>> a3d11b8 (sync: update open-source release)
        $data = self::readJsonBody();
        $order = $data['order'] ?? []; // Array of Card IDs
        
        if (!empty($order)) {
            $sql = "UPDATE featured_cards SET sort_order = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            // Higher index = Lower sort_order (display first usually means desc sort_order or asc index)
            // Existing logic was "ORDER BY featured_order DESC"
            // So first item should have highest number.
            $max = count($order);
            foreach ($order as $index => $id) {
                // If frontend sends [id1, id2, id3] (first to last)
                // id1 should have sort_order 3, id2 = 2, id3 = 1
                $rank = $max - $index;
                $stmt->execute([$rank, (int)$id]);
            }
            echo json_encode(['status' => 'ok']);
        } else {
             echo json_encode(['status' => 'ok']); 
        }
    }

    public static function featuredPage(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('featured.manage');
>>>>>>> a3d11b8 (sync: update open-source release)
        View::render('admin/featured', [
            'config' => $config,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function updateFeaturedMeta(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('featured.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $data = self::readJsonBody();
        $csrfToken = $_POST['csrf_token'] ?? $data['csrf_token'] ?? null;
        if (!Csrf::verify($csrfToken)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id = (int)($data['id'] ?? $_POST['id'] ?? $data['post_id'] ?? $_POST['post_id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_id'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $existsStmt = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
        $existsStmt->execute([$id]);
        if (!$existsStmt->fetch()) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'not_found'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $linkProvided = array_key_exists('link_url', $data) || array_key_exists('link_url', $_POST);
        $linkUrl = $linkProvided ? trim((string)($data['link_url'] ?? $_POST['link_url'] ?? '')) : null;
        $clearLink = self::normalizeBool($data['clear_link'] ?? $_POST['clear_link'] ?? null) ?? 0;

        $imageUrl = self::handleUpload($config, false, 'featured_card_image');
        $clearImage = self::normalizeBool($data['clear_image'] ?? $_POST['clear_image'] ?? null) ?? 0;

        $set = [];
        $params = [];

        if ($linkProvided || $clearLink) {
            $set[] = 'featured_link_url = ?';
            if ($clearLink || $linkUrl === '') {
                $params[] = null;
            } else {
                $params[] = $linkUrl;
            }
        }

        if ($imageUrl) {
            $set[] = 'featured_card_image = ?';
            $params[] = $imageUrl;
        } elseif ($clearImage) {
            $set[] = 'featured_card_image = NULL';
        }

        if (empty($set)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'no_changes'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $sql = 'UPDATE posts SET ' . implode(', ', $set) . ' WHERE id = ?';
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok', 'featured_card_image' => $imageUrl], JSON_UNESCAPED_UNICODE);
    }

    public static function generateSummary(array $config): void
    {
        Auth::requireLogin();

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_csrf']);
            return;
        }

        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            http_response_code(400);
            echo json_encode(['error' => 'empty_content']);
            return;
        }

<<<<<<< HEAD
        $summary = Summary::generate($config, $content, 200);
        $error = Summary::lastError();
=======
        $summary = Summary::generate($config, $content, 50);
        $error = Summary::lastError();
        if ($summary !== '' && $error !== null) {
            $error = null;
        }
>>>>>>> a3d11b8 (sync: update open-source release)
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['summary' => $summary, 'error' => $error], JSON_UNESCAPED_UNICODE);
    }

    public static function deletePost(\PDO $pdo, int $id): void
    {
        Auth::requireLogin();
<<<<<<< HEAD
=======
        self::requirePostOwnerOrAdmin($pdo, $id);
>>>>>>> a3d11b8 (sync: update open-source release)

        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);

        flash('success', '文章已删除。');
        redirect_to('/admin');
    }

    public static function categories(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('categories.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

        View::render('admin/categories', [
            'config' => $config,
            'categories' => $categories,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function createCategory(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('categories.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/categories');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', '分类名称不能为空。');
            redirect_to('/admin/categories');
        }

        $slug = slugify($name);
        $stmt = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
        $stmt->execute([$name, $slug]);

        flash('success', '分类已添加。');
        redirect_to('/admin/categories');
    }

    public static function deleteCategory(\PDO $pdo, int $id): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('categories.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);

        flash('success', '分类已删除。');
        redirect_to('/admin/categories');
    }

    public static function tags(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('tags.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $tags = $pdo->query('SELECT * FROM tags ORDER BY name ASC')->fetchAll();

        View::render('admin/tags', [
            'config' => $config,
            'tags' => $tags,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function createTag(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('tags.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/tags');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', '标签名称不能为空。');
            redirect_to('/admin/tags');
        }

        $slug = slugify($name);
        $stmt = $pdo->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)');
        $stmt->execute([$name, $slug]);

        flash('success', '标签已添加。');
        redirect_to('/admin/tags');
    }

    public static function deleteTag(\PDO $pdo, int $id): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('tags.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $stmt = $pdo->prepare('DELETE FROM tags WHERE id = ?');
        $stmt->execute([$id]);

        flash('success', '标签已删除。');
        redirect_to('/admin/tags');
    }

    public static function comments(\PDO $pdo, array $config): void
    {
        Auth::requireLogin();

<<<<<<< HEAD
        $stmt = $pdo->query('SELECT c.*, p.title AS post_title FROM comments c INNER JOIN posts p ON p.id = c.post_id ORDER BY c.created_at DESC');
        $comments = $stmt->fetchAll();
=======
        if (Auth::hasPermission('comments.manage_all')) {
            $stmt = $pdo->query('SELECT c.*, p.title AS post_title FROM comments c INNER JOIN posts p ON p.id = c.post_id ORDER BY c.created_at DESC');
            $comments = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare('SELECT c.*, p.title AS post_title FROM comments c INNER JOIN posts p ON p.id = c.post_id WHERE p.user_id = ? ORDER BY c.created_at DESC');
            $stmt->execute([Auth::userId()]);
            $comments = $stmt->fetchAll();
        }
>>>>>>> a3d11b8 (sync: update open-source release)

        View::render('admin/comments', [
            'config' => $config,
            'comments' => $comments,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function approveComment(\PDO $pdo, int $id): void
    {
        Auth::requireLogin();
<<<<<<< HEAD
=======
        self::requireCommentOwnerOrAdmin($pdo, $id);
>>>>>>> a3d11b8 (sync: update open-source release)

        $stmt = $pdo->prepare('UPDATE comments SET status = ? WHERE id = ?');
        $stmt->execute(['approved', $id]);

        flash('success', '评论已通过。');
        redirect_to('/admin/comments');
    }

    public static function deleteComment(\PDO $pdo, int $id): void
    {
        Auth::requireLogin();
<<<<<<< HEAD
=======
        self::requireCommentOwnerOrAdmin($pdo, $id);
>>>>>>> a3d11b8 (sync: update open-source release)

        $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->execute([$id]);

        flash('success', '评论已删除。');
        redirect_to('/admin/comments');
    }

    public static function uploads(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('uploads.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $uploads = $pdo->query('SELECT * FROM uploads ORDER BY created_at DESC')->fetchAll();

        View::render('admin/uploads', [
            'config' => $config,
            'uploads' => $uploads,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function postCards(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('cards.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $postId = (int)($_GET['post_id'] ?? 0);
        if ($postId <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_post_id'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt = $pdo->prepare('SELECT * FROM post_cards WHERE post_id = ? ORDER BY priority DESC, id DESC');
        $stmt->execute([$postId]);
        $cards = $stmt->fetchAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['cards' => $cards], JSON_UNESCAPED_UNICODE);
    }

    public static function createPostCard(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('cards.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $linkUrl = trim($_POST['link_url'] ?? '');
        $priority = (int)($_POST['priority'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($postId <= 0 || $title === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_payload'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $imageUrl = self::handleUpload($config, false, 'card_image');

        $stmt = $pdo->prepare('INSERT INTO post_cards (post_id, title, description, image_url, link_url, priority, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
            $postId,
            $title,
            $description ?: null,
            $imageUrl,
            $linkUrl ?: null,
            $priority,
            $isActive,
        ]);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['id' => (int)$pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
    }

    public static function updatePostCard(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('cards.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $linkUrl = trim($_POST['link_url'] ?? '');
        $priority = (int)($_POST['priority'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($id <= 0 || $title === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_payload'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $imageUrl = self::handleUpload($config, false, 'card_image');

        if ($imageUrl) {
            $stmt = $pdo->prepare('UPDATE post_cards SET title = ?, description = ?, image_url = ?, link_url = ?, priority = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([
                $title,
                $description ?: null,
                $imageUrl,
                $linkUrl ?: null,
                $priority,
                $isActive,
                $id,
            ]);
        } else {
            $stmt = $pdo->prepare('UPDATE post_cards SET title = ?, description = ?, link_url = ?, priority = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([
                $title,
                $description ?: null,
                $linkUrl ?: null,
                $priority,
                $isActive,
                $id,
            ]);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
    }

    public static function deletePostCard(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('cards.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_id'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $pdo->prepare('DELETE FROM post_cards WHERE id = ?')->execute([$id]);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
    }

    public static function users(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();

        $users = $pdo->query('SELECT id, username, nickname, avatar_url, is_verified, verified_badge_url, created_at FROM users ORDER BY created_at DESC')->fetchAll();
=======
        Auth::requirePermission('users.manage');

        $users = $pdo->query('SELECT u.id, u.username, u.nickname, u.avatar_url, u.is_verified, u.verified_badge_url, u.role, u.group_id, u.created_at, g.name AS group_name, g.is_super AS group_is_super FROM users u LEFT JOIN admin_groups g ON g.id = u.group_id ORDER BY u.created_at DESC')->fetchAll();
        $groups = $pdo->query('SELECT id, name, is_super FROM admin_groups ORDER BY is_super DESC, name ASC')->fetchAll();
>>>>>>> a3d11b8 (sync: update open-source release)

        View::render('admin/users', [
            'config' => $config,
            'users' => $users,
<<<<<<< HEAD
=======
            'groups' => $groups,
>>>>>>> a3d11b8 (sync: update open-source release)
            'csrf_token' => Csrf::token(),
        ]);
    }

<<<<<<< HEAD
    public static function settings(array $config): void
    {
        Auth::requireLogin();
=======
    public static function groups(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('groups.manage');

        $groups = $pdo->query('SELECT g.*, COUNT(u.id) AS user_count FROM admin_groups g LEFT JOIN users u ON u.group_id = g.id GROUP BY g.id ORDER BY g.is_super DESC, g.name ASC')->fetchAll();
        $permRows = $pdo->query('SELECT group_id, perm_key FROM admin_group_permissions')->fetchAll();

        $groupPermissions = [];
        foreach ($permRows as $row) {
            $groupPermissions[(int)$row['group_id']][] = $row['perm_key'];
        }

        View::render('admin/groups', [
            'config' => $config,
            'groups' => $groups,
            'permissions' => self::permissionDefinitions(),
            'group_permissions' => $groupPermissions,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function createGroup(\PDO $pdo): void
    {
        Auth::requirePermission('groups.manage');

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/groups');
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isSuper = isset($_POST['is_super']) ? 1 : 0;

        if ($name === '') {
            flash('error', '组名称不能为空。');
            redirect_to('/admin/groups');
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO admin_groups (name, description, is_super, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$name, $description ?: null, $isSuper]);
            flash('success', '分组已创建。');
        } catch (\PDOException $e) {
            flash('error', '创建失败：' . $e->getMessage());
        }

        redirect_to('/admin/groups');
    }

    public static function manageGroup(\PDO $pdo, array $config, int $id): void
    {
        Auth::requirePermission('groups.manage');

        if ($id <= 0) {
            flash('error', '无效的分组ID。');
            redirect_to('/admin/groups');
        }

        $stmt = $pdo->prepare('SELECT * FROM admin_groups WHERE id = ?');
        $stmt->execute([$id]);
        $group = $stmt->fetch();

        if (!$group) {
            flash('error', '分组不存在。');
            redirect_to('/admin/groups');
        }

        $permRows = $pdo->prepare('SELECT perm_key FROM admin_group_permissions WHERE group_id = ?');
        $permRows->execute([$id]);
        $selected = $permRows->fetchAll(\PDO::FETCH_COLUMN);

        View::render('admin/group-manage', [
            'config' => $config,
            'group' => $group,
            'permissions' => self::permissionDefinitions(),
            'selected_permissions' => $selected,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function updateGroupInfo(\PDO $pdo): void
    {
        Auth::requirePermission('groups.manage');

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/groups');
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isSuper = isset($_POST['is_super']) ? 1 : 0;

        if ($id <= 0 || $name === '') {
            flash('error', '分组信息不完整。');
            redirect_to('/admin/groups');
        }

        $pdo->prepare('UPDATE admin_groups SET name = ?, description = ?, is_super = ? WHERE id = ?')
            ->execute([$name, $description ?: null, $isSuper, $id]);

        flash('success', '分组基本信息已更新。');
        redirect_to('/admin/groups');
    }

    public static function updateGroupPermissions(\PDO $pdo): void
    {
        Auth::requirePermission('groups.manage');

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/groups');
        }

        $id = (int)($_POST['id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];

        if ($id <= 0) {
            flash('error', '无效的分组ID。');
            redirect_to('/admin/groups');
        }

        // Check if group is super admin
        $stmt = $pdo->prepare('SELECT is_super FROM admin_groups WHERE id = ?');
        $stmt->execute([$id]);
        $isSuper = (int)$stmt->fetchColumn();

        $allowed = array_keys(self::permissionDefinitions());
        $permissions = array_values(array_intersect($allowed, is_array($permissions) ? $permissions : []));

        $pdo->prepare('DELETE FROM admin_group_permissions WHERE group_id = ?')->execute([$id]);
        
        // Only save permissions if not super admin (super admin has all implicitly)
        // However, user might want to check them for visual feedback or future downgrades.
        // But the original logic cleared them if super. Let's keep logic: if super, no explicit perms usually needed.
        // But for consistency let's save them if passed, or just ignore. 
        // The original logic was: IF super, disable checkboxes. So POST probably has empty permissions.
        // Let's stick to: if not super, save perms.
        
        if (!$isSuper && !empty($permissions)) {
            $insert = $pdo->prepare('INSERT INTO admin_group_permissions (group_id, perm_key) VALUES (?, ?)');
            foreach ($permissions as $perm) {
                $insert->execute([$id, $perm]);
            }
        }

        flash('success', '权限配置已更新。');
        redirect_to('/admin/groups/manage?id=' . $id);
    }

    public static function updateGroup(\PDO $pdo): void
    {
        // Legacy or Fallback
        Auth::requirePermission('groups.manage');
        self::updateGroupInfo($pdo);
    }

    public static function deleteGroup(\PDO $pdo): void
    {
        Auth::requirePermission('groups.manage');

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/groups');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', '分组不存在。');
            redirect_to('/admin/groups');
        }

        $stmt = $pdo->prepare('SELECT is_super FROM admin_groups WHERE id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() === 1) {
            flash('error', '超级管理员组不能删除。');
            redirect_to('/admin/groups');
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE group_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            flash('error', '该分组下仍有用户，无法删除。');
            redirect_to('/admin/groups');
        }

        $pdo->prepare('DELETE FROM admin_groups WHERE id = ?')->execute([$id]);
        flash('success', '分组已删除。');
        redirect_to('/admin/groups');
    }

    public static function settings(array $config): void
    {
        Auth::requirePermission('settings.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $settings = Settings::defaults();
        if (isset($GLOBALS['settings'])) {
            $settings = array_merge($settings, $GLOBALS['settings']);
        }

        View::render('admin/settings', [
            'config' => $config,
            'settings' => $settings,
            'csrf_token' => Csrf::token(),
            'active_tab' => 'display',
        ]);
    }

    public static function settingsHero(array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('settings.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $settings = Settings::defaults();
        if (isset($GLOBALS['settings'])) {
            $settings = array_merge($settings, $GLOBALS['settings']);
        }

        View::render('admin/settings-hero', [
            'config' => $config,
            'settings' => $settings,
            'csrf_token' => Csrf::token(),
            'active_tab' => 'hero',
        ]);
    }

    public static function settingsAdminCard(array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('settings.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $settings = Settings::defaults();
        if (isset($GLOBALS['settings'])) {
            $settings = array_merge($settings, $GLOBALS['settings']);
        }

        View::render('admin/settings-admin-card', [
            'config' => $config,
            'settings' => $settings,
            'csrf_token' => Csrf::token(),
            'active_tab' => 'admin_card',
        ]);
    }

<<<<<<< HEAD
    public static function updateSettings(\PDO $pdo, array $config): void
    {
        Auth::requireLogin();
=======
    public static function settingsShare(array $config): void
    {
        Auth::requirePermission('settings.manage');

        $settings = Settings::defaults();
        if (isset($GLOBALS['settings'])) {
            $settings = array_merge($settings, $GLOBALS['settings']);
        }

        View::render('admin/settings-share', [
            'config' => $config,
            'settings' => $settings,
            'csrf_token' => Csrf::token(),
            'active_tab' => 'share',
        ]);
    }

    public static function settingsMenu(array $config): void
    {
        Auth::requirePermission('settings.manage');

        $settings = Settings::defaults();
        if (isset($GLOBALS['settings'])) {
            $settings = array_merge($settings, $GLOBALS['settings']);
        }

        View::render('admin/settings-menu', [
            'config' => $config,
            'settings' => $settings,
            'csrf_token' => Csrf::token(),
            'active_tab' => 'menu',
        ]);
    }

    public static function updateSettings(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('settings.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/settings');
        }

        $faviconUrl = trim($_POST['favicon_url'] ?? '');
        $uploaded = self::handleUpload($config, false, 'favicon_file');
        if ($uploaded) {
            $faviconUrl = $uploaded;
        }

        $values = [
            'site_name' => trim($_POST['site_name'] ?? ''),
            'header_title' => trim($_POST['header_title'] ?? ''),
            'show_nav_home' => isset($_POST['show_nav_home']) ? '1' : '0',
            'show_nav_search' => isset($_POST['show_nav_search']) ? '1' : '0',
            'show_nav_admin' => isset($_POST['show_nav_admin']) ? '1' : '0',
            'show_search_form' => isset($_POST['show_search_form']) ? '1' : '0',
            'show_hero' => isset($_POST['show_hero']) ? '1' : '0',
            'favicon_url' => $faviconUrl,
            'footer_copyright' => trim($_POST['footer_copyright'] ?? ''),
            'show_admin_card' => isset($_POST['show_admin_card']) ? '1' : '0',
            // Sidebar
            'show_sidebar_categories' => isset($_POST['show_sidebar_categories']) ? '1' : '0',
            'show_sidebar_tags' => isset($_POST['show_sidebar_tags']) ? '1' : '0',
        ];

        Settings::setMany($pdo, $values);
        $GLOBALS['settings'] = Settings::all($pdo);

        flash('success', '站点配置已更新。');
        redirect_to('/admin/settings');
    }

    public static function updateSettingsHero(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('settings.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/settings/hero');
        }

        $heroUrl = trim($_POST['hero_image_url'] ?? '');
        if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] !== UPLOAD_ERR_OK && $_FILES['hero_image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => '文件大小超过服务器限制',
                UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
                UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
                UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
                UPLOAD_ERR_CANT_WRITE => '文件写入失败',
                UPLOAD_ERR_EXTENSION => '文件上传被扩展程序停止',
            ];
            $errorCode = $_FILES['hero_image_file']['error'];
            $errorMsg = $uploadErrors[$errorCode] ?? '未知错误';
            flash('error', "欢迎图片上传失败：{$errorMsg} (代码 {$errorCode})");
        }

        $heroUploaded = self::handleUpload($config, false, 'hero_image_file');
        if ($heroUploaded) {
            $heroUrl = $heroUploaded;
        }

        $values = [
            'hero_title' => trim($_POST['hero_title'] ?? ''),
            'hero_subtitle' => trim($_POST['hero_subtitle'] ?? ''),
            'hero_mode' => $_POST['hero_mode'] ?? 'text',
            'hero_image_url' => $heroUrl,
        ];

        Settings::setMany($pdo, $values);
        $GLOBALS['settings'] = Settings::all($pdo);

        flash('success', '欢迎模块已更新。');
        redirect_to('/admin/settings/hero');
    }

    public static function updateSettingsAdminCard(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('settings.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/settings/admin-card');
        }

        $adminAvatarUrl = trim($_POST['admin_card_avatar'] ?? '');
        $adminAvatarUploaded = self::handleUpload($config, false, 'admin_card_avatar_file');
        if ($adminAvatarUploaded) {
            $adminAvatarUrl = $adminAvatarUploaded;
        }

        $adminBadgeUrl = trim($_POST['admin_card_badge'] ?? '');
        $adminBadgeUploaded = self::handleUpload($config, false, 'admin_card_badge_file');
        if ($adminBadgeUploaded) {
            $adminBadgeUrl = $adminBadgeUploaded;
        }

        $socialValues = [];
        for ($i = 1; $i <= 4; $i++) {
            $iconKey = "social_icon_{$i}";
            $linkKey = "social_link_{$i}";
            
            $iconUrl = trim($_POST[$iconKey] ?? '');
            $iconUploaded = self::handleUpload($config, false, "{$iconKey}_file");
            if ($iconUploaded) {
                $iconUrl = $iconUploaded;
            }
            
            $socialValues[$iconKey] = $iconUrl;
            $socialValues[$linkKey] = trim($_POST[$linkKey] ?? '');
        }

        $values = [
            'admin_card_avatar' => $adminAvatarUrl,
            'admin_card_name' => trim($_POST['admin_card_name'] ?? ''),
            'admin_card_bio' => trim($_POST['admin_card_bio'] ?? ''),
            'admin_card_badge' => $adminBadgeUrl,
            ...$socialValues,
        ];

        Settings::setMany($pdo, $values);
        $GLOBALS['settings'] = Settings::all($pdo);

        flash('success', '管理员名片已更新。');
        redirect_to('/admin/settings/admin-card');
    }

<<<<<<< HEAD
    public static function createUser(\PDO $pdo, array $config): void
    {
        Auth::requireLogin();
=======
    public static function updateSettingsShare(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('settings.manage');

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/settings/share');
        }

        $items = ['system', 'copy', 'wechat', 'qq', 'weibo', 'qzone', 'telegram'];
        $values = [];

        foreach ($items as $key) {
            $enableKey = 'share_enable_' . $key;
            $iconKey = 'share_icon_' . $key;

            $values[$enableKey] = isset($_POST[$enableKey]) ? '1' : '0';

            $iconUrl = trim($_POST[$iconKey] ?? '');
            $uploaded = self::handleUpload($config, false, $iconKey . '_file');
            if ($uploaded) {
                $iconUrl = $uploaded;
            }
            $values[$iconKey] = $iconUrl;
        }

        Settings::setMany($pdo, $values);
        $GLOBALS['settings'] = Settings::all($pdo);

        flash('success', '分享设置已更新。');
        redirect_to('/admin/settings/share');
    }

    public static function updateSettingsMenu(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('settings.manage');

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/settings/menu');
        }

        $fields = [
            'admin_menu_title',
            'admin_menu_title_icon',
            'admin_menu_section_resources_label',
            'admin_menu_section_system_label',
            'admin_menu_dashboard_label',
            'admin_menu_dashboard_icon',
            'admin_menu_stats_label',
            'admin_menu_stats_icon',
            'admin_menu_write_label',
            'admin_menu_write_icon',
            'admin_menu_featured_label',
            'admin_menu_featured_icon',
            'admin_menu_categories_label',
            'admin_menu_categories_icon',
            'admin_menu_tags_label',
            'admin_menu_tags_icon',
            'admin_menu_uploads_label',
            'admin_menu_uploads_icon',
            'admin_menu_cards_label',
            'admin_menu_cards_icon',
            'admin_menu_comments_label',
            'admin_menu_comments_icon',
            'admin_menu_users_label',
            'admin_menu_users_icon',
            'admin_menu_groups_label',
            'admin_menu_groups_icon',
            'admin_menu_settings_label',
            'admin_menu_settings_icon',
            'admin_menu_maintenance_label',
            'admin_menu_maintenance_icon',
        ];

        $values = [];
        foreach ($fields as $field) {
            $values[$field] = trim($_POST[$field] ?? '');
        }

        Settings::setMany($pdo, $values);
        $GLOBALS['settings'] = Settings::all($pdo);

        flash('success', '后台菜单已更新。');
        redirect_to('/admin/settings/menu');
    }

    public static function createUser(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('users.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/users');
        }

        $username = trim($_POST['username'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $password = $_POST['password'] ?? '';
        $isVerified = isset($_POST['is_verified']) ? 1 : 0;
<<<<<<< HEAD
=======
        $groupId = (int)($_POST['group_id'] ?? 0);
        $groupRow = null;
        if ($groupId > 0) {
            $stmt = $pdo->prepare('SELECT id, is_super FROM admin_groups WHERE id = ?');
            $stmt->execute([$groupId]);
            $groupRow = $stmt->fetch();
            if (!$groupRow) {
                $groupId = 0;
            }
        }
        $role = ($groupRow && (int)$groupRow['is_super'] === 1) ? 'admin' : 'editor';
>>>>>>> a3d11b8 (sync: update open-source release)

        if ($username === '' || $password === '') {
            flash('error', '用户名和密码不能为空。');
            redirect_to('/admin/users');
        }

        $avatar = self::handleUpload($config, false, 'avatar_image');
        $badge = self::handleUpload($config, false, 'badge_image');
        $hash = password_hash($password, PASSWORD_DEFAULT);

<<<<<<< HEAD
        $stmt = $pdo->prepare('INSERT INTO users (username, nickname, password_hash, avatar_url, is_verified, verified_badge_url, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$username, $nickname ?: null, $hash, $avatar, $isVerified, $badge]);
=======
        $stmt = $pdo->prepare('INSERT INTO users (username, nickname, password_hash, avatar_url, is_verified, verified_badge_url, role, group_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$username, $nickname ?: null, $hash, $avatar, $isVerified, $badge, $role, $groupId ?: null]);
>>>>>>> a3d11b8 (sync: update open-source release)

        flash('success', '用户已创建。');
        redirect_to('/admin/users');
    }

    public static function updateUser(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('users.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/users');
        }

        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '');
        $password = $_POST['password'] ?? '';
        $isVerified = isset($_POST['is_verified']) ? 1 : 0;
<<<<<<< HEAD
=======
        $groupId = (int)($_POST['group_id'] ?? 0);
        $groupRow = null;
        if ($groupId > 0) {
            $stmt = $pdo->prepare('SELECT id, is_super FROM admin_groups WHERE id = ?');
            $stmt->execute([$groupId]);
            $groupRow = $stmt->fetch();
            if (!$groupRow) {
                $groupId = 0;
            }
        }
        $role = ($groupRow && (int)$groupRow['is_super'] === 1) ? 'admin' : 'editor';
>>>>>>> a3d11b8 (sync: update open-source release)

        if ($id <= 0 || $username === '') {
            flash('error', '用户名不能为空。');
            redirect_to('/admin/users');
        }

        $avatar = self::handleUpload($config, false, 'avatar_image');
        $badge = self::handleUpload($config, false, 'badge_image');

<<<<<<< HEAD
        $fields = ['username' => $username, 'nickname' => ($nickname ?: null), 'is_verified' => $isVerified];
=======
        if ($id === (int)($_SESSION['user_id'] ?? 0) && $role !== 'admin') {
            flash('error', '不能将当前登录账号降为非管理员。');
            redirect_to('/admin/users');
        }

        $fields = [
            'username' => $username,
            'nickname' => ($nickname ?: null),
            'is_verified' => $isVerified,
            'role' => $role,
            'group_id' => $groupId ?: null,
        ];
>>>>>>> a3d11b8 (sync: update open-source release)
        if ($avatar) {
            $fields['avatar_url'] = $avatar;
        }
        if ($badge) {
            $fields['verified_badge_url'] = $badge;
        }

        $set = [];
        $params = [];
        foreach ($fields as $key => $value) {
            $set[] = $key . ' = ?';
            $params[] = $value;
        }
        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE users SET ' . implode(', ', $set) . ' WHERE id = ?');
        $stmt->execute($params);

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $id]);
        }

        flash('success', '用户已更新。');
        redirect_to('/admin/users');
    }

    public static function deleteUser(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('users.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $id = (int)($_GET['id'] ?? 0);

        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            flash('error', '不能删除当前登录账号。');
            redirect_to('/admin/users');
        }

        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        flash('success', '用户已删除。');
        redirect_to('/admin/users');
    }

    public static function handleUploadRequest(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('uploads.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/uploads');
        }

        $path = self::handleUpload($config, true);

        if (!$path) {
            flash('error', '请上传有效的图片文件。');
            redirect_to('/admin/uploads');
        }

        $stmt = $pdo->prepare('INSERT INTO uploads (file_name, file_path, mime_type, size, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([
            basename($path),
            $path,
            $_SESSION['last_upload_mime'] ?? 'image/jpeg',
            $_SESSION['last_upload_size'] ?? 0,
        ]);

        unset($_SESSION['last_upload_mime'], $_SESSION['last_upload_size']);

        flash('success', '上传完成。');
        redirect_to('/admin/uploads');
    }

    public static function editorUpload(\PDO $pdo, array $config): void
    {
        Auth::requireLogin();

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_csrf'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $path = self::handleUpload($config, true, 'image');
        if (!$path) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
<<<<<<< HEAD
            echo json_encode(['error' => 'invalid_image'], JSON_UNESCAPED_UNICODE);
=======
            $error = $_SESSION['last_upload_error'] ?? 'invalid_image';
            echo json_encode(['error' => $error], JSON_UNESCAPED_UNICODE);
>>>>>>> a3d11b8 (sync: update open-source release)
            return;
        }

        $stmt = $pdo->prepare('INSERT INTO uploads (file_name, file_path, mime_type, size, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([
            basename($path),
            $path,
            $_SESSION['last_upload_mime'] ?? 'image/jpeg',
            $_SESSION['last_upload_size'] ?? 0,
        ]);

        unset($_SESSION['last_upload_mime'], $_SESSION['last_upload_size']);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['url' => $path], JSON_UNESCAPED_UNICODE);
    }

    public static function updateUpload(\PDO $pdo): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('uploads.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/admin/uploads');
        }

        $id = (int)($_POST['id'] ?? 0);
        $caption = trim($_POST['caption'] ?? '');

        $stmt = $pdo->prepare('UPDATE uploads SET caption = ? WHERE id = ?');
        $stmt->execute([$caption, $id]);

        flash('success', '素材已更新。');
        redirect_to('/admin/uploads');
    }

<<<<<<< HEAD
    public static function deleteUpload(\PDO $pdo): void
    {
        Auth::requireLogin();
=======
    public static function deleteUpload(\PDO $pdo, array $config): void
    {
        Auth::requirePermission('uploads.manage');
>>>>>>> a3d11b8 (sync: update open-source release)

        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT file_path FROM uploads WHERE id = ?');
        $stmt->execute([$id]);
        $upload = $stmt->fetch();

        if ($upload) {
            $path = $upload['file_path'];
<<<<<<< HEAD
            if (str_starts_with($path, '/')) {
                $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/blog/public', '/') . $path;
=======
            $storageDriver = $config['storage_driver'] ?? 'local';
            if ($storageDriver === 'r2') {
                $r2 = new \R2Client($config['r2'] ?? []);
                if ($r2->isConfigured()) {
                    $key = $r2->keyFromUrl($path);
                    if ($key) {
                        $r2->deleteObject($key);
                    }
                }
            }

            $localPath = null;
            if (str_starts_with($path, '/')) {
                $localPath = $path;
            } else {
                $parsedPath = parse_url($path, PHP_URL_PATH);
                if (is_string($parsedPath) && str_starts_with($parsedPath, '/uploads/')) {
                    $localPath = $parsedPath;
                }
            }

            if ($localPath) {
                $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '/var/www/blog/public', '/') . $localPath;
>>>>>>> a3d11b8 (sync: update open-source release)
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $pdo->prepare('DELETE FROM uploads WHERE id = ?')->execute([$id]);
        }

        flash('success', '素材已删除。');
        redirect_to('/admin/uploads');
    }

    private static function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

<<<<<<< HEAD
=======
    private static function tableExists(\PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    }

    private static function permissionDefinitions(): array
    {
        return [
            'posts.manage_all' => '管理所有文章',
            'comments.manage_all' => '管理所有评论',
            'users.manage' => '管理后台用户',
            'groups.manage' => '管理权限分组',
            'settings.manage' => '管理站点设置',
            'categories.manage' => '管理分类',
            'tags.manage' => '管理标签',
            'uploads.manage' => '管理素材库',
            'featured.manage' => '管理推荐文章',
            'cards.manage' => '管理卡片与推荐',
            'stats.view' => '查看站点统计',
            'maintenance.manage' => '执行维护操作',
        ];
    }

    private static function requirePostOwnerOrAdmin(\PDO $pdo, int $postId): void
    {
        if (Auth::hasPermission('posts.manage_all')) {
            return;
        }

        $stmt = $pdo->prepare('SELECT user_id FROM posts WHERE id = ?');
        $stmt->execute([$postId]);
        $ownerId = (int)($stmt->fetchColumn() ?: 0);

        if ($ownerId !== Auth::userId()) {
            flash('error', '当前账号无权限操作该文章。');
            redirect_to('/admin');
        }
    }

    private static function requireCommentOwnerOrAdmin(\PDO $pdo, int $commentId): void
    {
        if (Auth::hasPermission('comments.manage_all')) {
            return;
        }

        $stmt = $pdo->prepare('SELECT p.user_id FROM comments c INNER JOIN posts p ON p.id = c.post_id WHERE c.id = ?');
        $stmt->execute([$commentId]);
        $ownerId = (int)($stmt->fetchColumn() ?: 0);

        if ($ownerId !== Auth::userId()) {
            flash('error', '当前账号无权限处理该评论。');
            redirect_to('/admin/comments');
        }
    }

>>>>>>> a3d11b8 (sync: update open-source release)
    private static function normalizeBool(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return ((int)$value) ? 1 : 0;
        }
        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($filtered === null) {
            return null;
        }
        return $filtered ? 1 : 0;
    }

<<<<<<< HEAD
=======
    private static function requireTestEnv(array $config): void
    {
        if (!($config['is_test'] ?? false)) {
            http_response_code(404);
            View::render('404', ['config' => $config]);
            exit;
        }
    }

>>>>>>> a3d11b8 (sync: update open-source release)
    private static function nextFeaturedOrder(\PDO $pdo): int
    {
        $row = $pdo->query('SELECT MAX(featured_order) AS max_order FROM posts WHERE is_featured = 1')->fetch();
        $max = (int)($row['max_order'] ?? 0);
        return $max + 1;
    }

    private static function handleUpload(array $config, bool $required = false, string $field = 'featured_image'): ?string
    {
<<<<<<< HEAD
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
=======
        $_SESSION['last_upload_error'] = null;

        if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['last_upload_error'] = 'no_file';
>>>>>>> a3d11b8 (sync: update open-source release)
            return $required ? null : null;
        }

        $file = $_FILES[$field];

        if ($file['error'] !== UPLOAD_ERR_OK) {
<<<<<<< HEAD
=======
            $_SESSION['last_upload_error'] = 'upload_error_' . (string)$file['error'];
>>>>>>> a3d11b8 (sync: update open-source release)
            return null;
        }

        if ($file['size'] > $config['max_upload_bytes']) {
<<<<<<< HEAD
=======
            $_SESSION['last_upload_error'] = 'file_too_large';
>>>>>>> a3d11b8 (sync: update open-source release)
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
<<<<<<< HEAD
=======
            $_SESSION['last_upload_error'] = 'invalid_mime_' . $mime;
>>>>>>> a3d11b8 (sync: update open-source release)
            return null;
        }

        $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
<<<<<<< HEAD
        $destination = rtrim($config['upload_dir'], '/') . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
=======
        $storageDriver = $config['storage_driver'] ?? 'local';

        if ($storageDriver === 'r2') {
            $r2 = new \R2Client($config['r2'] ?? []);
            if (!$r2->isConfigured()) {
                $_SESSION['last_upload_error'] = 'r2_missing_config';
                return null;
            }
            $body = file_get_contents($file['tmp_name']);
            if ($body === false) {
                $_SESSION['last_upload_error'] = 'file_read_failed';
                return null;
            }
            $datePath = date('Y/m/d');
            $key = $r2->buildObjectKey($datePath . '/' . $name);
            if (!$r2->putObject($key, $body, $mime)) {
                $_SESSION['last_upload_error'] = 'r2_upload_failed_' . $r2->lastError();
                return null;
            }
            $_SESSION['last_upload_mime'] = $mime;
            $_SESSION['last_upload_size'] = $file['size'];
            return $r2->publicUrl($key);
        }

        $destination = rtrim($config['upload_dir'], '/') . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['last_upload_error'] = 'move_failed';
>>>>>>> a3d11b8 (sync: update open-source release)
            return null;
        }

        $_SESSION['last_upload_mime'] = $mime;
        $_SESSION['last_upload_size'] = $file['size'];

        return rtrim($config['upload_url'], '/') . '/' . $name;
    }

    private static function syncTags(\PDO $pdo, int $postId, string $tagsRaw): void
    {
        $pdo->prepare('DELETE FROM post_tags WHERE post_id = ?')->execute([$postId]);

        $tags = array_filter(array_map('trim', preg_split('/[,\s]+/', $tagsRaw)));
        foreach ($tags as $tag) {
            $slug = slugify($tag);
            if ($slug === '') {
                $slug = 'tag-' . substr(sha1($tag), 0, 10);
            }
            $stmt = $pdo->prepare('SELECT id FROM tags WHERE slug = ?');
            $stmt->execute([$slug]);
            $existing = $stmt->fetch();

            if ($existing) {
                $tagId = (int)$existing['id'];
            } else {
                $insert = $pdo->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)');
                $insert->execute([$tag, $slug]);
                $tagId = (int)$pdo->lastInsertId();
            }

            $pdo->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)')->execute([$postId, $tagId]);
        }
    }

    public static function cards(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        Auth::requireLogin();
=======
        Auth::requirePermission('cards.manage');
>>>>>>> a3d11b8 (sync: update open-source release)
        
        $posts = $pdo->query('SELECT id, title FROM posts ORDER BY created_at DESC')->fetchAll();
        
        View::render('admin/cards', [
            'config' => $config,
            'posts' => $posts,
            'csrf_token' => Csrf::token(),
        ]);
    }
}
