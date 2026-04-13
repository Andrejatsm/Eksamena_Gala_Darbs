<?php
/**
 * Language helper — provides t() translation function.
 * Detects language from: ?lang= param → $_SESSION['lang'] → 'lv' default.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allow switching via ?lang=en or ?lang=lv
if (isset($_GET['lang']) && in_array($_GET['lang'], ['lv', 'en'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + 86400 * 365, '/');
}

// Determine current language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = $_COOKIE['lang'] ?? 'lv';
    if (!in_array($_SESSION['lang'], ['lv', 'en'], true)) {
        $_SESSION['lang'] = 'lv';
    }
}

$GLOBALS['_current_lang'] = $_SESSION['lang'];

// Load translation file
$GLOBALS['_translations'] = [];
$langFile = __DIR__ . '/lang/' . $GLOBALS['_current_lang'] . '.php';
if (file_exists($langFile)) {
    $GLOBALS['_translations'] = require $langFile;
}

/**
 * Translate a key. Returns the translation or the key itself if not found.
 * Supports sprintf-style placeholders: t('welcome', $name) → "Sveiki, Jānis!"
 */
function t(string $key, ...$args): string {
    $text = $GLOBALS['_translations'][$key] ?? $key;
    if (!empty($args)) {
        $text = sprintf($text, ...$args);
    }
    return $text;
}

/**
 * Get current language code.
 */
function currentLang(): string {
    return $GLOBALS['_current_lang'];
}
