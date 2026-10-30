<?php
// Copy this file to config.php and fill in real values. config.php is git-ignored
// and blocked from web access by api/.htaccess.
return [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'your_database',
        'user'    => 'your_db_user',
        'pass'    => 'your_db_password',
        'charset' => 'utf8mb4',
    ],
    'smtp' => [
        'host'      => 'smtp.example.com',
        'port'      => 587,
        'secure'    => 'tls',               // 'tls' (STARTTLS, 587) | 'ssl' (465) | '' (none)
        'user'      => 'bookings@example.com',
        'pass'      => 'your_smtp_password',
        'from'      => 'bookings@example.com',
        'from_name' => 'Kodaikanal Cottages Website',
        'to'        => 'owner@example.com', // where new booking emails are delivered
    ],
    // Google Sheet lead log — see api/google-sheet.gs for setup. Leave 'url' empty to disable.
    'sheets' => [
        'url'    => 'https://script.google.com/macros/s/XXXXXXXX/exec',
        'secret' => 'same_value_as_SECRET_in_google-sheet.gs',
    ],
    // First blog admin, created once on the first visit to /blog-admin/. Delete the password afterwards.
    // 'blog_admin' => ['name' => 'Your Name', 'email' => 'you@example.com', 'password' => 'at least 10 characters'],
    // Extra origins allowed to POST (empty = same-origin only)
    'allowed_origins' => [],
];
