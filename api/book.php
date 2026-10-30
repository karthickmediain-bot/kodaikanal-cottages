<?php
// POST /api/book.php - stores a lead (any form, any page) with its source page and traffic source in MySQL, and emails it via SMTP.
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

require __DIR__ . '/attribution.php';

$allowed = ['Hotels', 'Resorts', 'Home Stay', 'Dormitory', 'Tent Stay', 'Group Stay', 'Lodge', 'Villas'];
$category = in_array($in['category'] ?? '', $allowed, true) ? $in['category'] : null;

// What kind of submission this is. Click events (WhatsApp / call) carry no phone number.
$leadTypes = ['hero_booking', 'trip_enquiry', 'contact_form', 'page_quote_form', 'cab_request', 'whatsapp_click', 'call_click', 'other'];
$leadType = in_array($in['lead_type'] ?? '', $leadTypes, true) ? $in['lead_type'] : 'hero_booking';
if ($leadType === 'hero_booking' && $category === null) $category = 'Hotels';
$isClick = in_array($leadType, ['whatsapp_click', 'call_click'], true);

$phone = preg_replace('/[\s\-+]/', '', (string) ($in['phone'] ?? ''));
$phone = preg_replace('/^(91|0)(?=[0-9]{10}$)/', '', $phone);
// Some forms (cab requests, quote cards) go straight to WhatsApp and never ask for a number.
$phoneOptional = $isClick || in_array($leadType, ['page_quote_form', 'cab_request'], true);
if ($phone === '' && $phoneOptional) $phone = null;
elseif (!preg_match('/^[0-9]{10}$/', (string) $phone)) {
    if ($phoneOptional) $phone = null;
    else respond(422, ['ok' => false, 'error' => 'Enter a 10-digit mobile number']);
}

$email = attr_clip($in['email'] ?? null, 190);
if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = null;

$date = function ($v) { $v = (string) $v; $d = DateTime::createFromFormat('Y-m-d', $v); return ($d && $d->format('Y-m-d') === $v) ? $v : null; };
$checkIn = $date($in['check_in'] ?? '');
$checkOut = $date($in['check_out'] ?? '');
$guests = isset($in['guests']) && ctype_digit((string) $in['guests']) ? min((int) $in['guests'], 1000) : null;

$host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
$sourceUrl = mb_substr((string) ($in['source_url'] ?? ''), 0, 500);
$srcParts = parse_url($sourceUrl);
if (!$srcParts || !in_array($srcParts['scheme'] ?? '', ['http', 'https'], true) || strcasecmp($srcParts['host'] ?? '', $host) !== 0) {
    $sourceUrl = '';
}
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

// Attribution: which page, and how the visitor found the site (ads, Meta, organic, direct...).
$att = is_array($in['attribution'] ?? null) ? $in['attribution'] : [];
if ($sourceUrl !== '') $att['page_url'] = $sourceUrl;
$a = attr_build($att, $host, $ua);

// Rare form fields and tracking detail share one JSON column (`extra`).
$formKeys = ['service_interest' => 160, 'traveller_type' => 40, 'trip_type' => 60, 'pickup' => 80, 'vehicle' => 60, 'stay_type' => 60];
$known = ['lead_type', 'form_id', 'category', 'name', 'phone', 'email', 'message', 'check_in', 'check_out', 'guests',
          'source_url', 'attribution', 'website'];
$form = [];
foreach ($formKeys as $k => $max) {
    $v = attr_clip($in[$k] ?? null, $max);
    if ($v !== null) $form[$k] = $v;
}
$others = array_diff_key($in, array_flip($known), $formKeys);
$form += array_slice(array_filter($others, 'is_scalar'), 0, 15);
$tracking = $a['tracking'];
unset($a['tracking']);
$extra = array_filter(['form' => $form, 'tracking' => $tracking]);
$extraJson = $extra ? mb_substr(json_encode($extra, JSON_UNESCAPED_UNICODE), 0, 4000) : null;

$row = [
    'lead_type' => $leadType, 'form_id' => attr_clip($in['form_id'] ?? null, 60),
    'name' => attr_clip($in['name'] ?? null, 120), 'phone' => $phone, 'email' => $email, 'message' => attr_clip($in['message'] ?? null, 2000),
    'category' => $category, 'check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $guests,
    'source_url' => $sourceUrl !== '' ? $sourceUrl : null,
] + $a + ['extra' => $extraJson, 'ip' => $ip, 'user_agent' => $ua];

try {
    $d = $cfg['db'];
    $pdo = new PDO("mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}", $d['user'], $d['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Basic rate limit per IP per 10 minutes: 5 form leads, 40 button clicks
    $rl = $pdo->prepare('SELECT COUNT(*) FROM booking_enquiries WHERE ip = ? AND created_at > (NOW() - INTERVAL 10 MINUTE) AND lead_type ' . ($isClick ? '' : 'NOT ') . "IN ('whatsapp_click','call_click')");
    $rl->execute([$ip]);
    if ((int) $rl->fetchColumn() >= ($isClick ? 40 : 5)) respond(429, ['ok' => false, 'error' => 'Too many requests. Please call us.']);

    $cols = array_keys($row);
    $st = $pdo->prepare('INSERT INTO booking_enquiries (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
    $st->execute(array_values($row));
    $id = (int) $pdo->lastInsertId();
} catch (Throwable $e) {
    error_log('book.php DB: ' . $e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Could not save your request']);
}

// Clicks on WhatsApp / call buttons are stored for reporting only: no email, no sheet row.
if ($isClick) respond(200, ['ok' => true, 'id' => $id]);

$label = $category ?? $leadType;
$line = fn(string $k, $v) => str_pad($k, 9) . ': ' . (($v === null || $v === '') ? '-' : $v) . "
";
$body = "New enquiry #$id ($leadType)

"
      . $line('Category', $category) . $line('Interest', $form['service_interest'] ?? null)
      . $line('Name', $row['name']) . $line('Phone', $phone) . $line('Email', $email)
      . $line('Check-in', $checkIn) . $line('Check-out', $checkOut) . $line('Guests', $guests)
      . $line('Trip', $form['trip_type'] ?? null) . $line('Pickup', $form['pickup'] ?? null) . $line('Vehicle', $form['vehicle'] ?? null)
      . $line('Message', $row['message'])
      . "
"
      . $line('Page', $row['source_path']) . $line('Group', $row['page_group'])
      . $line('Channel', $row['channel']) . $line('Source', $row['source'] . ' / ' . $row['medium'])
      . $line('Campaign', $row['campaign']) . $line('Landed on', $row['landing_path'])
      . $line('1st visit', $row['first_channel'] . ' / ' . $row['first_source'])
      . $line('IP', $ip);
try {
    smtp_send($cfg['smtp'], "New enquiry #$id - $label" . ($row['channel'] ? " [{$row['channel']}]" : ''), $body);
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
                'lead_type' => $leadType, 'name' => $row['name'], 'email' => $email, 'message' => $row['message'],
                'source_path' => $row['source_path'], 'page_group' => $row['page_group'],
                'channel' => $row['channel'], 'source' => $row['source'], 'medium' => $row['medium'], 'campaign' => $row['campaign'],
                'first_channel' => $row['first_channel'], 'first_source' => $row['first_source'],
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
