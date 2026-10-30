<?php
require __DIR__ . '/lib.php';
$post = blog_get((string)($_GET['slug'] ?? ''));
if (!$post || ($post['status'] ?? '') !== 'published' || ($post['date'] ?? '') > date('Y-m-d')) {
    header('Location: /blog', true, 302);
    exit;
}
$url = BLOG_SITE . '/blog/' . $post['slug'];
$desc = $post['meta_description'] ?: $post['excerpt'];
$ld = ['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post['title'], 'description' => $desc,
    'datePublished' => $post['date'], 'dateModified' => substr($post['updated'] ?? $post['date'], 0, 10), 'mainEntityOfPage' => $url,
    'author' => ['@type' => 'Organization', 'name' => 'Cottages Kodaikanal'],
    'publisher' => ['@type' => 'Organization', 'name' => 'Cottages Kodaikanal', 'url' => BLOG_SITE . '/']];
if (!empty($post['image'])) $ld['image'] = BLOG_SITE . $post['image'];

blog_head($post['title'] . ' | Cottages Kodaikanal', $desc, $url, json_encode($ld, JSON_UNESCAPED_SLASHES), $post['image'] ?? '');
?>
  <main id="main-content">
    <section class="blog-hero">
      <div class="container">
        <p class="blog-eyebrow"><a href="/blog">&larr; Travel blog</a><?= $post['category'] ? ' &middot; ' . h($post['category']) : '' ?></p>
        <h1><?= h($post['title']) ?></h1>
        <p class="blog-lede"><?= h(blog_date($post['date'])) ?> &middot; By Cottages Kodaikanal</p>
      </div>
    </section>

    <article class="blog-section">
      <div class="container">
<?php if (!empty($post['image'])): ?>
        <img class="blog-article-cover" src="<?= h($post['image']) ?>" alt="<?= h($post['title']) ?>" width="1000" height="560" />
<?php endif; ?>
        <div class="blog-article"><?= $post['content'] /* sanitised on save */ ?></div>
        <div class="blog-help">
          <p>Ready to plan your Kodaikanal stay?</p>
          <a class="btn btn-primary" href="tel:+917502345777">Call +91 75023 45777</a>
          <a class="btn btn-outline" href="/blog">More articles</a>
        </div>
      </div>
    </article>
  </main>
<?php blog_foot();
