<?php

declare(strict_types=1);

$config = require __DIR__ . '/../app/config/config.php';

date_default_timezone_set('Asia/Shanghai');

require __DIR__ . '/../app/lib/Db.php';
require __DIR__ . '/../app/lib/Helpers.php';
require __DIR__ . '/../app/lib/Markdown.php';
require __DIR__ . '/../app/lib/View.php';
require __DIR__ . '/../app/lib/Csrf.php';
require __DIR__ . '/../app/lib/Auth.php';
require __DIR__ . '/../app/lib/Summary.php';
require __DIR__ . '/../app/lib/Settings.php';

require __DIR__ . '/../app/controllers/PublicController.php';
require __DIR__ . '/../app/controllers/AuthController.php';
require __DIR__ . '/../app/controllers/AdminController.php';

session_name($config['session_name']);
session_start();

$pdo = Db::pdo($config);
$GLOBALS['pdo'] = $pdo;
$GLOBALS['settings'] = Settings::all($pdo);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($path === '/' && $method === 'GET') {
    PublicController::home($pdo, $config);
    return;
}

if (preg_match('#^/post/([^/]+)$#', $path, $matches) && $method === 'GET') {
    PublicController::viewPost($pdo, $config, rawurldecode($matches[1]));
    return;
}

if (preg_match('#^/post/([^/]+)/cards$#', $path, $matches) && $method === 'GET') {
    PublicController::postCards($pdo, rawurldecode($matches[1]));
    return;
}

if (preg_match('#^/post/([^/]+)/comment$#', $path, $matches) && $method === 'POST') {
    PublicController::submitComment($pdo, $config, rawurldecode($matches[1]));
    return;
}

if (preg_match('#^/og/image/(\d+)$#', $path, $matches) && $method === 'GET') {
    PublicController::ogImage($pdo, $config, (int)$matches[1]);
    return;
}

if (preg_match('#^/category/([^/]+)$#', $path, $matches) && $method === 'GET') {
    PublicController::category($pdo, $config, rawurldecode($matches[1]));
    return;
}

if (preg_match('#^/tag/([^/]+)$#', $path, $matches) && $method === 'GET') {
    PublicController::tag($pdo, $config, rawurldecode($matches[1]));
    return;
}

if ($path === '/search' && $method === 'GET') {
    PublicController::search($pdo, $config);
    return;
}

if ($path === '/sitemap.xml' && $method === 'GET') {
    PublicController::sitemap($pdo, $config);
    return;
}

if ($path === '/robots.txt' && $method === 'GET') {
    PublicController::robots($config);
    return;
}

if ($path === '/rss.xml' && $method === 'GET') {
    PublicController::rss($pdo, $config);
    return;
}

if ($path === '/admin/login' && $method === 'GET') {
    AuthController::loginForm($config);
    return;
}

if ($path === '/admin/login' && $method === 'POST') {
    AuthController::login($pdo);
    return;
}

if ($path === '/admin/logout' && $method === 'GET') {
    AuthController::logout();
    return;
}

if ($path === '/admin' && $method === 'GET') {
    AdminController::dashboard($pdo, $config);
    return;
}

if ($path === '/admin/posts/new' && $method === 'GET') {
    AdminController::newPostForm($pdo, $config);
    return;
}

if ($path === '/admin/posts/create' && $method === 'POST') {
    AdminController::createPost($pdo, $config);
    return;
}

if ($path === '/admin/posts/edit' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::editPostForm($pdo, $config, $id);
    return;
}

if ($path === '/admin/posts/update' && $method === 'POST') {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::updatePost($pdo, $config, $id);
    return;
}

if ($path === '/admin/posts/summary' && $method === 'POST') {
    AdminController::generateSummary($config);
    return;
}

if ($path === '/admin/featured-posts' && $method === 'GET') {
    AdminController::featuredPosts($pdo);
    return;
}

if ($path === '/admin/featured' && $method === 'GET') {
    AdminController::featuredPage($pdo, $config);
    return;
}

if ($path === '/admin/featured-posts/add' && $method === 'POST') {
    AdminController::addFeaturedItem($pdo);
    return;
}

if ($path === '/admin/featured-posts/remove' && $method === 'POST') {
    AdminController::removeFeaturedItem($pdo);
    return;
}

if ($path === '/admin/featured-posts/reorder' && $method === 'POST') {
    AdminController::reorderFeaturedItems($pdo);
    return;
}

if ($path === '/admin/featured-posts/update' && $method === 'POST') {
    AdminController::updateFeaturedItem($pdo);
    return;
}

if ($path === '/admin/post-cards' && $method === 'GET') {
    AdminController::postCards($pdo);
    return;
}

if ($path === '/admin/post-cards/create' && $method === 'POST') {
    AdminController::createPostCard($pdo, $config);
    return;
}

if ($path === '/admin/post-cards/update' && $method === 'POST') {
    AdminController::updatePostCard($pdo, $config);
    return;
}

if ($path === '/admin/post-cards/delete' && $method === 'POST') {
    AdminController::deletePostCard($pdo);
    return;
}

if ($path === '/admin/posts/delete' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deletePost($pdo, $id);
    return;
}

if ($path === '/admin/categories' && $method === 'GET') {
    AdminController::categories($pdo, $config);
    return;
}

if ($path === '/admin/categories/create' && $method === 'POST') {
    AdminController::createCategory($pdo);
    return;
}

if ($path === '/admin/categories/delete' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deleteCategory($pdo, $id);
    return;
}

if ($path === '/admin/tags' && $method === 'GET') {
    AdminController::tags($pdo, $config);
    return;
}

if ($path === '/admin/tags/create' && $method === 'POST') {
    AdminController::createTag($pdo);
    return;
}

if ($path === '/admin/tags/delete' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deleteTag($pdo, $id);
    return;
}

if ($path === '/admin/comments' && $method === 'GET') {
    AdminController::comments($pdo, $config);
    return;
}

if ($path === '/admin/comments/approve' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::approveComment($pdo, $id);
    return;
}

if ($path === '/admin/comments/delete' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deleteComment($pdo, $id);
    return;
}

if ($path === '/admin/uploads' && $method === 'GET') {
    AdminController::uploads($pdo, $config);
    return;
}

if ($path === '/admin/uploads/create' && $method === 'POST') {
    AdminController::handleUploadRequest($pdo, $config);
    return;
}

if ($path === '/admin/uploads/editor' && $method === 'POST') {
    AdminController::editorUpload($pdo, $config);
    return;
}

if ($path === '/admin/users' && $method === 'GET') {
    AdminController::users($pdo, $config);
    return;
}

if ($path === '/admin/users/create' && $method === 'POST') {
    AdminController::createUser($pdo, $config);
    return;
}

if ($path === '/admin/users/update' && $method === 'POST') {
    AdminController::updateUser($pdo, $config);
    return;
}

if ($path === '/admin/users/delete' && $method === 'GET') {
    AdminController::deleteUser($pdo);
    return;
}

if ($path === '/admin/settings' && $method === 'GET') {
    AdminController::settings($config);
    return;
}

if ($path === '/admin/settings' && $method === 'POST') {
    AdminController::updateSettings($pdo);
    return;
}

if ($path === '/admin/uploads/update' && $method === 'POST') {
    AdminController::updateUpload($pdo);
    return;
}

if ($path === '/admin/uploads/delete' && $method === 'GET') {
    AdminController::deleteUpload($pdo);
    return;
}

if ($path === '/admin/cards' && $method === 'GET') {
    AdminController::cards($pdo, $config);
    return;
}

if ($path === '/admin/stats' && $method === 'GET') {
    AdminController::stats($pdo, $config);
    return;
}

http_response_code(404);
View::render('404', ['config' => $config]);
