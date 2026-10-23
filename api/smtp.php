<?php
// Minimal dependency-free SMTP sender (AUTH LOGIN, STARTTLS / implicit SSL).
function smtp_send(array $c, string $subject, string $body, ?string $replyTo = null): void
{
    $host = ($c['secure'] === 'ssl' ? 'ssl://' : '') . $c['host'];
    $fp = stream_socket_client("$host:{$c['port']}", $errno, $errstr, 15);
    if (!$fp) throw new RuntimeException("SMTP connect failed: $errstr");
    stream_set_timeout($fp, 15);

    $read = function () use ($fp): string {
        $out = '';
        while (($line = fgets($fp, 515)) !== false) {
            $out .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        return $out;
    };
    $cmd = function (string $line, array $ok) use ($fp, $read): string {
        fwrite($fp, $line . "\r\n");
        $r = $read();
        if (!in_array((int) substr($r, 0, 3), $ok, true)) throw new RuntimeException('SMTP error: ' . trim($r));
        return $r;
    };

    $name = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $read();
    $cmd("EHLO $name", [250]);
    if ($c['secure'] === 'tls') {
        $cmd('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('TLS failed');
        $cmd("EHLO $name", [250]);
    }
    $cmd('AUTH LOGIN', [334]);
    $cmd(base64_encode($c['user']), [334]);
    $cmd(base64_encode($c['pass']), [235]);
    $cmd("MAIL FROM:<{$c['from']}>", [250]);
    $cmd("RCPT TO:<{$c['to']}>", [250, 251]);
    $cmd('DATA', [354]);

    $h  = 'From: =?UTF-8?B?' . base64_encode($c['from_name']) . "?= <{$c['from']}>\r\n";
    $h .= "To: <{$c['to']}>\r\n";
    if ($replyTo) $h .= "Reply-To: <$replyTo>\r\n";
    $h .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
    $h .= 'Date: ' . date('r') . "\r\nMIME-Version: 1.0\r\n";
    $h .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $cmd($h . chunk_split(base64_encode($body)) . "\r\n.", [250]);
    fwrite($fp, "QUIT\r\n");
    fclose($fp);
}
