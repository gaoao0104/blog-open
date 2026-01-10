<?php

declare(strict_types=1);

return [
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
];
