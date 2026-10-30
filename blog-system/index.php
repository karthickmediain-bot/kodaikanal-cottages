<?php
require __DIR__ . '/lib.php';
$posts = blog_all();
$feature = array_shift($posts);

blog_head(
    'Kodaikanal Travel Blog | Guides, Tips & Itineraries',
    'Kodaikanal travel blog: guides, seasonal tips and trip plans from the Cottages Kodaikanal team.',
    BLOG_SITE . '/blog',
    json_encode(['@context' => 'https://schema.org', '@type' => 'Blog', 'name' => 'Cottages Kodaikanal Travel Blog', 'url' => BLOG_SITE . '/blog',
        'publisher' => ['@type' => 'Organization', 'name' => 'Cottages Kodaikanal', 'url' => BLOG_SITE . '/']], JSON_UNESCAPED_SLASHES)
);
?>
  <main id="main-content">
    <section class="blog-hero">
      <div class="container">
        <a class="blog-login" href="/blog-admin/">Team login</a>
        <p class="blog-eyebrow">The Cottages Kodaikanal Journal</p>
        <h1>Kodaikanal travel blog</h1>
        <p class="blog-lede">Practical guides, seasonal tips and trip plans from the team that hosts thousands of guests every year.</p>
      </div>
    </section>

    <section class="blog-section">
      <div class="container">
<?php if (!$feature): ?>
        <p class="blog-empty">New articles are on the way. Meanwhile, <a href="/places-to-visit-in-kodaikanal">explore places to visit in Kodaikanal</a>.</p>
<?php else: ?>
        <a class="blog-feature<?= empty($feature['image']) ? ' no-img' : '' ?>" href="/blog/<?= h($feature['slug']) ?>">
<?php if (!empty($feature['image'])): ?>
          <span class="blog-feature-img"><img src="<?= h($feature['image']) ?>" alt="<?= h($feature['title']) ?>" width="800" height="560" /></span>
<?php endif; ?>
          <span class="blog-feature-body">
            <span class="blog-tag">Featured<?= $feature['category'] ? ' &middot; ' . h($feature['category']) : '' ?></span>
            <h2><?= h($feature['title']) ?></h2>
            <span class="blog-excerpt"><?= h($feature['excerpt']) ?></span>
            <span class="blog-date"><?= h(blog_date($feature['date'])) ?></span>
            <span class="blog-more">Read the article &rarr;</span>
          </span>
        </a>
<?php if ($posts): ?>
        <h2 class="blog-list-title">More articles</h2>
        <div class="blog-grid">
<?php foreach ($posts as $p): ?>
          <a class="blog-post" href="/blog/<?= h($p['slug']) ?>">
<?php if (!empty($p['image'])): ?>
            <span class="blog-post-img"><img src="<?= h($p['image']) ?>" alt="<?= h($p['title']) ?>" width="640" height="400" loading="lazy" /></span>
<?php endif; ?>
<?php if ($p['category']): ?>            <span class="blog-tag"><?= h($p['category']) ?></span>
<?php endif; ?>
            <h3><?= h($p['title']) ?></h3>
            <span class="blog-excerpt"><?= h($p['excerpt']) ?></span>
            <span class="blog-date"><?= h(blog_date($p['date'])) ?></span>
          </a>
<?php endforeach; ?>
        </div>
<?php endif; endif; ?>
        <div class="blog-help">
          <p>Need help planning your trip? Our team replies fast.</p>
          <a class="btn btn-primary" href="tel:+917502345777">Call +91 75023 45777</a>
          <a class="btn btn-outline" href="/contact">Contact Us</a>
        </div>
      </div>
    </section>
  </main>
<?php blog_foot();
