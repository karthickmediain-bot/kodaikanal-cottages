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
        <h2 class="blog-list-title">Guides and cottage pages</h2>
        <div class="blog-grid">
          <a class="blog-post" href="/kodaikanal-trip">
            <span class="blog-post-img"><img src="/images/scenery/hero-kodaikanal-hiker.webp" alt="Kodaikanal Trip Packages & Itinerary" width="640" height="400" loading="lazy" /></span>
            <span class="blog-tag">Travel guide</span>
            <h3>Kodaikanal Trip Packages &amp; Itinerary</h3>
            <span class="blog-excerpt">One, two and three day plans, what each package covers, when to go and what it costs.</span>
          </a>
          <a class="blog-post" href="/water-falls-in-kodaikanal">
            <span class="blog-post-img"><img src="/images/destinations/falls-cliff-aerial.webp" alt="Water Falls in Kodaikanal" width="640" height="400" loading="lazy" /></span>
            <span class="blog-tag">Travel guide</span>
            <h3>Water Falls in Kodaikanal</h3>
            <span class="blog-excerpt">Silver Cascade, Bear Shola, Pambar and more: when they flow best and how to reach them.</span>
          </a>
          <a class="blog-post" href="/2-bedroom-cottage-in-kodaikanal">
            <span class="blog-post-img"><img src="/images/properties/cottage-showcase-2bhk.webp" alt="2 Bedroom Cottages in Kodaikanal" width="640" height="400" loading="lazy" /></span>
            <span class="blog-tag">Cottage guide</span>
            <h3>2 Bedroom Cottages in Kodaikanal</h3>
            <span class="blog-excerpt">Whole 2 BHK cottages for couples and small families, with a kitchen and valley view.</span>
          </a>
          <a class="blog-post" href="/3-bedroom-cottage-in-kodaikanal">
            <span class="blog-post-img"><img src="/images/properties/cottage-showcase-3bhk.webp" alt="3 Bedroom Cottages in Kodaikanal" width="640" height="400" loading="lazy" /></span>
            <span class="blog-tag">Cottage guide</span>
            <h3>3 Bedroom Cottages in Kodaikanal</h3>
            <span class="blog-excerpt">Private 3 bedroom cottages for families and groups of up to 12.</span>
          </a>
          <a class="blog-post" href="/4-bedroom-cottage-in-kodaikanal">
            <span class="blog-post-img"><img src="/images/properties/cottage-showcase-4bhk.webp" alt="4 Bedroom Cottages in Kodaikanal" width="640" height="400" loading="lazy" /></span>
            <span class="blog-tag">Cottage guide</span>
            <h3>4 Bedroom Cottages in Kodaikanal</h3>
            <span class="blog-excerpt">4 BHK cottages for big family and group stays, with kitchen and parking.</span>
          </a>
          <a class="blog-post" href="/5-bedroom-cottage-in-kodaikanal">
            <span class="blog-post-img"><img src="/images/properties/cottage-showcase-5bhk.webp" alt="5 Bedroom Cottages in Kodaikanal" width="640" height="400" loading="lazy" /></span>
            <span class="blog-tag">Cottage guide</span>
            <h3>5 Bedroom Cottages in Kodaikanal</h3>
            <span class="blog-excerpt">Whole-house stays for one large group, with six cottages to choose from.</span>
          </a>
        </div>
        <div class="blog-help">
          <p>Need help planning your trip? Our team replies fast.</p>
          <a class="btn btn-primary" href="tel:+917502345777">Call +91 75023 45777</a>
          <a class="btn btn-outline" href="/contact">Contact Us</a>
        </div>
      </div>
    </section>
  </main>
<?php blog_foot();
