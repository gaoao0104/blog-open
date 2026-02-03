<?php

declare(strict_types=1);

return [
<<<<<<< HEAD
    'base_url' => getenv('APP_BASE_URL') ?: '',
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'blog',
        'user' => getenv('DB_USER') ?: 'blog_user',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'socket' => getenv('DB_SOCKET') ?: '/run/mysqld/mysqld.sock',
    ],
    'session_name' => getenv('APP_SESSION_NAME') ?: 'blog_session',
    'upload_dir' => __DIR__ . '/../../public/uploads',
    'upload_url' => '/uploads',
    'max_upload_bytes' => 5 * 1024 * 1024,
    'gemini_api_key' => getenv('GEMINI_API_KEY') ?: '',
    'gemini_model' => getenv('GEMINI_MODEL') ?: 'gemini-2.5-pro',
=======
    'base_url' => '',
    'is_test' => false,
    'db' => [
        'host' => 'localhost',
        'name' => 'blog',
        'user' => 'blog_user',
        'pass' => getenv('BLOG_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'socket' => '/run/mysqld/mysqld.sock',
    ],
    'session_name' => 'blog_session',
    'upload_dir' => __DIR__ . '/../../public/uploads',
    'upload_url' => '/uploads',
    'max_upload_bytes' => 20 * 1024 * 1024,
    'storage_driver' => 'local',
    'r2' => [
        'endpoint' => 'https://<account_id>.r2.cloudflarestorage.com',
        'bucket' => 'blog',
        'access_key' => getenv('R2_ACCESS_KEY') ?: '',
        'secret_key' => getenv('R2_SECRET_KEY') ?: '',
        'public_base_url' => 'https://img.example.com',
        'region' => 'auto',
        'prefix' => 'uploads/prod',
    ],
    'gemini_api_key' => getenv('GEMINI_API_KEY') ?: '',
    'gemini_model' => 'gemini-3-flash-preview',
    'wechat_app_id' => getenv('WECHAT_APP_ID') ?: '',
    'wechat_app_secret' => getenv('WECHAT_APP_SECRET') ?: '',
    'wechat_token_cache' => getenv('WECHAT_TOKEN_CACHE') ?: '',
    'wechat_ticket_cache' => getenv('WECHAT_TICKET_CACHE') ?: '',
>>>>>>> a3d11b8 (sync: update open-source release)
];
