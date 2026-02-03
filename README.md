# Gaoao Blog

<<<<<<< HEAD
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

## 项目主页
项目主页用于展示系统的完整功能和视觉效果，可作为线上演示入口。
- 线上演示地址：blog.gaoao.xin
- 代码仓库主页：https://github.com/gaoao0104/blog-open

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
=======
一个轻量级的 PHP 博客系统，包含前台展示、后台管理、Markdown 编辑器、SEO/统计、推荐模块、权限分组与 R2 图床等功能。

## 环境要求

- PHP 8.1+（推荐开启扩展：`curl`、`mbstring`、`pdo_mysql`）
- MySQL 5.7+ / 8.0+
- Nginx（或 Apache）

## 快速开始

### 1. 获取代码

```bash
git clone <your_repo_url>
cd blog-open
```

### 2. 配置文件

复制配置文件并按需修改：

```bash
cp app/config/config.example.php app/config/config.php
```

重要配置说明：
- `base_url`：站点主域名（建议填写 `https://yourdomain.com`）
- `db`：数据库连接信息（密码建议通过环境变量注入）
- `storage_driver`：`local` 或 `r2`
- `r2`：启用 R2 时需要补齐配置

### 3. 创建数据库与导入结构

```bash
mysql -u root -p -e "CREATE DATABASE blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p blog < app/database/schema.sql
```

如需运行增量迁移：

```bash
php app/database/migrate.php --config=app/config/config.php
```

### 4. 创建管理员账号

生成密码哈希：

```bash
php -r 'echo password_hash("YourPassword", PASSWORD_DEFAULT) . PHP_EOL;'
```

写入数据库（示例）：

```sql
INSERT INTO admin_groups (name, description, is_super, created_at)
VALUES ('超级管理员', '超级管理员组', 1, NOW());

INSERT INTO users (username, nickname, password_hash, role, group_id, created_at)
VALUES ('admin', '管理员', '<上一步生成的hash>', 'admin', 1, NOW());
```

### 5. Nginx 配置示例

```nginx
server {
    server_name yourdomain.com;
    root /var/www/blog-open/public;
>>>>>>> a3d11b8 (sync: update open-source release)
    index index.php;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
<<<<<<< HEAD
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
=======
        include snippets/fastcgi-php.conf;
>>>>>>> a3d11b8 (sync: update open-source release)
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
}
```

<<<<<<< HEAD
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
=======
### 6. 目录权限

```bash
chmod -R 755 public/uploads
```

## R2 存储（可选）

1. 将 `storage_driver` 改为 `r2`。
2. 配置 `R2_ACCESS_KEY`、`R2_SECRET_KEY`、`r2.endpoint`、`r2.public_base_url`。
3. 需要历史图片迁移时：

```bash
php app/scripts/migrate_uploads_to_r2.php --config=app/config/config.php
```

## 生产部署

- 建议开启 HTTPS（Certbot）
- 建议在 PHP-FPM 中通过环境变量注入敏感配置：

```ini
env[BLOG_DB_PASS] = "your_db_password"
env[R2_ACCESS_KEY] = "your_r2_access_key"
env[R2_SECRET_KEY] = "your_r2_secret_key"
```

- `base_url` 建议填写主域名，避免 SEO 重复收录

## 备份 / 迁移

- 数据库备份：

```bash
mysqldump -u root -p blog > blog.sql
```

- 本地上传文件：备份 `public/uploads/`
- 使用 R2 时：备份对象存储即可

## 常见问题

**1) 访问 500 或 404**
- 检查 `public` 是否为站点根目录
- 检查 Nginx `try_files` 与 PHP-FPM 配置

**2) 无法上传图片**
- 检查 `public/uploads` 权限
- 检查 `php.ini` 上传大小限制

**3) RSS/分享链接不正确**
- 请配置 `base_url` 为实际域名

## License

>>>>>>> a3d11b8 (sync: update open-source release)
GPL-3.0
