<?php
// Shared helpers for the blog (public pages + admin). Not a web page.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }

const BLOG_SITE = 'https://www.cottageskodaikanal.com';
define('BLOG_POSTS_DIR', __DIR__ . '/data/posts');
define('BLOG_UPLOAD_DIR', dirname(__DIR__) . '/images/blog');

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function blog_slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim(substr($s, 0, 80), '-');
}

function blog_valid_slug(string $s): bool { return (bool)preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $s) && strlen($s) <= 80; }

function blog_get(string $slug): ?array {
    if (!blog_valid_slug($slug)) return null;
    $f = BLOG_POSTS_DIR . "/$slug.json";
    if (!is_file($f)) return null;
    $p = json_decode((string)file_get_contents($f), true);
    return is_array($p) ? $p : null;
}

/** All posts, newest first. $publishedOnly hides drafts and future-dated posts. */
function blog_all(bool $publishedOnly = true): array {
    $out = [];
    foreach (glob(BLOG_POSTS_DIR . '/*.json') ?: [] as $f) {
        $p = json_decode((string)file_get_contents($f), true);
        if (!is_array($p) || empty($p['slug'])) continue;
        if ($publishedOnly && (($p['status'] ?? '') !== 'published' || ($p['date'] ?? '') > date('Y-m-d'))) continue;
        $out[] = $p;
    }
    usort($out, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? '') ?: strcmp($b['updated'] ?? '', $a['updated'] ?? ''));
    return $out;
}

function blog_save(array $p): bool {
    if (!is_dir(BLOG_POSTS_DIR)) mkdir(BLOG_POSTS_DIR, 0755, true);
    $p['updated'] = date('c');
    $tmp = BLOG_POSTS_DIR . '/' . $p['slug'] . '.tmp';
    return file_put_contents($tmp, json_encode($p, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false
        && rename($tmp, BLOG_POSTS_DIR . '/' . $p['slug'] . '.json');
}

function blog_delete(string $slug): bool {
    return blog_valid_slug($slug) && is_file($f = BLOG_POSTS_DIR . "/$slug.json") && unlink($f);
}

function blog_unique_slug(string $base, ?string $except = null): string {
    $base = $base ?: 'post';
    $slug = $base;
    for ($i = 2; $slug !== $except && is_file(BLOG_POSTS_DIR . "/$slug.json"); $i++) $slug = "$base-$i";
    return $slug;
}

function blog_cut(string $s, int $from, int $len): string { return function_exists('mb_substr') ? mb_substr($s, $from, $len) : substr($s, $from, $len); }

/**
 * Database for blog_users. Uses the site's MySQL database from api/config.php; when that file is absent
 * (local development) it falls back to a SQLite file in blog-data/. Creates the table on first use.
 */
function blog_db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    $cfgFile = dirname(__DIR__) . '/api/config.php';
    if (is_file($cfgFile)) {
        $d = (require $cfgFile)['db'];
        $pdo = new PDO("mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}", $d['user'], $d['pass'], $opts);
        $pdo->exec("CREATE TABLE IF NOT EXISTS blog_users (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name          VARCHAR(80)  NOT NULL,
            email         VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
            active        TINYINT(1)   NOT NULL DEFAULT 1,
            last_login    DATETIME     NULL,
            created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_blog_users_email (email)
        ) CHARACTER SET utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS blog_password_resets (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id    INT UNSIGNED NOT NULL,
            token_hash CHAR(64)     NOT NULL,
            expires_at BIGINT       NOT NULL,
            created_at BIGINT       NOT NULL,
            KEY idx_reset_hash (token_hash),
            KEY idx_reset_user (user_id)
        ) CHARACTER SET utf8mb4");
    } else {
        $pdo = new PDO('sqlite:' . __DIR__ . '/data/dev.sqlite', null, null, $opts);
        $pdo->exec("CREATE TABLE IF NOT EXISTS blog_users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            name          TEXT NOT NULL,
            email         TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role          TEXT NOT NULL DEFAULT 'editor',
            active        INTEGER NOT NULL DEFAULT 1,
            last_login    TEXT NULL,
            created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS blog_password_resets (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id    INTEGER NOT NULL,
            token_hash TEXT NOT NULL,
            expires_at INTEGER NOT NULL,
            created_at INTEGER NOT NULL
        )");
    }
    return $pdo;
}

function blog_date(?string $d): string { $t = strtotime((string)$d); return $t ? date('j F Y', $t) : ''; }

/** Reduce editor HTML to a safe allowlist (no scripts, styles, event handlers). */
function blog_sanitize(string $html): string {
    if (trim($html) === '') return '';
    $allowed = [
        'p' => [], 'br' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [], 'a' => ['href', 'target', 'rel'], 'img' => ['src', 'alt'],
    ];
    $drop = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'math', 'noscript', 'template'];
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="r">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    $root = $doc->getElementById('r');
    if (!$root) return '';
    $clean = function (DOMNode $node) use (&$clean, $allowed, $drop) {
        foreach (iterator_to_array($node->childNodes) as $c) {
            if ($c instanceof DOMText) continue;
            if (!($c instanceof DOMElement)) { $node->removeChild($c); continue; }
            $tag = strtolower($c->tagName);
            if (!isset($allowed[$tag])) {
                if (in_array($tag, $drop, true)) { $node->removeChild($c); continue; }
                $clean($c); // unwrap unknown tag, keep its (cleaned) children
                while ($c->firstChild) $node->insertBefore($c->firstChild, $c);
                $node->removeChild($c);
                continue;
            }
            foreach (iterator_to_array($c->attributes) as $a) {
                if (!in_array($a->name, $allowed[$tag], true)) $c->removeAttribute($a->name);
            }
            if ($tag === 'a') {
                $href = trim($c->getAttribute('href'));
                if (!preg_match('~^(https?://|mailto:|tel:|/|#)~i', $href)) $c->removeAttribute('href');
                if (preg_match('~^https?://~i', $href)) { $c->setAttribute('target', '_blank'); $c->setAttribute('rel', 'noopener'); }
                else { $c->removeAttribute('target'); $c->removeAttribute('rel'); }
            }
            if ($tag === 'img') {
                if (!preg_match('~^(https?://|/)~i', trim($c->getAttribute('src')))) { $node->removeChild($c); continue; }
                $c->setAttribute('loading', 'lazy');
            }
            $clean($c);
        }
    };
    $clean($root);
    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    return trim($out);
}

function blog_head(string $title, string $desc, string $canonical, string $jsonld = '', string $ogImage = ''): void {
    ?><!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <base href="/" />
  <title><?= h($title) ?></title>
  <meta name="description" content="<?= h($desc) ?>" />
  <link rel="canonical" href="<?= h($canonical) ?>" />
  <meta property="og:title" content="<?= h($title) ?>" />
  <meta property="og:description" content="<?= h($desc) ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= h($canonical) ?>" />
<?php if ($ogImage): ?>  <meta property="og:image" content="<?= h(BLOG_SITE . $ogImage) ?>" />
<?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/css/styles.css?v=hdr9" />
  <link rel="stylesheet" href="/css/info-pages.css" />
  <link rel="stylesheet" href="/css/blog.css?v=2" />
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-5SH1V9KRG7"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-5SH1V9KRG7');
  </script>
<?php if ($jsonld): ?>  <script type="application/ld+json"><?= $jsonld ?></script>
<?php endif; ?>
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to main content</a>
  <div id="site-header" data-include="/partials/header.html"></div>
<?php
}

function blog_foot(): void {
    ?>
  <div id="site-footer" data-include="/partials/footer.html"></div>
  <div id="site-floating-cta" data-include="/partials/floating-cta.html"></div>
  <script src="/js/include.js" defer></script>
  <script src="/js/main.js" defer></script>
  <script src="/js/wa-context.js" defer></script>
</body>
</html>
<?php
}
