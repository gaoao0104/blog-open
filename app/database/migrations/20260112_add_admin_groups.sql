CREATE TABLE admin_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    is_super TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE admin_group_permissions (
    group_id INT UNSIGNED NOT NULL,
    perm_key VARCHAR(80) NOT NULL,
    PRIMARY KEY (group_id, perm_key),
    FOREIGN KEY (group_id) REFERENCES admin_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users ADD COLUMN group_id INT UNSIGNED NULL;
ALTER TABLE users ADD CONSTRAINT fk_users_group_id FOREIGN KEY (group_id) REFERENCES admin_groups(id) ON DELETE SET NULL;

INSERT INTO admin_groups (id, name, description, is_super, created_at) VALUES
    (1, '管理员', '拥有全部权限', 1, NOW()),
    (2, '编辑', '仅管理自己的内容', 0, NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), is_super = VALUES(is_super);

UPDATE users SET group_id = 1 WHERE group_id IS NULL;
