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
require __DIR__ . '/../app/lib/WeChat.php';
<<<<<<< HEAD
=======
require __DIR__ . '/../app/lib/R2.php';
>>>>>>> a3d11b8 (sync: update open-source release)

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

<<<<<<< HEAD
if ($path === '/' && $method === 'GET') {
=======
if ($path === '/' && ($method === 'GET' || $method === 'HEAD')) {
    http_response_code(200);
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::home($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/api/home' && $method === 'GET') {
=======
if ($path === '/api/home' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiHome($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/api/posts' && $method === 'GET') {
=======
if ($path === '/api/posts' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiPosts($pdo, $config);
    return;
}

if (preg_match('#^/api/post/([^/]+)/comment$#', $path, $matches) && $method === 'POST') {
    PublicController::submitCommentApi($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if (preg_match('#^/api/post/([^/]+)$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/api/post/([^/]+)$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiPost($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if ($path === '/api/categories' && $method === 'GET') {
=======
if ($path === '/api/categories' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiCategories($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/api/tags' && $method === 'GET') {
=======
if ($path === '/api/tags' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiTags($pdo, $config);
    return;
}

<<<<<<< HEAD
if (preg_match('#^/api/category/([^/]+)$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/api/category/([^/]+)$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiCategory($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if (preg_match('#^/api/tag/([^/]+)$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/api/tag/([^/]+)$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiTag($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if ($path === '/api/search' && $method === 'GET') {
=======
if ($path === '/api/search' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::apiSearch($pdo, $config);
    return;
}

<<<<<<< HEAD
if (preg_match('#^/post/([^/]+)$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/post/([^/]+)$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::viewPost($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if (preg_match('#^/post/([^/]+)/cards$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/post/([^/]+)/cards$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::postCards($pdo, rawurldecode($matches[1]));
    return;
}

if (preg_match('#^/post/([^/]+)/comment$#', $path, $matches) && $method === 'POST') {
    PublicController::submitComment($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if (preg_match('#^/og/image/(\d+)$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/og/image/(\d+)$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::ogImage($pdo, $config, (int)$matches[1]);
    return;
}

<<<<<<< HEAD
if (preg_match('#^/category/([^/]+)$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/category/([^/]+)$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::category($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if (preg_match('#^/tag/([^/]+)$#', $path, $matches) && $method === 'GET') {
=======
if (preg_match('#^/tag/([^/]+)$#', $path, $matches) && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::tag($pdo, $config, rawurldecode($matches[1]));
    return;
}

<<<<<<< HEAD
if ($path === '/search' && $method === 'GET') {
=======
if ($path === '/search' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::search($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/sitemap.xml' && $method === 'GET') {
=======
if ($path === '/sitemap.xml' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::sitemap($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/robots.txt' && $method === 'GET') {
=======
if ($path === '/robots.txt' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::robots($config);
    return;
}

<<<<<<< HEAD
if ($path === '/wechat/signature' && $method === 'GET') {
=======
if ($path === '/wechat/signature' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::wechatSignature($config);
    return;
}

<<<<<<< HEAD
if ($path === '/rss.xml' && $method === 'GET') {
=======
if ($path === '/rss.xml' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    PublicController::rss($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/login' && $method === 'GET') {
=======
if ($path === '/admin/login' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AuthController::loginForm($config);
    return;
}

if ($path === '/admin/login' && $method === 'POST') {
    AuthController::login($pdo);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/logout' && $method === 'GET') {
=======
if ($path === '/admin/logout' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AuthController::logout();
    return;
}

<<<<<<< HEAD
if ($path === '/admin' && $method === 'GET') {
=======
if ($path === '/admin' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::dashboard($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/posts/new' && $method === 'GET') {
=======
if ($path === '/admin/posts/new' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::newPostForm($pdo, $config);
    return;
}

if ($path === '/admin/posts/create' && $method === 'POST') {
    AdminController::createPost($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/posts/edit' && $method === 'GET') {
=======
if ($path === '/admin/posts/edit' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
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

<<<<<<< HEAD
if ($path === '/admin/featured-posts' && $method === 'GET') {
=======
if ($path === '/admin/featured-posts' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::featuredPosts($pdo);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/featured' && $method === 'GET') {
=======
if ($path === '/admin/featured' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
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

<<<<<<< HEAD
if ($path === '/admin/post-cards' && $method === 'GET') {
=======
if ($path === '/admin/post-cards' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
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

<<<<<<< HEAD
if ($path === '/admin/posts/delete' && $method === 'GET') {
=======
if ($path === '/admin/posts/delete' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deletePost($pdo, $id);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/categories' && $method === 'GET') {
=======
if ($path === '/admin/categories' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::categories($pdo, $config);
    return;
}

if ($path === '/admin/categories/create' && $method === 'POST') {
    AdminController::createCategory($pdo);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/categories/delete' && $method === 'GET') {
=======
if ($path === '/admin/categories/delete' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deleteCategory($pdo, $id);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/tags' && $method === 'GET') {
=======
if ($path === '/admin/tags' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::tags($pdo, $config);
    return;
}

if ($path === '/admin/tags/create' && $method === 'POST') {
    AdminController::createTag($pdo);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/tags/delete' && $method === 'GET') {
=======
if ($path === '/admin/tags/delete' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deleteTag($pdo, $id);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/comments' && $method === 'GET') {
=======
if ($path === '/admin/comments' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::comments($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/comments/approve' && $method === 'GET') {
=======
if ($path === '/admin/comments/approve' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    $id = (int)($_GET['id'] ?? 0);
    AdminController::approveComment($pdo, $id);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/comments/delete' && $method === 'GET') {
=======
if ($path === '/admin/comments/delete' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    $id = (int)($_GET['id'] ?? 0);
    AdminController::deleteComment($pdo, $id);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/uploads' && $method === 'GET') {
=======
if ($path === '/admin/uploads' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
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

<<<<<<< HEAD
if ($path === '/admin/users' && $method === 'GET') {
=======
if ($path === '/admin/users' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
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

<<<<<<< HEAD
if ($path === '/admin/users/delete' && $method === 'GET') {
=======
if ($path === '/admin/users/delete' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::deleteUser($pdo);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/settings' && $method === 'GET') {
=======
if ($path === '/admin/settings' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::settings($config);
    return;
}

if ($path === '/admin/settings' && $method === 'POST') {
    AdminController::updateSettings($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/settings/hero' && $method === 'GET') {
=======
if ($path === '/admin/settings/hero' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::settingsHero($config);
    return;
}

if ($path === '/admin/settings/hero' && $method === 'POST') {
    AdminController::updateSettingsHero($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/settings/admin-card' && $method === 'GET') {
=======
if ($path === '/admin/settings/admin-card' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::settingsAdminCard($config);
    return;
}

if ($path === '/admin/settings/admin-card' && $method === 'POST') {
    AdminController::updateSettingsAdminCard($pdo, $config);
    return;
}

<<<<<<< HEAD
=======
if ($path === '/admin/settings/share' && ($method === 'GET' || $method === 'HEAD')) {
    AdminController::settingsShare($config);
    return;
}

if ($path === '/admin/settings/share' && $method === 'POST') {
    AdminController::updateSettingsShare($pdo, $config);
    return;
}

if ($path === '/admin/settings/menu' && ($method === 'GET' || $method === 'HEAD')) {
    AdminController::settingsMenu($config);
    return;
}

if ($path === '/admin/settings/menu' && $method === 'POST') {
    AdminController::updateSettingsMenu($pdo, $config);
    return;
}

>>>>>>> a3d11b8 (sync: update open-source release)
if ($path === '/admin/uploads/update' && $method === 'POST') {
    AdminController::updateUpload($pdo);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/uploads/delete' && $method === 'GET') {
    AdminController::deleteUpload($pdo);
    return;
}

if ($path === '/admin/cards' && $method === 'GET') {
=======
if ($path === '/admin/uploads/delete' && ($method === 'GET' || $method === 'HEAD')) {
    AdminController::deleteUpload($pdo, $config);
    return;
}

if ($path === '/admin/cards' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::cards($pdo, $config);
    return;
}

<<<<<<< HEAD
if ($path === '/admin/stats' && $method === 'GET') {
=======
if ($path === '/admin/groups' && ($method === 'GET' || $method === 'HEAD')) {
    AdminController::groups($pdo, $config);
    return;
}

if ($path === '/admin/groups/create' && $method === 'POST') {
    AdminController::createGroup($pdo);
    return;
}

if ($path === '/admin/groups/update' && $method === 'POST') {
    AdminController::updateGroup($pdo);
    return;
}

if ($path === '/admin/groups/manage' && ($method === 'GET' || $method === 'HEAD')) {
    $id = (int)($_GET['id'] ?? 0);
    AdminController::manageGroup($pdo, $config, $id);
    return;
}

if ($path === '/admin/groups/update-info' && $method === 'POST') {
    AdminController::updateGroupInfo($pdo);
    return;
}

if ($path === '/admin/groups/update-permissions' && $method === 'POST') {
    AdminController::updateGroupPermissions($pdo);
    return;
}

if ($path === '/admin/groups/delete' && $method === 'POST') {
    AdminController::deleteGroup($pdo);
    return;
}

if ($path === '/admin/maintenance' && ($method === 'GET' || $method === 'HEAD')) {
    AdminController::maintenance($pdo, $config);
    return;
}

if ($path === '/admin/maintenance/migrate' && $method === 'POST') {
    AdminController::migrateFeaturedCards($pdo, $config);
    return;
}

if ($path === '/admin/stats' && ($method === 'GET' || $method === 'HEAD')) {
>>>>>>> a3d11b8 (sync: update open-source release)
    AdminController::stats($pdo, $config);
    return;
}

if (str_starts_with($path, '/api/')) {
    PublicController::apiNotFound();
    return;
}

http_response_code(404);
View::render('404', ['config' => $config]);
