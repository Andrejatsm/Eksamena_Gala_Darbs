<?php
/**
 * Šifrēšanas utilītas — AES-256-CBC ar HMAC verifikāciju.
 * Atslēga tiek automātiski ģenerēta un saglabāta .saprasts_key failā.
 * SVARĪGI: Neiekļaujiet .saprasts_key failā versiju kontrolē (Git)!
 */

$_saprastsKeyFile = __DIR__ . DIRECTORY_SEPARATOR . '.saprasts_key';
if (!file_exists($_saprastsKeyFile)) {
    file_put_contents($_saprastsKeyFile, bin2hex(random_bytes(32)));
}
define('SAPRASTS_ENC_KEY', hex2bin(trim(file_get_contents($_saprastsKeyFile))));
unset($_saprastsKeyFile);

/**
 * Šifrē tekstu ar AES-256-CBC + HMAC-SHA256.
 */
function saprasts_encrypt(string $plaintext): string {
    $iv = random_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', SAPRASTS_ENC_KEY, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext === false) {
        return $plaintext;
    }
    $hmac = hash_hmac('sha256', $iv . $ciphertext, SAPRASTS_ENC_KEY, true);
    return base64_encode($iv . $ciphertext . $hmac);
}

/**
 * Atšifrē tekstu. Ja ievade nav šifrēta (vecie ieraksti), atgriež kā ir.
 */
function saprasts_decrypt(string $encoded): string {
    $data = base64_decode($encoded, true);
    if ($data === false || strlen($data) < 49) {
        return $encoded; // Nav šifrēts — atgriežam oriģinālu
    }
    $iv = substr($data, 0, 16);
    $hmac = substr($data, -32);
    $ciphertext = substr($data, 16, -32);

    $expectedHmac = hash_hmac('sha256', $iv . $ciphertext, SAPRASTS_ENC_KEY, true);
    if (!hash_equals($expectedHmac, $hmac)) {
        return $encoded; // HMAC nesakrīt — nav šifrēts
    }

    $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', SAPRASTS_ENC_KEY, OPENSSL_RAW_DATA, $iv);
    return $plaintext !== false ? $plaintext : $encoded;
}
