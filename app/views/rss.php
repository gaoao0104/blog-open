<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$settings = $settings ?? [];
$site_name = ($settings['site_name'] ?? '') ?: 'Gaoao Blog';
$site_url = base_url($config);
$feed_url = url_for($config, '/rss.xml');
$description = $site_name . ' RSS 订阅';
$last_build = !empty($last_build) ? date(DATE_RSS, strtotime($last_build)) : date(DATE_RSS);

function rss_cdata(string $value): string
{
    return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $value) . ']]>';
}
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title><?= rss_cdata($site_name) ?></title>
        <link><?= e($site_url) ?></link>
        <atom:link href="<?= e($feed_url) ?>" rel="self" type="application/rss+xml" />
        <description><?= rss_cdata($description) ?></description>
        <language>zh-CN</language>
        <lastBuildDate><?= e($last_build) ?></lastBuildDate>
        <?php foreach ($posts as $post): ?>
            <?php
                $post_url = url_for($config, '/post/' . $post['slug']);
                $pub_date = $post['published_at'] ?? $post['created_at'];
                $pub_date = $pub_date ? date(DATE_RSS, strtotime($pub_date)) : date(DATE_RSS);
                $desc = $post['excerpt'] ?: snippet(markdown_plaintext($post['content_md']), 200);
                $content = Markdown::render($post['content_md']);
            ?>
            <item>
                <title><?= rss_cdata($post['title']) ?></title>
                <link><?= e($post_url) ?></link>
                <guid isPermaLink="true"><?= e($post_url) ?></guid>
                <pubDate><?= e($pub_date) ?></pubDate>
                <description><?= rss_cdata($desc) ?></description>
                <content:encoded><?= rss_cdata($content) ?></content:encoded>
            </item>
        <?php endforeach; ?>
    </channel>
</rss>
