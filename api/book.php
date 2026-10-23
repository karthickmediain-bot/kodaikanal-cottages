<?php
// POST /api/book.php - stores a hero-form booking enquiry in MySQL and emails it via SMTP.
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $data): void { http_response_code($code); echo json_encode($data); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, ['ok' => false, 'error' => 'POST only']);

$cfgFile = __DIR__ . '/config.php';
if (!is_file($cfgFile)) respond(500, ['ok' => false, 'error' => 'Server not configured']);
$cfg = require $cfgFile;
require __DIR__ . '/smtp.php';

// Same-origin check
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $self = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '');
    if ($origin !== $self && !in_array($origin, $cfg['allowed_origins'] ?? [], true)) respond(403, ['ok' => false, 'error' => 'Forbidden']);
}

$in = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($in)) $in = $_POST;

// Honeypot
if (!empty($in['website'])) respond(200, ['ok' => true]);

$allowed = ['Hotels', 'Resorts', 'Home Stay', 'Dormitory', 'Tent Stay', 'Group Stay', 'Lodge', 'Villas'];
$category = in_array($in['category'] ?? '', $allowed, true) ? $in['category'] : 'Hotels';
$phone = (string) ($in['phone'] ?? '');
if (!preg_match('/^[0-9]{10}$/', $phone)) respond(422, ['ok' => false, 'error' => 'Enter a 10-digit mobile number']);

$date = function ($v) { $v = (string) $v; $d = DateTime::createFromFormat('Y-m-d', $v); return ($d && $d->format('Y-m-d') === $v) ? $v : null; };
$checkIn = $date($in['check_in'] ?? '');
$checkOut = $date($in['check_out'] ?? '');
$guests = isset($in['guests']) && ctype_digit((string) $in['guests']) ? min((int) $in['guests'], 1000) : null;
$sourceUrl = mb_substr((string) ($in['source_url'] ?? ''), 0, 255);
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

try {
    $d = $cfg['db'];
    $pdo = new PDO("mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}", $d['user'], $d['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Basic rate limit: 5 per IP per 10 minutes
    $rl = $pdo->prepare('SELECT COUNT(*) FROM booking_enquiries WHERE ip = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)');
    $rl->execute([$ip]);
    if ((int) $rl->fetchColumn() >= 5) respond(429, ['ok' => false, 'error' => 'Too many requests. Please call us.']);

    $st = $pdo->prepare('INSERT INTO booking_enquiries (category, check_in, check_out, guests, phone, source_url, ip, user_agent) VALUES (?,?,?,?,?,?,?,?)');
    $st->execute([$category, $checkIn, $checkOut, $guests, $phone, $sourceUrl, $ip, $ua]);
    $id = (int) $pdo->lastInsertId();
} catch (Throwable $e) {
    error_log('book.php DB: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Could not save your request']);
}

$body = "New booking enquiry #$id\n\n"
      . "Category : $category\n"
      . 'Check-in : ' . ($checkIn ?? '-') . "\n"
      . 'Check-out: ' . ($checkOut ?? '-') . "\n"
      . 'Guests   : ' . ($guests ?? '-') . "\n"
      . "Phone    : $phone\n"
      . "Page     : $sourceUrl\n"
      . "IP       : $ip\n";
try {
    smtp_send($cfg['smtp'], "New booking enquiry #$id - $category", $body);
    $pdo->prepare('UPDATE booking_enquiries SET email_sent = 1 WHERE id = ?')->execute([$id]);
} catch (Throwable $e) {
    error_log('book.php SMTP: ' . $e->getMessage()); // enquiry is still saved
}

// Append to Google Sheet (via Apps Script web app). Failure is logged, never blocks the lead.
if (!empty($cfg['sheets']['url'])) {
    try {
        $ch = curl_init($cfg['sheets']['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'secret' => $cfg['sheets']['secret'] ?? '', 'id' => $id, 'category' => $category,
                'check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $guests,
                'phone' => $phone, 'source_url' => $sourceUrl, 'ip' => $ip,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true, // Apps Script answers POSTs with a redirect
            CURLOPT_TIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false || $status >= 400 || strpos((string) $resp, '"ok":true') === false) {
            error_log("book.php Sheets: status $status, response " . substr((string) $resp, 0, 200));
        }
    } catch (Throwable $e) {
        error_log('book.php Sheets: ' . $e->getMessage());
    }
}

respond(200, ['ok' => true, 'id' => $id]);
