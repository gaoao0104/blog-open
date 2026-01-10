<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= e(url_for($config, '/')) ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <?php foreach ($categories ?? [] as $category): ?>
        <url>
            <loc><?= e(url_for($config, '/category/' . $category['slug'])) ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    <?php endforeach; ?>
    <?php foreach ($tags ?? [] as $tag): ?>
        <url>
            <loc><?= e(url_for($config, '/tag/' . $tag['slug'])) ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
        </url>
    <?php endforeach; ?>
    <?php foreach ($posts as $post): ?>
        <url>
            <loc><?= e(url_for($config, '/post/' . $post['slug'])) ?></loc>
            <lastmod><?= e(date('c', strtotime($post['updated_at']))) ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>
</urlset>
