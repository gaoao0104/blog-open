CREATE TABLE site_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    referrer TEXT NULL,
    referrer_host VARCHAR(255) NULL,
    visit_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_visit_date (visit_date),
    INDEX idx_visit_date_ip (visit_date, ip_address),
    INDEX idx_visit_date_referrer (visit_date, referrer_host),
    INDEX idx_post_date (post_id, visit_date),
    INDEX idx_post_date_ip (post_id, visit_date, ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
