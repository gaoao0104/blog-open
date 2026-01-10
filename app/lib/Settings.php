<?php

declare(strict_types=1);

final class Settings
{
    private const DEFAULTS = [
        'site_name' => 'Gaoao Blog',
        'header_title' => 'Gaoao Blog',
        'hero_title' => '欢迎来到 Gaoao Blog',
        'hero_subtitle' => '记录技术与生活的点滴。',
        'show_nav_home' => '1',
        'show_nav_search' => '1',
        'show_nav_admin' => '1',
        'show_search_form' => '1',
        'show_hero' => '1',
    ];

    public static function all(\PDO $pdo): array
    {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $settings = self::DEFAULTS;
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function setMany(\PDO $pdo, array $values): void
    {
        $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($values as $key => $value) {
            $stmt->execute([$key, (string)$value]);
        }
    }

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }
}
