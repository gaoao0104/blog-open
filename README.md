# Gaoao Blog

轻量级 PHP + MySQL 博客系统，支持 Markdown、SEO、RSS、统计与后台管理。

## 功能特性
- 文章 / 分类 / 标签 / 评论
- Markdown 编辑与渲染
- 图片上传
- 推荐文章（可排序）
- 文章卡片（可选）
- SEO 元信息 + JSON-LD
- RSS 订阅（`/rss.xml`）
- 访问统计与数据面板（`/stats.php`）

## 环境要求
- PHP 8.1+
- MySQL 5.7+ / 8.0+
- Nginx 或 Apache
- PHP 扩展：pdo_mysql、mbstring、fileinfo、json、openssl

## 快速开始
1. 克隆项目
2. 创建配置文件：
   ```bash
   cp app/config/config.example.php app/config/config.php
   ```
3. 使用环境变量或直接编辑 `app/config/config.php` 填写数据库等信息
4. 创建数据库并导入结构：
   ```bash
   mysql -u root -p your_db < app/database/schema.sql
   ```
   如果只需要统计模块，可单独导入：
   ```bash
   mysql -u root -p your_db < app/database/site_analytics.sql
   ```
5. 确保上传目录可写：
   ```bash
   mkdir -p public/uploads
   chown -R www-data:www-data public/uploads
   chmod -R 755 public/uploads
   ```
6. Web 服务器根目录指向 `public/`

## 创建管理员账号
生成密码哈希：
```bash
php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
```
写入数据库：
```sql
INSERT INTO users (username, nickname, password_hash, created_at)
VALUES ('admin', 'Admin', 'YOUR_HASH_HERE', NOW());
```
访问 `/admin` 登录。

## RSS 订阅
- RSS 地址：`/rss.xml`
- 页面头部已自动加入 RSS 发现标签

## 统计面板
`/stats.php`（需管理员登录）

## Nginx 配置示例
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/project/public;
    index index.php;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
}
```

## 部署到生产
1. 配置域名与 HTTPS（推荐使用 Certbot 或面板自动签发）
2. 生产环境建议关闭错误显示（php.ini 或 PHP-FPM 里关闭 `display_errors`）
3. 配置缓存与压缩（Nginx 开启 gzip/br）
4. 定期备份数据库与上传文件
5. 设置合适的权限（仅 `public/uploads` 可写）

## 备份/迁移
数据库备份：
```bash
mysqldump -u root -p your_db > backup.sql
```
数据库恢复：
```bash
mysql -u root -p your_db < backup.sql
```
迁移时请同时复制：
- 数据库
- `public/uploads` 目录
- `app/config/config.php`

## 常见问题
**1. 后台无法登录？**  
检查 `users` 表是否存在账号，并确认密码哈希是否正确。

**2. 图片上传失败？**  
确认 `public/uploads` 可写权限，`php.ini` 的上传大小限制是否足够。

**3. 页面 500 错误？**  
检查 PHP-FPM 与 Nginx 日志，确认 `config.php` 是否正确，数据库连接是否可用。

**4. RSS 无法访问？**  
确认路由 `/rss.xml` 可访问，Web 服务器重写规则正常。

## 许可协议
GPL-3.0
