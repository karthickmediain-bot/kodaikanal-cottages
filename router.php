<?php
/**
 * Clean-URL router for PHP's built-in dev server.
 *
 * Run the site locally with:
 *   php -S localhost:8000 router.php
 *
 * This lets every page be requested without its ".html" extension —
 * e.g. http://localhost:8000/hotels-in-kodaikanal instead of
 * http://localhost:8000/hotels-in-kodaikanal.html — while real files
 * (css, js, images, partials) are still served exactly as they are on
 * disk. It has no effect once the site is deployed behind Apache/Nginx;
 * see .htaccess for the equivalent rewrite there.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Real files and directories (css/js/images/partials/the .html files
// themselves) are handed straight to the built-in server's default
// handler by returning false.
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// "/" -> the homepage.
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.html';
    return true;
}

// Blog (PHP-rendered from blog-system/data/, edited at /blog-admin/).
if ($uri === '/blog-admin' || $uri === '/blog-admin/') { chdir(__DIR__ . '/blog-system/admin'); require __DIR__ . '/blog-system/admin/index.php'; return true; }
if ($uri === '/sitemap-blog.xml') { require __DIR__ . '/blog-system/sitemap.php'; return true; }
if (preg_match('#^/blog/?$#', $uri)) { require __DIR__ . '/blog-system/index.php'; return true; }
if (preg_match('#^/blog/([a-z0-9-]+)/?$#', $uri, $m)) { $_GET['slug'] = $m[1]; require __DIR__ . '/blog-system/post.php'; return true; }

// "/some-page" or "/some-page/" -> some-page.html, if it exists.
$path = rtrim($uri, '/');
$candidate = __DIR__ . $path . '.html';

if (file_exists($candidate)) {
    require $candidate;
    return true;
}

// No matching page.
http_response_code(404);
echo '404 Not Found';
return true;
