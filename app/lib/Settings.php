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
        'hero_mode' => 'text', // text or image
        'hero_image_url' => '',
        'favicon_url' => '',
        'footer_copyright' => '',
<<<<<<< HEAD
=======
        // Admin Menu Defaults
        'admin_menu_title' => '后台管理',
        'admin_menu_title_icon' => '⚙️',
        'admin_menu_section_resources_label' => '资源管理',
        'admin_menu_section_system_label' => '系统',
        'admin_menu_dashboard_label' => '仪表盘',
        'admin_menu_dashboard_icon' => '📊',
        'admin_menu_stats_label' => '站点统计',
        'admin_menu_stats_icon' => '📈',
        'admin_menu_write_label' => '写文章',
        'admin_menu_write_icon' => '📝',
        'admin_menu_featured_label' => '推荐管理',
        'admin_menu_featured_icon' => '🌟',
        'admin_menu_categories_label' => '分类',
        'admin_menu_categories_icon' => '📂',
        'admin_menu_tags_label' => '标签',
        'admin_menu_tags_icon' => '🏷️',
        'admin_menu_uploads_label' => '素材',
        'admin_menu_uploads_icon' => '🖼️',
        'admin_menu_cards_label' => '卡片',
        'admin_menu_cards_icon' => '🗂️',
        'admin_menu_comments_label' => '评论',
        'admin_menu_comments_icon' => '💬',
        'admin_menu_users_label' => '用户',
        'admin_menu_users_icon' => '👥',
        'admin_menu_groups_label' => '权限组',
        'admin_menu_groups_icon' => '🧩',
        'admin_menu_settings_label' => '设置',
        'admin_menu_settings_icon' => '⚙️',
        'admin_menu_maintenance_label' => '维护',
        'admin_menu_maintenance_icon' => '🛠️',
>>>>>>> a3d11b8 (sync: update open-source release)
        // Admin Card Defaults
        'admin_card_avatar' => '',
        'admin_card_name' => '管理员',
        'admin_card_bio' => '这里是站长简介/签名...',
        'admin_card_badge' => '',
        'social_icon_1' => '', 'social_link_1' => '',
        'social_icon_2' => '', 'social_link_2' => '',
        'social_icon_3' => '', 'social_link_3' => '',
        'social_icon_4' => '', 'social_link_4' => '',
        'show_admin_card' => '1',
        // Sidebar Defaults
        'show_sidebar_categories' => '1',
        'show_sidebar_tags' => '1',
<<<<<<< HEAD
=======
        // Share Defaults
        'share_enable_system' => '1',
        'share_enable_copy' => '1',
        'share_enable_wechat' => '1',
        'share_enable_qq' => '1',
        'share_enable_weibo' => '1',
        'share_enable_qzone' => '1',
        'share_enable_telegram' => '1',
        'share_icon_system' => '',
        'share_icon_copy' => '',
        'share_icon_wechat' => '',
        'share_icon_qq' => '',
        'share_icon_weibo' => '',
        'share_icon_qzone' => '',
        'share_icon_telegram' => '',
>>>>>>> a3d11b8 (sync: update open-source release)
    ];

    public static function all(\PDO $pdo): array
    {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $settings = self::defaults(); // Use dynamic defaults
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
        $defaults = self::DEFAULTS;
        $defaults['footer_copyright'] = '© ' . date('Y') . ' Gaoao Blog';
        return $defaults;
    }
}
