<?php
// Blog admin: log in, write / edit / delete posts, upload images. No developer needed.
require dirname(__DIR__) . '/lib.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

$self = strtok($_SERVER['REQUEST_URI'], '?');
$msg = '';
$err = '';

function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_ok(): bool { return hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? '')); }
function go(string $to): never { header("Location: $to"); exit; }
function user_find(PDO $db, int $id): ?array { $q = $db->prepare('SELECT * FROM blog_users WHERE id = ? AND active = 1'); $q->execute([$id]); return $q->fetch(PDO::FETCH_ASSOC) ?: null; }
function admins_other_than(PDO $db, int $id): int { $q = $db->prepare("SELECT COUNT(*) FROM blog_users WHERE role = 'admin' AND active = 1 AND id <> ?"); $q->execute([$id]); return (int)$q->fetchColumn(); }

/** Creates a one-hour reset token for this email (if it has an active account) and emails the link.
 *  Always returns quietly so the page never reveals whether the email exists.
 *  In local test mode (no api/config.php) it returns the link instead of sending mail. */
function reset_send(PDO $db, string $email): ?string {
    $q = $db->prepare('SELECT id, name FROM blog_users WHERE email = ? AND active = 1');
    $q->execute([$email]);
    $u = $q->fetch(PDO::FETCH_ASSOC);
    if (!$u) return null;
    $db->prepare('DELETE FROM blog_password_resets WHERE expires_at < ?')->execute([time()]);
    $recent = $db->prepare('SELECT COUNT(*) FROM blog_password_resets WHERE user_id = ? AND created_at > ?');
    $recent->execute([$u['id'], time() - 60]);
    if ((int)$recent->fetchColumn() > 0) return null;
    $token = bin2hex(random_bytes(32));
    $db->prepare('INSERT INTO blog_password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?)')
       ->execute([$u['id'], hash('sha256', $token), time() + 3600, time()]);

    $cfgFile = dirname(__DIR__, 2) . '/api/config.php';
    if (!is_file($cfgFile)) return '/blog-admin/?reset=' . $token; // local test mode
    $link = BLOG_SITE . '/blog-admin/?reset=' . $token;
    try {
        require_once dirname(__DIR__, 2) . '/api/smtp.php';
        $smtp = (require $cfgFile)['smtp'];
        $smtp['to'] = $email;
        smtp_send($smtp, 'Reset your blog password',
            "Hi " . $u['name'] . ",\n\nSomeone asked to reset the password for your Cottages Kodaikanal blog account.\n\n"
            . "Choose a new password here (the link works for one hour):\n$link\n\n"
            . "If this was not you, ignore this email. Your password stays the same.\n");
    } catch (Throwable $e) {
        error_log('blog-admin reset email: ' . $e->getMessage());
    }
    return null;
}

// ---------- Database (user accounts live in the site's MySQL database) ----------
try {
    $db = blog_db();
    $userCount = (int)$db->query('SELECT COUNT(*) FROM blog_users')->fetchColumn();
} catch (Throwable $e) {
    error_log('blog-admin database: ' . $e->getMessage());
    auth_top('Database', 'Database not connected', 'The blog stores its user accounts in the website database. Check the db details in api/config.php and that the database exists.', '');
    auth_bottom(); exit;
}

// ---------- No public sign-up ----------
// Accounts are created by an admin (Team page). The very first admin is seeded by the site owner:
//   - add 'blog_admin' => ['name' => '...', 'email' => '...', 'password' => '...'] to api/config.php, or
//   - run: php blog-system/create-admin.php "Name" email@example.com "password"
// Remove the password from config.php once the account exists.
if ($userCount === 0) {
    $cfgFile = dirname(__DIR__, 2) . '/api/config.php';
    $seed = is_file($cfgFile) ? ((require $cfgFile)['blog_admin'] ?? null) : null;
    $seedEmail = is_array($seed) ? strtolower(trim((string)($seed['email'] ?? ''))) : '';
    $seedWhy = !is_file($cfgFile) ? 'api/config.php was not found on the server.'
        : (!is_array($seed) ? "api/config.php has no 'blog_admin' entry (check the spelling, and that the line is not commented out with //)."
        : (!filter_var($seedEmail, FILTER_VALIDATE_EMAIL) ? "The 'email' inside 'blog_admin' is missing or not a valid email address."
        : (strlen((string)($seed['password'] ?? '')) < 10 ? "The 'password' inside 'blog_admin' is missing or shorter than 10 characters." : '')));
    if ($seedWhy === '') {
        $db->prepare("INSERT INTO blog_users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')")
           ->execute([blog_cut(trim((string)($seed['name'] ?? 'Admin')), 0, 80), $seedEmail, password_hash((string)$seed['password'], PASSWORD_DEFAULT)]);
        $userCount = 1;
    }
}

// ---------- Login / logout ----------
if (isset($_GET['logout'])) { session_destroy(); go($self); }

$me = !empty($_SESSION['uid']) ? user_find($db, (int)$_SESSION['uid']) : null;

if (!$me && isset($_GET['forgot'])) {
    $sent = false; $devLink = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        if (!csrf_ok()) $err = 'Session expired. Reload the page and try again.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter the email address you log in with.';
        elseif (!empty($_SESSION['reset_at']) && $_SESSION['reset_at'] > time() - 60) $err = 'Please wait a minute before asking again.';
        else {
            $_SESSION['reset_at'] = time();
            $devLink = reset_send($db, $email);
            $sent = true;
        }
    }
    if ($sent) {
        auth_top('Reset password', 'Check your email', 'If that email belongs to a team account, a reset link is on its way. It works for one hour.', '');
        if ($devLink) echo '<p class="intro"><small>Local test mode, no email is sent: <a href="' . h($devLink) . '">open the reset link</a></small></p>';
        echo '<a class="btn full" style="display:block;text-align:center;text-decoration:none" href="' . h($self) . '">Back to log in</a>';
    } else {
        auth_top('Reset password', 'Forgot your password?', 'Enter your email and we will send you a link to choose a new one.', $err);
        echo '<input type="hidden" name="csrf" value="' . csrf() . '">
              <label>Email<span class="pw"><input type="email" name="email" required autofocus autocomplete="username" value="' . h($_POST['email'] ?? '') . '"></span></label>
              <button class="btn full">Send reset link</button>
              <a class="back" href="' . h($self) . '">Back to log in</a>';
    }
    auth_bottom(); exit;
}

if (!$me && isset($_GET['reset'])) {
    $token = (string)$_GET['reset'];
    $q = $db->prepare('SELECT id, user_id FROM blog_password_resets WHERE token_hash = ? AND expires_at > ?');
    $q->execute([hash('sha256', $token), time()]);
    $row = preg_match('/^[a-f0-9]{64}$/', $token) ? $q->fetch(PDO::FETCH_ASSOC) : false;
    if (!$row) {
        auth_top('Reset password', 'Link expired', 'This reset link is not valid any more. Ask for a new one.', '');
        echo '<a class="btn full" style="display:block;text-align:center;text-decoration:none" href="' . h($self) . '?forgot">Send a new link</a>';
        auth_bottom(); exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new = (string)($_POST['new'] ?? '');
        if (!csrf_ok()) $err = 'Session expired. Reload the page and try again.';
        elseif (strlen($new) < 10) $err = 'The new password needs at least 10 characters.';
        elseif ($new !== ($_POST['new2'] ?? '')) $err = 'The two passwords do not match.';
        else {
            $db->prepare('UPDATE blog_users SET password_hash = ? WHERE id = ? AND active = 1')->execute([password_hash($new, PASSWORD_DEFAULT), $row['user_id']]);
            $db->prepare('DELETE FROM blog_password_resets WHERE user_id = ?')->execute([$row['user_id']]);
            session_regenerate_id(true);
            $_SESSION = [];
            go($self . '?msg=pw_reset');
        }
    }
    auth_top('Reset password', 'Choose a new password', 'Use at least 10 characters.', $err);
    echo '<input type="hidden" name="csrf" value="' . csrf() . '">
          <label>New password<span class="pw"><input type="password" name="new" required minlength="10" autofocus autocomplete="new-password"><button type="button" class="eye" aria-label="Show password">Show</button></span></label>
          <label>Repeat new password<span class="pw"><input type="password" name="new2" required autocomplete="new-password"></span></label>
          <button class="btn full">Save new password</button>';
    auth_bottom(); exit;
}

if (!$me) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        if (!empty($_SESSION['lock']) && $_SESSION['lock'] > time()) $err = 'Too many attempts. Wait a minute and try again.';
        else {
            $q = $db->prepare('SELECT * FROM blog_users WHERE email = ? AND active = 1');
            $q->execute([$email]);
            $u = $q->fetch(PDO::FETCH_ASSOC);
            // Always run a hash check so response time does not reveal whether the email exists.
            $ok = password_verify((string)($_POST['password'] ?? ''), $u['password_hash'] ?? password_hash('x', PASSWORD_DEFAULT)) && $u;
            if ($ok) {
                $db->prepare('UPDATE blog_users SET last_login = ? WHERE id = ?')->execute([date('Y-m-d H:i:s'), $u['id']]);
                session_regenerate_id(true);
                $_SESSION = ['uid' => (int)$u['id']];
                go($self);
            }
            $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
            if ($_SESSION['fails'] >= 5) { $_SESSION['lock'] = time() + 60; $_SESSION['fails'] = 0; }
            sleep(1);
            $err = 'Wrong email or password.';
        }
    }
    $intro = ($_GET['msg'] ?? '') === 'pw_reset' ? 'Your password was changed. Log in with the new one.' : 'Log in to write and manage your Kodaikanal travel blog.';
    if ($userCount === 0) $err = 'No team accounts exist yet. Ask the site owner to set up the first admin.' . (!empty($seedWhy) ? ' Setup check: ' . $seedWhy : '');
    auth_top('Log in', 'Welcome back', $intro, $err);
    echo '<label>Email<span class="pw"><input type="email" name="email" required autofocus autocomplete="username" value="' . h($_POST['email'] ?? '') . '"></span></label>
          <label>Password<span class="pw"><input type="password" name="password" required autocomplete="current-password"><button type="button" class="eye" aria-label="Show password">Show</button></span></label>
          <button class="btn full">Log in</button>
          <a class="back" href="' . h($self) . '?forgot">Forgot password?</a>';
    auth_bottom(); exit;
}
$isAdmin = $me['role'] === 'admin';

// ---------- Team & account actions ----------
$act = (string)($_POST['action'] ?? '');
if (str_starts_with($act, 'user_') || $act === 'account_pw') {
    if (!csrf_ok()) { $err = 'Session expired. Reload the page.'; $_GET[$act === 'account_pw' ? 'account' : 'users'] = 1; }
    elseif ($act === 'account_pw') {
        $new = (string)($_POST['new'] ?? '');
        if (!password_verify((string)($_POST['current'] ?? ''), $me['password_hash'])) $err = 'Your current password is not right.';
        elseif (strlen($new) < 10) $err = 'The new password needs at least 10 characters.';
        elseif ($new !== ($_POST['new2'] ?? '')) $err = 'The two new passwords do not match.';
        else { $db->prepare('UPDATE blog_users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]); go($self . '?account&msg=pw_changed'); }
        $_GET['account'] = 1;
    } elseif ($isAdmin) {
        $id = (int)($_POST['id'] ?? 0);
        $_GET['users'] = 1;
        try {
            if ($act === 'user_add') {
                $name = trim((string)($_POST['name'] ?? ''));
                $email = strtolower(trim((string)($_POST['email'] ?? '')));
                $pw = (string)($_POST['password'] ?? '');
                $role = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'editor';
                if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter a name and a valid email address.';
                elseif (strlen($pw) < 10) $err = 'The password needs at least 10 characters.';
                else { $db->prepare('INSERT INTO blog_users (name, email, password_hash, role) VALUES (?, ?, ?, ?)')->execute([blog_cut($name, 0, 80), $email, password_hash($pw, PASSWORD_DEFAULT), $role]); go($self . '?users&msg=user_added'); }
            } elseif ($id && $id !== (int)$me['id'] && $act === 'user_delete') {
                $db->prepare('DELETE FROM blog_users WHERE id = ?')->execute([$id]); go($self . '?users&msg=user_deleted');
            } elseif ($id && $act === 'user_reset') {
                $pw = (string)($_POST['password'] ?? '');
                if (strlen($pw) < 10) $err = 'The password needs at least 10 characters.';
                else { $db->prepare('UPDATE blog_users SET password_hash = ? WHERE id = ?')->execute([password_hash($pw, PASSWORD_DEFAULT), $id]); go($self . '?users&msg=user_updated'); }
            } elseif ($id && in_array($act, ['user_toggle', 'user_role'], true)) {
                $t = $db->prepare('SELECT role, active FROM blog_users WHERE id = ?'); $t->execute([$id]); $row = $t->fetch(PDO::FETCH_ASSOC);
                if ($id === (int)$me['id']) $err = 'You cannot change your own access.';
                elseif ($row && $row['role'] === 'admin' && $row['active'] && admins_other_than($db, $id) === 0) $err = 'There must always be one active admin.';
                else {
                    $db->prepare($act === 'user_toggle' ? 'UPDATE blog_users SET active = 1 - active WHERE id = ?' : "UPDATE blog_users SET role = CASE WHEN role = 'admin' THEN 'editor' ELSE 'admin' END WHERE id = ?")->execute([$id]);
                    go($self . '?users&msg=user_updated');
                }
            }
        } catch (PDOException $e) {
            $err = in_array((string)$e->getCode(), ['23000', '23505'], true) ? 'That email already has an account.' : 'Could not save. Please try again.';
        }
    }
}

// ---------- Image upload (used by the editor and the cover field) ----------
if (($_GET['action'] ?? '') === 'upload') {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) { http_response_code(403); echo '{"error":"Session expired. Reload the page."}'; exit; }
    $f = $_FILES['image'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) { http_response_code(400); echo '{"error":"Upload failed."}'; exit; }
    if ($f['size'] > 5 * 1024 * 1024) { http_response_code(400); echo '{"error":"Image is over 5 MB. Please compress it first."}'; exit; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? null;
    if (!$ext || !getimagesize($f['tmp_name'])) { http_response_code(400); echo '{"error":"Only JPG, PNG, WebP or GIF images."}'; exit; }
    if (!is_dir(BLOG_UPLOAD_DIR)) mkdir(BLOG_UPLOAD_DIR, 0755, true);
    $base = blog_slugify(pathinfo($f['name'], PATHINFO_FILENAME)) ?: 'image';
    $name = $base . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], BLOG_UPLOAD_DIR . '/' . $name)) { http_response_code(500); echo '{"error":"Could not save the image."}'; exit; }
    echo json_encode(['url' => '/images/blog/' . $name]);
    exit;
}

// ---------- Delete ----------
if (($_POST['action'] ?? '') === 'delete' && csrf_ok()) {
    blog_delete((string)$_POST['slug']);
    go($self . '?msg=deleted');
}

// ---------- Save ----------
$editing = isset($_GET['edit']) ? (string)$_GET['edit'] : null; // '' = new post
$post = null;
if ($editing !== null && $editing !== '') $post = blog_get($editing);
$post ??= ['slug' => '', 'title' => '', 'excerpt' => '', 'category' => '', 'image' => '', 'content' => '', 'date' => date('Y-m-d'),
           'status' => 'draft', 'meta_description' => '', 'author' => ''];

if (($_POST['action'] ?? '') === 'save') {
    if (!csrf_ok()) { $err = 'Session expired. Reload the page.'; }
    else {
        $old = (string)($_POST['old_slug'] ?? '');
        $title = trim((string)($_POST['title'] ?? ''));
        $slugIn = blog_slugify((string)(($_POST['slug'] ?? '') ?: $title));
        $date = (string)($_POST['date'] ?? '');
        $image = trim((string)($_POST['image'] ?? ''));
        $prev = $old !== '' ? blog_get($old) : null;
        $new = [
            'author' => $prev['author'] ?? $me['name'],
            'slug' => blog_unique_slug($slugIn, $old ?: null),
            'title' => blog_cut($title, 0, 160),
            'excerpt' => blog_cut(trim((string)($_POST['excerpt'] ?? '')), 0, 300),
            'category' => blog_cut(trim((string)($_POST['category'] ?? '')), 0, 40),
            'image' => preg_match('~^/images/blog/[A-Za-z0-9._-]+$|^/images/[A-Za-z0-9._/-]+$~', $image) ? $image : '',
            'content' => blog_sanitize((string)($_POST['content'] ?? '')),
            'date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d'),
            'status' => ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft',
            'meta_description' => blog_cut(trim((string)($_POST['meta_description'] ?? '')), 0, 200),
        ];
        if ($new['title'] === '') { $err = 'Please add a title.'; $post = $new; $post['slug'] = $old; }
        elseif (!blog_valid_slug($new['slug'])) { $err = 'The web address (slug) is not valid.'; $post = $new; }
        elseif (!blog_save($new)) { $err = 'Could not save. Ask your host to make blog-system/data writable.'; $post = $new; }
        else {
            if ($old !== '' && $old !== $new['slug']) blog_delete($old);
            go($self . '?msg=' . ($new['status'] === 'published' ? 'published' : 'saved'));
        }
        $editing = $old;
    }
}

// ---------- Views ----------
$msgs = ['saved' => 'Draft saved.', 'published' => 'Published. It is live on the blog now.', 'deleted' => 'Post deleted.',
         'user_added' => 'Team member added.', 'user_updated' => 'Team member updated.', 'user_deleted' => 'Team member removed.', 'pw_changed' => 'Password changed.'];
$msg = $msgs[$_GET['msg'] ?? ''] ?? '';

function top_bar(string $title, array $me, string $extra = ''): void {
    echo '<div class="bar"><h1>' . h($title) . '</h1><span>' . $extra
       . ($me['role'] === 'admin' ? '<a class="link" href="?users">Team</a>' : '') . '<a class="link" href="?account">My account</a>'
       . '<a class="link" href="/blog" target="_blank">View blog</a><a class="link" href="?logout=1">Log out (' . h($me['name']) . ')</a></span></div>';
}

if (isset($_GET['users']) && $isAdmin) {
    $users = $db->query('SELECT * FROM blog_users ORDER BY active DESC, name')->fetchAll(PDO::FETCH_ASSOC);
    page_top('Team');
    top_bar('Team', $me, '<a class="link" href="' . h($self) . '">&larr; Posts</a>');
    if ($msg) echo '<p class="ok">' . h($msg) . '</p>';
    if ($err) echo '<p class="err">' . h($err) . '</p>';
    echo '<p class="muted"><b>Admins</b> can write posts and manage the team. <b>Editors</b> can write and edit posts only.</p>';
    echo '<table class="card"><tr><th>Name</th><th>Role</th><th>Last login</th><th></th></tr>';
    foreach ($users as $u) {
        $mine = (int)$u['id'] === (int)$me['id'];
        $f = fn(string $a, string $btn, string $conf = '') => '<form method="post"' . ($conf ? ' onsubmit="return confirm(\'' . $conf . '\')"' : '') . '><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="' . $a . '"><input type="hidden" name="id" value="' . (int)$u['id'] . '"><button class="link' . ($a === 'user_delete' ? ' danger' : '') . '">' . $btn . '</button></form>';
        echo '<tr><td>' . h($u['name']) . ($mine ? ' <small>(you)</small>' : '') . '<br><small>' . h($u['email']) . '</small></td>'
           . '<td><span class="tag ' . ($u['active'] ? $u['role'] : 'draft') . '">' . ($u['active'] ? ucfirst($u['role']) : 'Disabled') . '</span></td>'
           . '<td>' . ($u['last_login'] ? h(blog_date($u['last_login'])) : 'Never') . '</td><td class="actions">';
        if (!$mine) {
            echo $f('user_role', $u['role'] === 'admin' ? 'Make editor' : 'Make admin') . ' ' . $f('user_toggle', $u['active'] ? 'Disable' : 'Enable') . ' '
               . '<form method="post" onsubmit="var p=prompt(\'New password (10+ characters):\');if(!p)return false;this.password.value=p"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="user_reset"><input type="hidden" name="id" value="' . (int)$u['id'] . '"><input type="hidden" name="password"><button class="link">Reset password</button></form> '
               . $f('user_delete', 'Delete', 'Remove this team member for good?');
        }
        echo '</td></tr>';
    }
    echo '</table><h2 style="margin:32px 0 12px;font-size:1.15rem">Add a team member</h2>
    <form method="post" class="card"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="user_add">
      <div class="row"><label>Name<input name="name" required maxlength="80"></label><label>Email<input type="email" name="email" required></label></div>
      <div class="row"><label>Temporary password <small>(10+ characters, share it with them privately)</small><input type="text" name="password" required minlength="10" autocomplete="off"></label>
      <label>Role<select name="role"><option value="editor">Editor</option><option value="admin">Admin</option></select></label></div>
      <button class="btn">Add team member</button></form>';
    page_bottom(); exit;
}

if (isset($_GET['account'])) {
    page_top('My account');
    top_bar('My account', $me, '<a class="link" href="' . h($self) . '">&larr; Posts</a>');
    if ($msg) echo '<p class="ok">' . h($msg) . '</p>';
    if ($err) echo '<p class="err">' . h($err) . '</p>';
    echo '<p class="muted">Signed in as <b>' . h($me['name']) . '</b> (' . h($me['email']) . ', ' . h($me['role']) . ').</p>
    <form method="post" class="card narrow"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="account_pw">
      <label>Current password<input type="password" name="current" required autocomplete="current-password"></label>
      <label>New password<input type="password" name="new" required minlength="10" autocomplete="new-password"></label>
      <label>Repeat new password<input type="password" name="new2" required autocomplete="new-password"></label>
      <button class="btn">Change password</button></form>';
    page_bottom(); exit;
}

if ($editing === null) {
    $posts = blog_all(false);
    page_top('Posts');
    top_bar('Blog posts', $me, '<a class="btn" href="?edit=">+ New post</a>');
    if ($msg) echo '<p class="ok">' . h($msg) . '</p>';
    if (!$posts) echo '<p class="muted">No posts yet. Click “New post” to write your first one.</p>';
    else {
        echo '<table class="card"><tr><th>Title</th><th>Author</th><th>Status</th><th>Date</th><th></th></tr>';
        foreach ($posts as $p) {
            $live = $p['status'] === 'published' && $p['date'] <= date('Y-m-d');
            $label = $p['status'] === 'published' ? ($live ? 'Published' : 'Scheduled') : 'Draft';
            echo '<tr><td><a href="?edit=' . h($p['slug']) . '">' . h($p['title']) . '</a></td><td>' . h($p['author'] ?? '') . '</td><td><span class="tag ' . strtolower($label) . '">' . $label . '</span></td><td>' . h(blog_date($p['date'])) . '</td><td class="actions">';
            if ($live) echo '<a class="link" href="/blog/' . h($p['slug']) . '" target="_blank">View</a> ';
            echo '<a class="link" href="?edit=' . h($p['slug']) . '">Edit</a> <form method="post" onsubmit="return confirm(\'Delete this post for good?\')"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="' . h($p['slug']) . '"><button class="link danger">Delete</button></form></td></tr>';
        }
        echo '</table>';
    }
    page_bottom(); exit;
}

// Editor
page_top($post['slug'] ? 'Edit post' : 'New post', true);
echo '<div class="bar"><h1>' . ($post['slug'] ? 'Edit post' : 'New post') . '</h1><a class="link" href="' . h($self) . '">&larr; All posts</a></div>';
if ($err) echo '<p class="err">' . h($err) . '</p>';
?>
<form method="post" id="postForm" class="editor">
  <input type="hidden" name="csrf" value="<?= csrf() ?>">
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="old_slug" value="<?= h($post['slug']) ?>">
  <input type="hidden" name="content" id="content">
  <input type="hidden" name="image" id="image" value="<?= h($post['image']) ?>">

  <label>Title<input name="title" id="title" value="<?= h($post['title']) ?>" maxlength="160" required placeholder="e.g. Best time to visit Kodaikanal"></label>
  <label>Short summary <small>(shown on the blog list and Google, about 150 characters)</small>
    <textarea name="excerpt" rows="2" maxlength="300"><?= h($post['excerpt']) ?></textarea></label>

  <div class="row">
    <label>Category<input name="category" value="<?= h($post['category']) ?>" maxlength="40" placeholder="Travel Tips"></label>
    <label>Publish date<input type="date" name="date" value="<?= h($post['date']) ?>"></label>
  </div>

  <div class="field">
    <span class="lbl">Cover image</span>
    <div class="cover">
      <img id="coverPreview" src="<?= h($post['image']) ?>" alt="" <?= $post['image'] ? '' : 'hidden' ?>>
      <div>
        <input type="file" id="coverFile" accept="image/jpeg,image/png,image/webp,image/gif">
        <button type="button" class="link danger" id="coverClear">Remove</button>
        <small id="coverMsg" class="muted">JPG, PNG or WebP under 5 MB. Wide photos (3:2) look best.</small>
      </div>
    </div>
  </div>

  <div class="field">
    <span class="lbl">Article</span>
    <div id="quill"><?= $post['content'] /* sanitised on save */ ?></div>
  </div>

  <details class="field">
    <summary>Search engine options (optional)</summary>
    <label>Web address <small>(leave blank to build it from the title)</small>
      <input name="slug" value="<?= h($post['slug']) ?>" maxlength="80" pattern="[a-z0-9\-]*" placeholder="best-time-to-visit-kodaikanal"></label>
    <label>Google description <small>(up to 160 characters; falls back to the summary)</small>
      <textarea name="meta_description" rows="2" maxlength="200"><?= h($post['meta_description']) ?></textarea></label>
  </details>

  <div class="submit">
    <button class="btn secondary" name="status" value="draft">Save as draft</button>
    <button class="btn" name="status" value="published"><?= $post['status'] === 'published' ? 'Update &amp; keep published' : 'Publish' ?></button>
  </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
(function () {
  var csrf = <?= json_encode(csrf()) ?>;
  var quill = new Quill('#quill', {
    theme: 'snow',
    placeholder: 'Write your article here…',
    modules: { toolbar: {
      container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link', 'image'], ['clean']],
      handlers: { image: function () {
        var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/*';
        inp.onchange = function () { upload(inp.files[0], function (url) {
          var r = quill.getSelection(true); quill.insertEmbed(r.index, 'image', url); quill.setSelection(r.index + 1);
        }); };
        inp.click();
      } }
    } }
  });

  function upload(file, done, onerr) {
    if (!file) return;
    var fd = new FormData(); fd.append('image', file); fd.append('csrf', csrf);
    fetch('?action=upload', { method: 'POST', body: fd }).then(function (r) { return r.json(); }).then(function (j) {
      if (j.url) done(j.url); else { alert(j.error || 'Upload failed'); if (onerr) onerr(); }
    }).catch(function () { alert('Upload failed. Check your connection.'); if (onerr) onerr(); });
  }

  var image = document.getElementById('image'), prev = document.getElementById('coverPreview');
  function showCover() { prev.hidden = !image.value; prev.src = image.value; }
  document.getElementById('coverFile').onchange = function () {
    document.getElementById('coverMsg').textContent = 'Uploading…';
    upload(this.files[0], function (u) { image.value = u; showCover(); document.getElementById('coverMsg').textContent = 'Uploaded.'; });
  };
  document.getElementById('coverClear').onclick = function () { image.value = ''; showCover(); };

  document.getElementById('postForm').addEventListener('submit', function () {
    document.getElementById('content').value = quill.getText().trim() ? quill.root.innerHTML : '';
  });
})();
</script>
<?php
page_bottom();

// ---------- Layout ----------
function page_top(string $title, bool $quill = false): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="icon" href="/favicon.ico" sizes="any"><link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="apple-touch-icon" href="/apple-touch-icon.png"><meta name="robots" content="noindex,nofollow"><title>' . h($title) . ' · Blog admin</title>';
    if ($quill) echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">';
    echo '<style>
*{box-sizing:border-box}body{margin:0;font:16px/1.5 system-ui,Segoe UI,sans-serif;background:#f4f7f8;color:#1c1f24}
main{max-width:860px;margin:0 auto;padding:24px 16px 64px}h1{font-size:1.5rem;margin:0}
.bar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin:8px 0 20px}
.bar span{display:flex;gap:14px;align-items:center}.muted,small{color:#5c6b70;font-weight:400}
.btn{display:inline-block;background:#145066;color:#fff;border:0;border-radius:8px;padding:10px 18px;font:600 15px inherit;cursor:pointer;text-decoration:none}
.btn:hover{background:#103e4e}.btn.secondary{background:#fff;color:#145066;border:1px solid #145066}
.link{background:none;border:0;color:#145066;font:inherit;cursor:pointer;padding:0;text-decoration:underline}.danger{color:#b3261e}
.card{background:#fff;border:1px solid #dfeaec;border-radius:12px;padding:20px}.narrow{max-width:380px}
table.card{width:100%;border-collapse:collapse;padding:0;overflow:hidden}th,td{text-align:left;padding:12px 16px;border-bottom:1px solid #eef3f4}th{font-size:.8rem;text-transform:uppercase;color:#5c6b70}
td.actions{white-space:nowrap;text-align:right}td.actions form{display:inline;margin-left:8px}td.actions{line-height:2}
.tag{font-size:.75rem;font-weight:600;padding:3px 10px;border-radius:99px;background:#eef3f4}.tag.published{background:#dff3e6;color:#1f6a3d}.tag.admin{background:#dbeaf3;color:#145066}.tag.editor{background:#eef3f4}.tag.scheduled{background:#fff1d6;color:#8a5a00}
label,.field{display:block;margin-bottom:18px;font-weight:600}.lbl{display:block;margin-bottom:6px;font-weight:600}
input:not([type=file]):not([type=hidden]),textarea,select{display:block;width:100%;margin-top:6px;padding:10px 12px;border:1px solid #c5d3d6;border-radius:8px;font:inherit;background:#fff}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px}.err{background:#fde8e6;color:#8c1d16;padding:10px 14px;border-radius:8px}.ok{background:#dff3e6;color:#1f6a3d;padding:10px 14px;border-radius:8px}
.cover{display:flex;gap:16px;align-items:center;flex-wrap:wrap}.cover img{width:160px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #dfeaec}.cover small{display:block;margin-top:6px}
#quill{background:#fff;min-height:320px;font-size:16px;font-weight:400}input,textarea,select{font-weight:400}table a:not(.link){color:#145066;font-weight:600;text-decoration:none}table a:not(.link):hover{text-decoration:underline}.cover input[type=file]{font:inherit;font-weight:400}.ql-editor{min-height:320px;line-height:1.7}.ql-editor img{max-width:100%}
summary{cursor:pointer;font-weight:600;margin-bottom:12px}.submit{display:flex;gap:12px;justify-content:flex-end;position:sticky;bottom:0;background:#f4f7f8;padding:14px 0;border-top:1px solid #dfeaec;margin-top:8px}
@media(max-width:600px){.row{grid-template-columns:1fr}td.actions{white-space:normal}}
</style></head><body><main>';
}

function auth_top(string $title, string $heading, string $intro, string $err): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="icon" href="/favicon.ico" sizes="any"><link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png"><link rel="apple-touch-icon" href="/apple-touch-icon.png"><meta name="robots" content="noindex,nofollow"><title>' . h($title) . ' · Blog admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"><style>
*{box-sizing:border-box}html{height:100%}body{margin:0;min-height:100vh;min-height:100dvh;display:grid;grid-template-columns:1.1fr 1fr;grid-template-rows:minmax(0,1fr);font:16px/1.5 Poppins,system-ui,sans-serif;color:#1c1f24;background:#fff}
.side{background:#103e4e;color:#fff;padding:48px;display:flex;flex-direction:column;justify-content:space-between;background-image:url(/images/enhanced/kodai-lake-misty-shore.webp);background-size:cover;background-position:center;position:relative}
.side::before{content:"";position:absolute;inset:0;background:rgba(16,62,78,.78)}.side>*{position:relative}
.side img{height:72px;width:auto;align-self:flex-start}.side h2{font-size:clamp(1.8rem,3vw,2.6rem);line-height:1.15;margin:0 0 12px;max-width:16ch}.side p{margin:0;max-width:40ch;color:rgba(255,255,255,.85)}
.panel{display:flex;align-items:center;justify-content:center;padding:32px 20px}.form{width:100%;max-width:380px}
.form h1{font-size:1.7rem;margin:0 0 6px}.intro{margin:0 0 24px;color:#3f4f54}
label{display:block;margin-bottom:18px;font-weight:600;font-size:.9rem}small{display:block;margin-top:6px;font-weight:400;color:#5c6b70}
.pw{position:relative;display:block;margin-top:6px}.pw input{width:100%;padding:12px 64px 12px 14px;border:1px solid #c5d3d6;border-radius:10px;font:inherit;background:#fff}
.pw input:focus{outline:3px solid #cdeef2;border-color:#145066}.eye{position:absolute;right:6px;top:50%;transform:translateY(-50%);background:none;border:0;color:#145066;font:600 .8rem inherit;padding:8px 10px;cursor:pointer}
.btn{background:#145066;color:#fff;border:0;border-radius:10px;padding:13px 18px;font:600 1rem inherit;cursor:pointer}.btn:hover{background:#103e4e}.full{width:100%}
.err{background:#fde8e6;color:#8c1d16;padding:10px 14px;border-radius:10px;margin:0 0 18px;font-size:.9rem}
.back{display:block;margin-top:24px;text-align:center;color:#145066;font-size:.9rem;text-decoration:none}.back:hover{text-decoration:underline}
@media(max-width:820px){body{grid-template-columns:1fr}.side{display:none}}
</style></head><body>
<aside class="side"><img src="/images/branding/logo-w.webp" alt="Cottages Kodaikanal"><div><h2>Share Kodaikanal with the world</h2><p>Write travel guides, seasonal tips and trip plans. Publish in a few clicks.</p></div></aside>
<div class="panel"><form method="post" class="form"><h1>' . h($heading) . '</h1><p class="intro">' . h($intro) . '</p>';
    if ($err) echo '<p class="err" role="alert">' . h($err) . '</p>';
}

function auth_bottom(): void {
    echo '<a class="back" href="/">&larr; Back to website</a></form></div>
<script>document.querySelectorAll(".eye").forEach(function(b){b.onclick=function(){var i=b.previousElementSibling,s=i.type==="password";i.type=s?"text":"password";b.textContent=s?"Hide":"Show"}})</script></body></html>';
}

function page_bottom(): void { echo '</main></body></html>'; }
