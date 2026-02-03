<?php

declare(strict_types=1);

final class PublicController
{
    public static function home(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
=======
        http_response_code(200);
>>>>>>> a3d11b8 (sync: update open-source release)
        // Fetch Featured items from featured_cards table
        $featuredStmt = $pdo->prepare('
            SELECT 
                fc.id,
                fc.title AS custom_title,
                fc.link_url,
                fc.image_url,
                p.id AS post_id,
                p.title AS post_title,
                p.slug AS post_slug,
                p.featured_image,
                p.published_at,
                p.created_at,
                COALESCE(u.nickname, u.username) AS author_name, 
                u.avatar_url AS author_avatar,
                u.is_verified AS author_verified,
                u.verified_badge_url AS author_badge,
                c.name AS category_name,
                c.slug AS category_slug
            FROM featured_cards fc
            LEFT JOIN posts p ON fc.post_id = p.id
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY fc.sort_order DESC, fc.created_at DESC
        ');
        $featuredStmt->execute();
        $featuredRaw = $featuredStmt->fetchAll();
        
        $featuredPosts = [];
        foreach ($featuredRaw as $row) {
            $featuredPosts[] = [
                'title' => $row['custom_title'] ?: $row['post_title'],
                'image' => $row['image_url'] ?: $row['featured_image'],
                'link'  => $row['link_url'] ?: ($row['post_slug'] ? '/post/' . $row['post_slug'] : '#'),
                'date'  => $row['published_at'] ?: $row['created_at'],
                'author_name' => $row['author_name'],
                'author_avatar' => $row['author_avatar'],
                'author_verified' => $row['author_verified'],
                'author_badge' => $row['author_badge'],
                'category_name' => $row['category_name'],
                'category_slug' => $row['category_slug'],
                'is_custom' => empty($row['post_id'])
            ];
        }

        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN featured_cards fc ON fc.post_id = p.id LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = ? AND fc.id IS NULL ORDER BY p.published_at DESC');
        $stmt->execute(['published']);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) {
            $post['tags'] = self::tagsForPost($pdo, (int)$post['id']);
        }

        $categories = self::allCategories($pdo);
        $tags = self::allTags($pdo);

        View::render('home', [
            'config' => $config,
            'featured_posts' => $featuredPosts,
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public static function viewPost(\PDO $pdo, array $config, string $slug): void
    {
        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE LOWER(p.slug) = LOWER(?) AND p.status = ?');
        $stmt->execute([$slug, 'published']);
        $post = $stmt->fetch();

        if (!$post) {
            http_response_code(404);
            View::render('404', ['config' => $config]);
            return;
        }

        $tags = self::tagsForPost($pdo, (int)$post['id']);
        $comments = self::approvedComments($pdo, (int)$post['id']);

        View::render('post', [
            'config' => $config,
            'post' => $post,
            'tags' => $tags,
            'comments' => $comments,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public static function postCards(\PDO $pdo, string $slug): void
    {
        $postStmt = $pdo->prepare('SELECT id FROM posts WHERE LOWER(slug) = LOWER(?) AND status = ?');
        $postStmt->execute([$slug, 'published']);
        $post = $postStmt->fetch();

        if (!$post) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'not_found'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $stmt = $pdo->prepare('SELECT id, title, description, image_url, link_url, priority FROM post_cards WHERE post_id = ? AND is_active = 1 ORDER BY priority DESC, id DESC');
        $stmt->execute([(int)$post['id']]);
        $cards = $stmt->fetchAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['cards' => $cards], JSON_UNESCAPED_UNICODE);
    }

    public static function category(\PDO $pdo, array $config, string $slug): void
    {
        $categoryStmt = $pdo->prepare('SELECT * FROM categories WHERE LOWER(slug) = LOWER(?)');
        $categoryStmt->execute([$slug]);
        $category = $categoryStmt->fetch();

        if (!$category) {
            http_response_code(404);
            View::render('404', ['config' => $config]);
            return;
        }

        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = ? AND p.category_id = ? ORDER BY p.published_at DESC');
        $stmt->execute(['published', $category['id']]);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) {
            $post['tags'] = self::tagsForPost($pdo, (int)$post['id']);
        }

        View::render('category', [
            'config' => $config,
            'category' => $category,
            'posts' => $posts,
        ]);
    }

    public static function tag(\PDO $pdo, array $config, string $slug): void
    {
        $tagStmt = $pdo->prepare('SELECT * FROM tags WHERE LOWER(slug) = LOWER(?)');
        $tagStmt->execute([$slug]);
        $tag = $tagStmt->fetch();

        if (!$tag) {
            http_response_code(404);
            View::render('404', ['config' => $config]);
            return;
        }

        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p INNER JOIN post_tags pt ON pt.post_id = p.id LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE pt.tag_id = ? AND p.status = ? ORDER BY p.published_at DESC');
        $stmt->execute([$tag['id'], 'published']);
        $posts = $stmt->fetchAll();

        foreach ($posts as &$post) {
            $post['tags'] = self::tagsForPost($pdo, (int)$post['id']);
        }

        View::render('tag', [
            'config' => $config,
            'tag' => $tag,
            'posts' => $posts,
        ]);
    }

    public static function submitComment(\PDO $pdo, array $config, string $slug): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            flash('error', '请求已过期，请刷新后再试。');
            redirect_to('/post/' . $slug);
        }

        $stmt = $pdo->prepare('SELECT id FROM posts WHERE LOWER(slug) = LOWER(?) AND status = ?');
        $stmt->execute([$slug, 'published']);
        $post = $stmt->fetch();

        if (!$post) {
            http_response_code(404);
            View::render('404', ['config' => $config]);
            return;
        }

        $author = trim($_POST['author'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($author === '' || $email === '' || $content === '') {
            flash('error', '请完整填写评论信息。');
            redirect_to('/post/' . $slug);
        }

        $insert = $pdo->prepare('INSERT INTO comments (post_id, author_name, author_email, content, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $insert->execute([(int)$post['id'], $author, $email, $content, 'pending']);

        flash('success', '评论已提交，等待审核。');
        redirect_to('/post/' . $slug);
    }

    public static function search(\PDO $pdo, array $config): void
    {
        $query = trim($_GET['q'] ?? '');
        $posts = [];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $total = 0;

        if ($query !== '') {
            $like = '%' . $query . '%';
            $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM posts WHERE status = ? AND (title LIKE ? OR content_md LIKE ? OR excerpt LIKE ?)');
            $countStmt->execute(['published', $like, $like, $like]);
            $total = (int)($countStmt->fetch()['total'] ?? 0);

            $offset = ($page - 1) * $perPage;
            $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = ? AND (p.title LIKE ? OR p.content_md LIKE ? OR p.excerpt LIKE ?) ORDER BY p.published_at DESC LIMIT ? OFFSET ?');
            $stmt->bindValue(1, 'published');
            $stmt->bindValue(2, $like);
            $stmt->bindValue(3, $like);
            $stmt->bindValue(4, $like);
            $stmt->bindValue(5, (int)$perPage, \PDO::PARAM_INT);
            $stmt->bindValue(6, (int)$offset, \PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll();
        }

        View::render('search', [
            'config' => $config,
            'query' => $query,
            'posts' => $posts,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    public static function apiHome(\PDO $pdo, array $config): void
    {
        [$page, $perPage, $offset] = self::apiPagination();
        $settings = self::publicSettings($config);

        $featuredStmt = $pdo->prepare('
            SELECT 
                fc.id,
                fc.title AS custom_title,
                fc.link_url,
                fc.image_url,
                p.id AS post_id,
                p.title AS post_title,
                p.slug AS post_slug,
                p.featured_image,
                p.published_at,
                p.created_at
            FROM featured_cards fc
            LEFT JOIN posts p ON fc.post_id = p.id
            ORDER BY fc.sort_order DESC, fc.created_at DESC
        ');
        $featuredStmt->execute();
        $featuredRaw = $featuredStmt->fetchAll();
        $featured = [];
        foreach ($featuredRaw as $row) {
            $image = $row['image_url'] ?: $row['featured_image'];
            $link = $row['link_url'] ?: ($row['post_slug'] ? '/post/' . $row['post_slug'] : '');
            $featured[] = [
                'id' => (int)$row['id'],
                'post_id' => $row['post_id'] ? (int)$row['post_id'] : null,
                'title' => $row['custom_title'] ?: $row['post_title'],
                'image' => self::absoluteUrl($config, $image),
                'link' => self::normalizeLink($config, $link),
                'published_at' => $row['published_at'] ?: $row['created_at'],
            ];
        }

        $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM posts p LEFT JOIN featured_cards fc ON fc.post_id = p.id WHERE p.status = ? AND fc.id IS NULL');
        $countStmt->execute(['published']);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN featured_cards fc ON fc.post_id = p.id LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = ? AND fc.id IS NULL ORDER BY p.published_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, 'published');
        $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $postsRaw = $stmt->fetchAll();

        $posts = array_map(fn($post) => self::apiPostSummary($post, $config), $postsRaw);

        self::jsonResponse([
            'settings' => $settings,
            'featured' => $featured,
            'posts' => $posts,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    public static function apiPosts(\PDO $pdo, array $config): void
    {
        [$page, $perPage, $offset] = self::apiPagination();

        $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM posts WHERE status = ?');
        $countStmt->execute(['published']);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = ? ORDER BY p.published_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, 'published');
        $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $postsRaw = $stmt->fetchAll();

        $posts = array_map(fn($post) => self::apiPostSummary($post, $config), $postsRaw);

        self::jsonResponse([
            'posts' => $posts,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    public static function apiPost(\PDO $pdo, array $config, string $slug): void
    {
        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE LOWER(p.slug) = LOWER(?) AND p.status = ?');
        $stmt->execute([$slug, 'published']);
        $post = $stmt->fetch();

        if (!$post) {
            self::jsonResponse(['error' => 'not_found'], 404);
            return;
        }

        if (!empty($_GET['track'])) {
            track_page_view((int)$post['id']);
        }

        $tags = self::tagsForPost($pdo, (int)$post['id']);
        $comments = self::approvedComments($pdo, (int)$post['id']);

        $commentList = [];
        foreach ($comments as $comment) {
            $commentList[] = [
                'id' => (int)$comment['id'],
                'author' => $comment['author_name'],
                'content' => $comment['content'],
                'created_at' => $comment['created_at'],
            ];
        }

        $data = self::apiPostSummary($post, $config);
        $data['content_md'] = $post['content_md'];
        $contentHtml = Markdown::render($post['content_md']);
        $data['content_html'] = self::normalizeHtmlAssets($config, $contentHtml);
        $data['tags'] = array_map(fn($tag) => ['id' => (int)$tag['id'], 'name' => $tag['name'], 'slug' => $tag['slug']], $tags);
        $data['comments'] = $commentList;

        self::jsonResponse($data);
    }

    public static function apiCategories(\PDO $pdo, array $config): void
    {
        $categories = self::allCategories($pdo);
        $data = array_map(fn($category) => [
            'id' => (int)$category['id'],
            'name' => $category['name'],
            'slug' => $category['slug'],
        ], $categories);

        self::jsonResponse(['categories' => $data]);
    }

    public static function apiTags(\PDO $pdo, array $config): void
    {
        $tags = self::allTags($pdo);
        $data = array_map(fn($tag) => [
            'id' => (int)$tag['id'],
            'name' => $tag['name'],
            'slug' => $tag['slug'],
            'usage_count' => (int)($tag['usage_count'] ?? 0),
        ], $tags);

        self::jsonResponse(['tags' => $data]);
    }

    public static function apiCategory(\PDO $pdo, array $config, string $slug): void
    {
        $categoryStmt = $pdo->prepare('SELECT * FROM categories WHERE LOWER(slug) = LOWER(?)');
        $categoryStmt->execute([$slug]);
        $category = $categoryStmt->fetch();

        if (!$category) {
            self::jsonResponse(['error' => 'not_found'], 404);
            return;
        }

        [$page, $perPage, $offset] = self::apiPagination();

        $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM posts WHERE status = ? AND category_id = ?');
        $countStmt->execute(['published', $category['id']]);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = ? AND p.category_id = ? ORDER BY p.published_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, 'published');
        $stmt->bindValue(2, (int)$category['id'], \PDO::PARAM_INT);
        $stmt->bindValue(3, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(4, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $postsRaw = $stmt->fetchAll();

        $posts = array_map(fn($post) => self::apiPostSummary($post, $config), $postsRaw);

        self::jsonResponse([
            'category' => [
                'id' => (int)$category['id'],
                'name' => $category['name'],
                'slug' => $category['slug'],
            ],
            'posts' => $posts,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    public static function apiTag(\PDO $pdo, array $config, string $slug): void
    {
        $tagStmt = $pdo->prepare('SELECT * FROM tags WHERE LOWER(slug) = LOWER(?)');
        $tagStmt->execute([$slug]);
        $tag = $tagStmt->fetch();

        if (!$tag) {
            self::jsonResponse(['error' => 'not_found'], 404);
            return;
        }

        [$page, $perPage, $offset] = self::apiPagination();

        $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM posts p INNER JOIN post_tags pt ON pt.post_id = p.id WHERE p.status = ? AND pt.tag_id = ?');
        $countStmt->execute(['published', $tag['id']]);
        $total = (int)($countStmt->fetch()['total'] ?? 0);

        $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p INNER JOIN post_tags pt ON pt.post_id = p.id LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE pt.tag_id = ? AND p.status = ? ORDER BY p.published_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, (int)$tag['id'], \PDO::PARAM_INT);
        $stmt->bindValue(2, 'published');
        $stmt->bindValue(3, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(4, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $postsRaw = $stmt->fetchAll();

        $posts = array_map(fn($post) => self::apiPostSummary($post, $config), $postsRaw);

        self::jsonResponse([
            'tag' => [
                'id' => (int)$tag['id'],
                'name' => $tag['name'],
                'slug' => $tag['slug'],
            ],
            'posts' => $posts,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    public static function apiSearch(\PDO $pdo, array $config): void
    {
        $query = trim($_GET['q'] ?? '');
        [$page, $perPage, $offset] = self::apiPagination();
        $posts = [];
        $total = 0;

        if ($query !== '') {
            $like = '%' . $query . '%';
            $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM posts WHERE status = ? AND (title LIKE ? OR content_md LIKE ? OR excerpt LIKE ?)');
            $countStmt->execute(['published', $like, $like, $like]);
            $total = (int)($countStmt->fetch()['total'] ?? 0);

            $stmt = $pdo->prepare('SELECT p.*, COALESCE(u.nickname, u.username) AS author_name, u.avatar_url AS author_avatar, u.is_verified AS author_verified, u.verified_badge_url AS author_badge, c.name AS category_name, c.slug AS category_slug FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = ? AND (p.title LIKE ? OR p.content_md LIKE ? OR p.excerpt LIKE ?) ORDER BY p.published_at DESC LIMIT ? OFFSET ?');
            $stmt->bindValue(1, 'published');
            $stmt->bindValue(2, $like);
            $stmt->bindValue(3, $like);
            $stmt->bindValue(4, $like);
            $stmt->bindValue(5, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(6, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $postsRaw = $stmt->fetchAll();
            $posts = array_map(fn($post) => self::apiPostSummary($post, $config), $postsRaw);
        }

        self::jsonResponse([
            'query' => $query,
            'posts' => $posts,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    public static function submitCommentApi(\PDO $pdo, array $config, string $slug): void
    {
        $input = self::readJsonBody();
        $author = trim((string)($input['author'] ?? $_POST['author'] ?? ''));
        $email = trim((string)($input['email'] ?? $_POST['email'] ?? ''));
        $content = trim((string)($input['content'] ?? $_POST['content'] ?? ''));

        if ($author === '') {
            $author = '匿名';
        }
        if ($email === '') {
            $email = 'miniapp@invalid.local';
        }

        if ($content === '') {
            self::jsonResponse(['error' => 'empty_content'], 400);
            return;
        }

        $length = function_exists('mb_strlen') ? mb_strlen($content, 'UTF-8') : strlen($content);
        if ($length > 2000) {
            self::jsonResponse(['error' => 'content_too_long'], 400);
            return;
        }

        $postStmt = $pdo->prepare('SELECT id FROM posts WHERE LOWER(slug) = LOWER(?) AND status = ?');
        $postStmt->execute([$slug, 'published']);
        $post = $postStmt->fetch();

        if (!$post) {
            self::jsonResponse(['error' => 'not_found'], 404);
            return;
        }

        $insert = $pdo->prepare('INSERT INTO comments (post_id, author_name, author_email, content, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $insert->execute([(int)$post['id'], $author, $email, $content, 'pending']);

        self::jsonResponse(['status' => 'ok']);
    }

    public static function apiNotFound(): void
    {
        self::jsonResponse(['error' => 'not_found'], 404);
    }

    public static function sitemap(\PDO $pdo, array $config): void
    {
<<<<<<< HEAD
        $stmt = $pdo->query('SELECT slug, updated_at FROM posts WHERE status = \"published\" ORDER BY published_at DESC');
=======
        $stmt = $pdo->prepare('SELECT slug, updated_at FROM posts WHERE status = ? ORDER BY published_at DESC');
        $stmt->execute(['published']);
>>>>>>> a3d11b8 (sync: update open-source release)
        $posts = $stmt->fetchAll();
        $categories = $pdo->query('SELECT slug FROM categories ORDER BY name ASC')->fetchAll();
        $tags = $pdo->query('SELECT slug FROM tags ORDER BY name ASC')->fetchAll();

        header('Content-Type: application/xml; charset=utf-8');

        View::render('sitemap', [
            'config' => $config,
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public static function robots(array $config): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        View::render('robots', [
            'config' => $config,
        ]);
    }

    public static function wechatSignature(array $config): void
    {
        $url = trim((string)($_GET['url'] ?? ''));
        if ($url === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'missing_url'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $url = rawurldecode($url);
        $url = explode('#', $url, 2)[0];

        $host = parse_url($url, PHP_URL_HOST);
        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($host && $currentHost && strcasecmp($host, $currentHost) !== 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'invalid_host'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $signature = WeChat::signature($config, $url);
        if ($signature === null) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'not_configured'], JSON_UNESCAPED_UNICODE);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($signature, JSON_UNESCAPED_UNICODE);
    }

    public static function rss(\PDO $pdo, array $config): void
    {
        $stmt = $pdo->prepare('SELECT id, title, slug, excerpt, content_md, published_at, created_at, updated_at FROM posts WHERE status = ? ORDER BY published_at DESC LIMIT 50');
        $stmt->execute(['published']);
        $posts = $stmt->fetchAll();

        $lastBuild = date('c');
        if (!empty($posts)) {
            $latest = $posts[0]['updated_at'] ?? $posts[0]['published_at'] ?? $posts[0]['created_at'];
            if (!empty($latest)) {
                $lastBuild = date('c', strtotime($latest));
            }
        }

        header('Content-Type: application/rss+xml; charset=utf-8');
        View::render('rss', [
            'config' => $config,
            'posts' => $posts,
            'last_build' => $lastBuild,
        ]);
    }

    public static function ogImage(\PDO $pdo, array $config, int $id): void
    {
        $stmt = $pdo->prepare('SELECT title, published_at, COALESCE(u.nickname, u.username) as author_name FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.status = ?');
        $stmt->execute([$id, 'published']);
        $post = $stmt->fetch();

        if (!$post) {
            http_response_code(404);
            die('Not Found');
        }

        $width = 1200;
        $height = 630;
        $im = imagecreatetruecolor($width, $height);

        // Colors
        $bg = imagecolorallocate($im, 20, 20, 23); // Dark background #141417
        $textMain = imagecolorallocate($im, 255, 255, 255);
        $textSub = imagecolorallocate($im, 160, 160, 160);
        $accent = imagecolorallocate($im, 50, 140, 250); // Blue accent

        // Fill Background
        imagefilledrectangle($im, 0, 0, $width, $height, $bg);

        // Draw Decoration (Simple Gradient-ish circles)
        $circleColor = imagecolorallocatealpha($im, 50, 140, 250, 110);
        imagefilledellipse($im, $width, 0, 800, 800, $circleColor);
        
        $circleColor2 = imagecolorallocatealpha($im, 180, 50, 250, 115);
        imagefilledellipse($im, 0, $height, 600, 600, $circleColor2);

        // Font Path
        $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $fontRegular = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        
        // Site Name
        $siteName = ($config['site_name'] ?? 'Blog') . ' - Article';
        if (file_exists($fontPath)) {
            imagettftext($im, 24, 0, 60, 80, $accent, $fontPath, $siteName);
        } else {
            imagestring($im, 5, 60, 60, $siteName, $accent);
        }

        // Title (Word Wrap logic)
        $title = $post['title'];
        $fontSize = 50;
        $x = 60;
        $y = 250;
        $maxWidth = 1080;
        
        if (file_exists($fontPath)) {
            $words = explode(' ', $title);
            // Simple character Split for Chinese support if no spaces
            if (count($words) === 1 && mb_strlen($title) > 10) {
                 $chars = mb_str_split($title);
                 $lines = [];
                 $currentLine = '';
                 foreach ($chars as $char) {
                     $bbox = imagettfbbox($fontSize, 0, $fontPath, $currentLine . $char);
                     if ($bbox[2] - $bbox[0] > $maxWidth) {
                         $lines[] = $currentLine;
                         $currentLine = $char;
                     } else {
                         $currentLine .= $char;
                     }
                 }
                 $lines[] = $currentLine;
            } else {
                 // Basic English split logic (can be improved)
                 $lines = [$title]; 
            }
            
            foreach ($lines as $line) {
                imagettftext($im, $fontSize, 0, $x, $y, $textMain, $fontPath, $line);
                $y += 90; // Line height
            }
            
        } else {
            imagestring($im, 5, $x, $y, $title, $textMain);
            $y += 40;
        }

        // Meta Info (Author | Date)
        $meta = $post['author_name'] . '  •  ' . date('F j, Y', strtotime($post['published_at'] ?? 'now'));
        $metaY = $height - 80;
        
        if (file_exists($fontRegular)) {
            imagettftext($im, 20, 0, 60, $metaY, $textSub, $fontRegular, $meta);
        } elseif (file_exists($fontPath)) {
            imagettftext($im, 20, 0, 60, $metaY, $textSub, $fontPath, $meta);
        } else {
            imagestring($im, 4, 60, $metaY, $meta, $textSub);
        }

        header('Content-Type: image/png');
        // Cache for 1 hour
        $ts = gmdate("D, d M Y H:i:s", time() + 3600) . " GMT";
        header("Expires: $ts");
        header("Pragma: cache");
        header("Cache-Control: max-age=3600");
        
        imagepng($im);
        imagedestroy($im);
    }

    private static function tagsForPost(\PDO $pdo, int $postId): array
    {
        $stmt = $pdo->prepare('SELECT t.* FROM tags t INNER JOIN post_tags pt ON pt.tag_id = t.id WHERE pt.post_id = ? ORDER BY t.name ASC');
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    private static function approvedComments(\PDO $pdo, int $postId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM comments WHERE post_id = ? AND status = ? ORDER BY created_at DESC');
        $stmt->execute([$postId, 'approved']);
        return $stmt->fetchAll();
    }

    private static function allCategories(\PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    private static function allTags(\PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT t.*, COUNT(pt.post_id) AS usage_count FROM tags t LEFT JOIN post_tags pt ON pt.tag_id = t.id GROUP BY t.id HAVING usage_count > 0 ORDER BY t.name ASC');
        return $stmt->fetchAll();
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private static function apiPagination(): array
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }
        $perPage = min(20, $perPage);
        $offset = ($page - 1) * $perPage;
        return [$page, $perPage, $offset];
    }

    private static function apiPostSummary(array $post, array $config): array
    {
        $excerpt = $post['excerpt'] ?: snippet(markdown_plaintext($post['content_md']), 120);
        $publishedAt = $post['published_at'] ?? $post['created_at'];
        $featuredImage = self::absoluteUrl($config, $post['featured_image'] ?? null);
        $authorAvatar = self::absoluteUrl($config, $post['author_avatar'] ?? null);
        $authorBadge = self::absoluteUrl($config, $post['author_badge'] ?? null);

        return [
            'id' => (int)$post['id'],
            'title' => $post['title'],
            'slug' => $post['slug'],
            'excerpt' => $excerpt,
            'featured_image' => $featuredImage,
            'published_at' => $publishedAt,
            'created_at' => $post['created_at'],
            'updated_at' => $post['updated_at'],
            'category' => $post['category_name'] ? [
                'name' => $post['category_name'],
                'slug' => $post['category_slug'],
            ] : null,
            'author' => [
                'name' => $post['author_name'] ?? '管理员',
                'avatar' => $authorAvatar,
                'verified' => !empty($post['author_verified']),
                'badge' => $authorBadge,
            ],
            'url' => url_for($config, '/post/' . $post['slug']),
        ];
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

    private static function publicSettings(array $config): array
    {
        $defaults = Settings::defaults();
        $settings = $defaults;
        if (isset($GLOBALS['settings']) && is_array($GLOBALS['settings'])) {
            $settings = array_merge($settings, $GLOBALS['settings']);
        }

        return [
            'site_name' => $settings['site_name'] ?? $defaults['site_name'],
            'hero_title' => $settings['hero_title'] ?? $defaults['hero_title'],
            'hero_subtitle' => $settings['hero_subtitle'] ?? $defaults['hero_subtitle'],
            'hero_mode' => $settings['hero_mode'] ?? $defaults['hero_mode'],
            'hero_image_url' => self::absoluteUrl($config, $settings['hero_image_url'] ?? ''),
            'show_hero' => ($settings['show_hero'] ?? '1') === '1',
        ];
    }

    private static function normalizeHtmlAssets(array $config, string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $html = preg_replace_callback('/<img\\b[^>]*>/i', function (array $matches) use ($config): string {
            $tag = $matches[0];
            return preg_replace_callback('/\\bsrc\\s*=\\s*(["\\\']?)([^"\\\'>\\s]+)\\1/i', function (array $srcMatch) use ($config): string {
                $src = $srcMatch[2];
                if ($src === '' || preg_match('#^(https?:)?//#i', $src) || str_starts_with($src, 'data:')) {
                    return $srcMatch[0];
                }
                $absolute = self::absoluteUrl($config, $src);
                if ($absolute === null) {
                    return $srcMatch[0];
                }
                $quote = $srcMatch[1] !== '' ? $srcMatch[1] : '"';
                return 'src=' . $quote . $absolute . $quote;
            }, $tag) ?? $tag;
        }, $html) ?? $html;

        return $html;
    }

    private static function absoluteUrl(array $config, ?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        if (str_starts_with($path, '//')) {
            return $path;
        }
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        return url_for($config, $path);
    }

    private static function normalizeLink(array $config, string $link): string
    {
        if ($link === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $link)) {
            return $link;
        }
        if (str_starts_with($link, '//')) {
            return $link;
        }
        if (!str_starts_with($link, '/')) {
            $link = '/' . $link;
        }
        return url_for($config, $link);
    }
}
