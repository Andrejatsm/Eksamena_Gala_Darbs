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

function send_email(string $to, string $subject, string $body, array $headers = []): bool
{
    if (is_smtp_mail_configured()) {
        return smtp_send_email($to, $subject, $body, $headers);
    }

    if (!function_exists('mail')) {
        return false;
    }

    return @mail($to, $subject, $body, implode("\r\n", $headers));
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
