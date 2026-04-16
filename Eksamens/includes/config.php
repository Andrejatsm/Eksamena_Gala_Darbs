<?php
/**
 * Application Configuration
 * Defines base paths and URLs for consistent link generation
 */

// Detect if running on production server or local environment
$isProduction = ($_SERVER['HTTP_HOST'] ?? '') === 'kristovskis.lv';

// Define base path based on environment
if ($isProduction) {
    // Production server path
    define('BASE_PATH', '/4pt/rackovs/Eksamens/');
} else {
    // Local development - calculate dynamically
    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $depth = $scriptDir === '' ? 0 : substr_count($scriptDir, '/') + 1;
    define('BASE_PATH', str_repeat('../', $depth));
}

// Export for use in templates
$basePath = BASE_PATH;

// Load translation system - always available
require_once __DIR__ . '/lang.php';
