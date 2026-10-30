<?php
// /sitemap-blog.xml: lists every published blog post, updated automatically as posts are published.
require __DIR__ . '/lib.php';
header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach (blog_all() as $p): ?>
  <url><loc><?= h(BLOG_SITE . '/blog/' . $p['slug']) ?></loc><lastmod><?= h(substr($p['updated'] ?? $p['date'], 0, 10)) ?></lastmod><priority>0.7</priority></url>
<?php endforeach; ?>
</urlset>
