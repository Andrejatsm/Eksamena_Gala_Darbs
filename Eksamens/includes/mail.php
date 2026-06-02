<?php

function get_email_config(): array
{
    return [
        'host' => getenv('EMAIL_HOST') ?: '',
        'port' => getenv('EMAIL_PORT') ?: '465',
        'username' => getenv('EMAIL_USERNAME') ?: 'saprastsinfo@gmail.com',
        'password' => getenv('EMAIL_PASSWORD') ?: '',
        'from_address' => getenv('EMAIL_FROM_ADDRESS') ?: 'noreply@saprasts.local',
        'from_name' => getenv('EMAIL_FROM_NAME') ?: 'Saprasts',
        'use_ssl' => filter_var(getenv('EMAIL_USE_SSL') ?: '1', FILTER_VALIDATE_BOOLEAN),
        'use_tls' => filter_var(getenv('EMAIL_USE_TLS') ?: '0', FILTER_VALIDATE_BOOLEAN),
    ];
}

function is_smtp_mail_configured(): bool
{
    $cfg = get_email_config();
    return !empty($cfg['host']) && !empty($cfg['username']) && !empty($cfg['password']);
}

function encode_mime_header(string $text): string
{
    if (function_exists('mb_encode_mimeheader')) {
        return mb_encode_mimeheader($text, 'UTF-8', 'B', "\r\n");
    }
    return $text;
}

function send_email(string $to, string $subject, string $body, array $headers = []): bool
{
    $subject = encode_mime_header($subject);

    if (is_smtp_mail_configured()) {
        return smtp_send_email($to, $subject, $body, $headers);
    }

    if (!function_exists('mail')) {
        return false;
    }

    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

function send_html_email(string $to, string $subject, string $htmlBody, array $headers = []): bool
{
    $cfg = get_email_config();
    $headers[] = 'From: ' . $cfg['from_name'] . ' <' . $cfg['from_address'] . '>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    return send_email($to, $subject, $htmlBody, $headers);
}

function smtp_send_email(string $to, string $subject, string $body, array $headers = []): bool
{
    $cfg = get_email_config();
    $remoteHost = sprintf('%s://%s:%s', $cfg['use_ssl'] ? 'ssl' : 'tcp', $cfg['host'], $cfg['port']);
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $fp = stream_socket_client($remoteHost, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
    if (!is_resource($fp)) {
        return false;
    }

    $response = smtp_get_response($fp);
    if (strpos($response, '220') !== 0) {
        fclose($fp);
        return false;
    }

    $domain = parse_url($cfg['host'], PHP_URL_HOST) ?: $cfg['host'];
    smtp_send_command($fp, "EHLO {$domain}");

    if (!$cfg['use_ssl'] && $cfg['use_tls']) {
        smtp_send_command($fp, 'STARTTLS');
        stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        smtp_send_command($fp, "EHLO {$domain}");
    }

    smtp_send_command($fp, 'AUTH LOGIN');
    smtp_send_command($fp, base64_encode($cfg['username']));
    smtp_send_command($fp, base64_encode($cfg['password']));

    smtp_send_command($fp, 'MAIL FROM:<' . $cfg['from_address'] . '>');
    smtp_send_command($fp, 'RCPT TO:<' . $to . '>');
    smtp_send_command($fp, 'DATA');

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
    fwrite($fp, $message . "\r\n.\r\n");
    $response = smtp_get_response($fp);
    if (strpos($response, '250') !== 0 && strpos($response, '354') !== 0) {
        fclose($fp);
        return false;
    }

    smtp_send_command($fp, 'QUIT');
    fclose($fp);

    return true;
}

function smtp_get_response($fp): string
{
    $response = '';
    while (($line = fgets($fp, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function smtp_send_command($fp, string $command): string
{
    fwrite($fp, $command . "\r\n");
    return smtp_get_response($fp);
}

function build_approval_email_html(string $recipientName, string $loginUrl, string $lang = 'lv'): string
{
    $isDark = false;
    $heading = $lang === 'lv' ? 'Jūsu profils ir apstiprināts' : 'Your profile has been approved';
    $greeting = $lang === 'lv' ? 'Labdien' : 'Hello';
    $intro = $lang === 'lv' 
        ? 'Jūsu psihologa profils platformā Saprasts ir veiksmīgi apstiprināts. Tagad varat ielogoties un sākt pārvaldīt savu grafiku, pieņemt pierakstus un publicēt rakstus.'
        : 'Your psychologist profile on Saprasts has been approved. You can now log in and start managing your schedule, accepting appointments, and publishing articles.';
    $buttonText = $lang === 'lv' ? 'Pieteikties Saprastā' : 'Log in to Saprasts';
    $supportText = $lang === 'lv'
        ? 'Ja Jūs nedarbojāt šo pieprasījumu, lūdzu sazinieties ar mūsu atbalstu.'
        : 'If you did not request this, please contact our support team.';
    $regards = $lang === 'lv' ? 'Ar cieņu' : 'Best regards';
    $team = 'Saprasts';
    
    return <<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>$heading</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
    <div style="max-width:600px;margin:0 auto;padding:20px;">
        <div style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.1);">
            <div style="padding:40px;background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#ffffff;text-align:center;">
                <div style="font-size:48px;margin-bottom:16px;">✓</div>
                <h1 style="margin:0;font-size:28px;font-weight:700;line-height:1.3;">$heading</h1>
            </div>
            <div style="padding:40px;color:#1f2937;">
                <p style="margin:0 0 20px;font-size:16px;line-height:1.6;">
                    $greeting $recipientName,
                </p>
                <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#4b5563;">
                    $intro
                </p>
                <div style="text-align:center;margin:32px 0;">
                    <a href="$loginUrl" style="display:inline-block;padding:14px 32px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;transition:background 0.3s;">
                        $buttonText
                    </a>
                </div>
                <p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#6b7280;">
                    $supportText
                </p>
                <div style="margin-top:32px;padding-top:20px;border-top:1px solid #e5e7eb;">
                    <p style="margin:0;font-size:13px;color:#9ca3af;">
                        $regards,<br>
                        <strong style="color:#1f2937;">$team</strong>
                    </p>
                </div>
            </div>
        </div>
        <div style="text-align:center;margin-top:20px;font-size:12px;color:#9ca3af;">
            <p style="margin:0;">&copy; 2024-2026 Saprasts. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
}
