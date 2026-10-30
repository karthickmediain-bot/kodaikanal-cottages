<?php
// Creates (or resets) a blog admin from the command line. Not reachable from the web.
//   php blog-system/create-admin.php "Your Name" you@example.com "a-password-of-10+-chars"
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/lib.php';
[$self, $name, $email, $pw] = array_pad($argv, 4, '');
$email = strtolower(trim($email));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 10) {
    fwrite(STDERR, "Usage: php blog-system/create-admin.php \"Name\" email@example.com \"password (10+ characters)\"\n");
    exit(1);
}
$db = blog_db();
$hash = password_hash($pw, PASSWORD_DEFAULT);
$q = $db->prepare('SELECT id FROM blog_users WHERE email = ?');
$q->execute([$email]);
if ($id = $q->fetchColumn()) {
    $db->prepare("UPDATE blog_users SET name = ?, password_hash = ?, role = 'admin', active = 1 WHERE id = ?")->execute([$name, $hash, $id]);
    echo "Updated existing account $email (admin).\n";
} else {
    $db->prepare("INSERT INTO blog_users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')")->execute([$name, $email, $hash]);
    echo "Created admin $email.\n";
}
