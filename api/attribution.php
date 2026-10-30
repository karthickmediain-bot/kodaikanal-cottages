<?php
// Shared by the lead endpoints: cleans the attribution the browser sends (see js/main.js,
// window.ckAttribution) and works out channel / source / medium and the page group.
declare(strict_types=1);

function attr_clip($v, int $max): ?string {
    $v = trim((string) ($v ?? ''));
    return $v === '' ? null : mb_substr($v, 0, $max);
}

/** Path only (no host, query or hash), lowercased, "/" for the homepage. */
function attr_path($v): ?string {
    $p = parse_url((string) ($v ?? ''), PHP_URL_PATH);
    if (!is_string($p) || $p === '') return null;
    $p = strtolower(preg_replace('#(/index)?(\.html)?/?$#i', '', $p));
    return mb_substr($p === '' ? '/' : $p, 0, 255);
}

/** Which part of the site a page belongs to. */
function attr_page_group(?string $path): ?string {
    if ($path === null) return null;
    $rules = [
        'home'         => '#^/$#',
        'blog'         => '#^/blog(/|$)#',
        'cottage-size' => '#^/[2-5]-bedroom-cottage#',
        'travel-guide' => '#^/(kodaikanal-trip|places-to-visit|kodaikanal-weather|how-to-reach|water-falls)#',
        'cabs'         => '#^/(cabs-in|car-hire|car-rental|kodaikanal-call-taxi|kodaikanal-jeep-safari|[a-z-]+-to-kodaikanal$)#',
        'tours'        => '#(tour|trip-package|travels-in)#',
        'stays'        => '#^/(home-stay|hotels|resorts|dormitory|tentstay|groupstay|villas|lodges)-in#',
        'info'         => '#^/(about|contact|privacy|terms|faq)#',
    ];
    foreach ($rules as $group => $re) if (preg_match($re, $path)) return $group;
    return 'other';
}

function attr_device(string $ua): string {
    if (preg_match('/ipad|tablet/i', $ua)) return 'tablet';
    if (preg_match('/mobi|android|iphone/i', $ua)) return 'mobile';
    return 'desktop';
}

/**
 * Channel / source / medium for one touch, from utm_* fields, ad click ids and the external referrer.
 * $t keys: utm_source, utm_medium, gclid, gbraid, wbraid, fbclid, msclkid, ttclid, referrer_host
 * Returns [channel, source, medium].
 */
function attr_classify(array $t): array {
    $src = strtolower((string) ($t['utm_source'] ?? ''));
    $med = strtolower((string) ($t['utm_medium'] ?? ''));
    $ref = preg_replace('/^(www|m|l|lm)\./', '', strtolower((string) ($t['referrer_host'] ?? '')));
    $paidMed = (bool) preg_match('/^(cpc|ppc|paid|paidsearch|paid[-_ ]?social|display|cpm|cpv|retargeting|ads?)$/', $med);
    $socialSrc = '/(facebook|^fb$|instagram|^ig$|meta|tiktok|twitter|^x$|linkedin|pinterest|youtube|snapchat|whatsapp)/';
    $metaSrc = (bool) preg_match('/(facebook|^fb$|instagram|^ig$|meta)/', $src);

    // Ad click ids are the strongest signal.
    if (!empty($t['gclid']) || !empty($t['gbraid']) || !empty($t['wbraid'])) return ['Paid Search', $src ?: 'google', $med ?: 'cpc'];
    if (!empty($t['msclkid'])) return ['Paid Search', $src ?: 'bing', $med ?: 'cpc'];
    if (!empty($t['ttclid']))  return ['Paid Social', $src ?: 'tiktok', $med ?: 'paid_social'];

    // Meta adds fbclid to organic shares too, so it only counts as paid when utm_medium says so.
    if (!empty($t['fbclid']) || $metaSrc) {
        return [$paidMed ? 'Paid Social' : 'Organic Social', $src ?: 'meta', $med ?: ($paidMed ? 'paid_social' : 'social')];
    }

    if ($src !== '' || $med !== '') {
        if ($med === 'email' || $med === 'newsletter' || $src === 'email') return ['Email', $src ?: 'email', $med ?: 'email'];
        if ($paidMed) {
            if (preg_match($socialSrc, $src)) return ['Paid Social', $src, $med];
            if (preg_match('/^(display|cpm|cpv)$/', $med)) return ['Paid Other', $src ?: '(not set)', $med];
            return ['Paid Search', $src ?: '(not set)', $med];
        }
        if ($med === 'social' || preg_match($socialSrc, $src)) return ['Organic Social', $src ?: '(not set)', $med ?: 'social'];
        if ($med === 'organic') return ['Organic Search', $src ?: '(not set)', 'organic'];
        if ($med === 'referral') return ['Referral', $src ?: '(not set)', 'referral'];
        return ['Other', $src ?: '(not set)', $med ?: '(not set)'];
    }

    if ($ref !== '') {
        if (preg_match('/^(google|bing|duckduckgo|yahoo|ecosia|baidu|yandex|ask|brave|startpage)\./', $ref)) {
            return ['Organic Search', explode('.', $ref)[0], 'organic'];
        }
        if (preg_match('/(facebook|instagram|^t\.co$|twitter|^x\.com$|linkedin|lnkd\.in|pinterest|youtube|youtu\.be|snapchat|tiktok|whatsapp|wa\.me)/', $ref)) {
            return ['Organic Social', $ref, 'social'];
        }
        return ['Referral', $ref, 'referral'];
    }
    return ['Direct', '(direct)', '(none)'];
}

/**
 * Turns the posted `attribution` object into column values.
 * $host is this site's host, so internal referrers are dropped.
 */
function attr_build(?array $a, string $host, string $ua): array {
    $a = is_array($a) ? $a : [];
    $site = preg_replace('/^www\./', '', preg_replace('/:\d+$/', '', strtolower($host)));

    $clean = function ($t) use ($site): array {
        $t = is_array($t) ? $t : [];
        $refHost = strtolower((string) parse_url((string) ($t['referrer'] ?? ''), PHP_URL_HOST));
        $same = $refHost !== '' && preg_replace('/^www\./', '', $refHost) === $site;
        $o = [
            'utm_source'    => attr_clip($t['utm_source'] ?? null, 80),
            'utm_medium'    => attr_clip($t['utm_medium'] ?? null, 80),
            'campaign'      => attr_clip($t['utm_campaign'] ?? null, 150),
            'utm_term'      => attr_clip($t['utm_term'] ?? null, 150),
            'utm_content'   => attr_clip($t['utm_content'] ?? null, 150),
            'utm_id'        => attr_clip($t['utm_id'] ?? null, 80),
            'gclid'         => attr_clip($t['gclid'] ?? null, 255),
            'gbraid'        => attr_clip($t['gbraid'] ?? null, 255),
            'wbraid'        => attr_clip($t['wbraid'] ?? null, 255),
            'fbclid'        => attr_clip($t['fbclid'] ?? null, 255),
            'msclkid'       => attr_clip($t['msclkid'] ?? null, 255),
            'ttclid'        => attr_clip($t['ttclid'] ?? null, 255),
            'landing_url'   => attr_clip($t['landing_url'] ?? null, 500),
            'referrer_url'  => $same ? null : attr_clip($t['referrer'] ?? null, 500),
            'referrer_host' => ($same || $refHost === '') ? null : mb_substr($refHost, 0, 190),
            'ts'            => attr_clip($t['ts'] ?? null, 30),
        ];
        $o['landing_path'] = attr_path($o['landing_url']);
        [$o['channel'], $o['source'], $o['medium']] = attr_classify($o);
        return $o;
    };

    $l = $clean($a['last'] ?? null);
    $f = !empty($a['first']) ? $clean($a['first']) : $l;
    $ts = $f['ts'] ? strtotime($f['ts']) : false;
    $pageUrl = attr_clip($a['page_url'] ?? null, 500);
    $path = attr_path($pageUrl);

    return [
        'source_path' => $path, 'page_group' => attr_page_group($path),
        'channel' => $l['channel'], 'source' => $l['source'], 'medium' => $l['medium'], 'campaign' => $l['campaign'],
        'gclid' => $l['gclid'], 'fbclid' => $l['fbclid'],
        'landing_path' => $l['landing_path'], 'referrer_host' => $l['referrer_host'],
        'first_channel' => $f['channel'], 'first_source' => $f['source'],
        'visitor_id' => attr_clip($a['visitor_id'] ?? null, 40),
        // rare tracking detail: goes into the `extra` JSON column, not columns of its own
        'tracking' => array_filter([
            'page_title' => attr_clip($a['page_title'] ?? null, 200), 'session_id' => attr_clip($a['session_id'] ?? null, 40),
            'device' => attr_device($ua),
            'utm_term' => $l['utm_term'], 'utm_content' => $l['utm_content'], 'utm_id' => $l['utm_id'],
            'gbraid' => $l['gbraid'], 'wbraid' => $l['wbraid'], 'msclkid' => $l['msclkid'], 'ttclid' => $l['ttclid'],
            'landing_url' => $l['landing_url'], 'referrer_url' => $l['referrer_url'],
            'first_campaign' => $f['campaign'], 'first_landing_path' => $f['landing_path'],
            'first_touch_at' => $ts ? date('Y-m-d H:i:s', $ts) : null,
        ], fn($v) => $v !== null && $v !== ''),
    ];
}
