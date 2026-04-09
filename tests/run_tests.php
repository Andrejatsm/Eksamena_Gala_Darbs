<?php
/**
 * Saprasts — vienkāršs testu runners.
 * Palaist: d:\xampp\php\php.exe tests/run_tests.php
 *
 * Katrs tests ir funkcija, kas met Exception, ja neizpildās.
 */

$passed = 0;
$failed = 0;
$errors = [];

function assert_true(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException("FAIL: {$message}");
    }
}

function assert_equals($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException("FAIL: {$message} (expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ")");
    }
}

function assert_contains(string $haystack, string $needle, string $message): void {
    if (strpos($haystack, $needle) === false) {
        throw new RuntimeException("FAIL: {$message} ('{$needle}' not found)");
    }
}

// ──────────────────────────────────────────────
// Test: .env loader
// ──────────────────────────────────────────────
function test_env_loader(): void {
    // Temporarily set a test env value and verify env.php loads it
    $envFile = __DIR__ . '/../.env';
    assert_true(is_file($envFile), '.env file exists');

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    assert_true($lines !== false && count($lines) > 0, '.env file has content');

    $foundGemini = false;
    $foundStripe = false;
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), 'GEMINI_API_KEY=')) $foundGemini = true;
        if (str_starts_with(trim($line), 'STRIPE_SECRET_KEY=')) $foundStripe = true;
    }
    assert_true($foundGemini, '.env contains GEMINI_API_KEY');
    assert_true($foundStripe, '.env contains STRIPE_SECRET_KEY');
}

// ──────────────────────────────────────────────
// Test: Encryption round-trip
// ──────────────────────────────────────────────
function test_encryption_roundtrip(): void {
    require_once __DIR__ . '/../includes/encryption.php';

    $plaintext = 'Sveiki, šis ir tests! 🧠';
    $encrypted = saprasts_encrypt($plaintext);
    assert_true($encrypted !== $plaintext, 'Encrypted text differs from plaintext');
    assert_true(base64_decode($encrypted, true) !== false, 'Encrypted format is valid base64');

    $decrypted = saprasts_decrypt($encrypted);
    assert_equals($plaintext, $decrypted, 'Decrypted text matches original');

    // Backward compat: unencrypted text returns as-is
    $plain = 'Just plain text';
    $result = saprasts_decrypt($plain);
    assert_equals($plain, $result, 'Plain text passes through decrypt unchanged');
}

// ──────────────────────────────────────────────
// Test: Encryption tamper detection
// ──────────────────────────────────────────────
function test_encryption_tamper_detection(): void {
    require_once __DIR__ . '/../includes/encryption.php';

    $encrypted = saprasts_encrypt('test data');
    // Tamper with the base64 encoded data
    $data = base64_decode($encrypted);
    if (strlen($data) > 20) {
        // Corrupt a byte in the ciphertext
        $data[17] = chr((ord($data[17]) + 1) % 256);
        $tampered = base64_encode($data);
        $result = saprasts_decrypt($tampered);
        // Should return the tampered string (fail gracefully), not the original plaintext
        assert_true($result !== 'test data', 'Tampered ciphertext does not decrypt to original');
    }
}

// ──────────────────────────────────────────────
// Test: DB connection
// ──────────────────────────────────────────────
function test_db_connection(): void {
    // We can't require db.php directly as it runs migrations.
    // Just test that mysqli can connect.
    $conn = @new mysqli('localhost', 'root', '', 'saprasts');
    assert_true($conn->connect_error === null, 'Database connection succeeds');
    $conn->close();
}

// ──────────────────────────────────────────────
// Test: DB tables exist
// ──────────────────────────────────────────────
function test_db_tables_exist(): void {
    $conn = new mysqli('localhost', 'root', '', 'saprasts');
    assert_true($conn->connect_error === null, 'DB connected for table check');

    $requiredTables = [
        'accounts', 'appointments', 'psychologist_profiles',
        'availability_slots', 'tests', 'articles',
        'chat_messages', 'video_rooms'
    ];

    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    foreach ($requiredTables as $table) {
        assert_true(in_array($table, $tables), "Table '{$table}' exists");
    }
    $conn->close();
}

// ──────────────────────────────────────────────
// Test: API handler - no API key = graceful error
// ──────────────────────────────────────────────
function test_ai_handler_no_hardcoded_key(): void {
    $content = file_get_contents(__DIR__ . '/../api/ai_handler.php');
    assert_true($content !== false, 'ai_handler.php readable');
    assert_true(!str_contains($content, 'AIzaSy'), 'No hardcoded Gemini API key in ai_handler.php');
}

// ──────────────────────────────────────────────
// Test: Checkout - no hardcoded Stripe key
// ──────────────────────────────────────────────
function test_checkout_no_hardcoded_key(): void {
    $content = file_get_contents(__DIR__ . '/../api/checkout.php');
    assert_true($content !== false, 'checkout.php readable');
    assert_true(!str_contains($content, 'sk_test_'), 'No hardcoded Stripe key in checkout.php');
}

// ──────────────────────────────────────────────
// Test: SQL injection - accounts_action uses prepared statements
// ──────────────────────────────────────────────
function test_no_sql_concatenation(): void {
    $content = file_get_contents(__DIR__ . '/../admin/accounts_action.php');
    assert_true($content !== false, 'accounts_action.php readable');
    // Should NOT have direct concatenation with $accountId in SQL
    assert_true(!preg_match('/WHERE\s+\w+\s*=\s*"\s*\.\s*\$accountId/', $content), 'No direct SQL concatenation with $accountId');
}

// ──────────────────────────────────────────────
// Test: Modal system JS exists
// ──────────────────────────────────────────────
function test_modal_system_exists(): void {
    $content = file_get_contents(__DIR__ . '/../assets/js/modals.js');
    assert_true($content !== false, 'modals.js exists');
    assert_contains($content, 'SaprastsToast', 'modals.js exports SaprastsToast');
    assert_contains($content, 'SaprastsConfirm', 'modals.js exports SaprastsConfirm');
}

// ──────────────────────────────────────────────
// Test: No native alert() left in main JS files
// ──────────────────────────────────────────────
function test_no_native_alerts(): void {
    $jsFiles = [
        __DIR__ . '/../assets/js/footer_ui.js',
        __DIR__ . '/../assets/js/appointments.js',
        __DIR__ . '/../assets/js/availability.js',
        __DIR__ . '/../assets/js/articles.js',
        __DIR__ . '/../admin/messages.js',
    ];

    foreach ($jsFiles as $file) {
        $name = basename($file);
        $content = file_get_contents($file);
        assert_true($content !== false, "{$name} readable");
        assert_true(!preg_match('/\balert\s*\(/', $content), "No alert() in {$name}");
        assert_true(!preg_match('/window\.confirm\s*\(/', $content), "No window.confirm() in {$name}");
    }
}

// ──────────────────────────────────────────────
// Test: Header has notification bell
// ──────────────────────────────────────────────
function test_header_has_bell(): void {
    $content = file_get_contents(__DIR__ . '/../includes/header.php');
    assert_true($content !== false, 'header.php readable');
    assert_contains($content, 'notification-bell-btn', 'Header has notification bell button');
    assert_contains($content, 'notification-dropdown', 'Header has notification dropdown');
}

// ──────────────────────────────────────────────
// Run all tests
// ──────────────────────────────────────────────
$tests = [
    'test_env_loader',
    'test_encryption_roundtrip',
    'test_encryption_tamper_detection',
    'test_db_connection',
    'test_db_tables_exist',
    'test_ai_handler_no_hardcoded_key',
    'test_checkout_no_hardcoded_key',
    'test_no_sql_concatenation',
    'test_modal_system_exists',
    'test_no_native_alerts',
    'test_header_has_bell',
];

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo " Saprasts — Automated Tests\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

foreach ($tests as $testFn) {
    try {
        $testFn();
        $passed++;
        echo "  ✓ {$testFn}\n";
    } catch (Throwable $e) {
        $failed++;
        $errors[] = "{$testFn}: {$e->getMessage()}";
        echo "  ✗ {$testFn}: {$e->getMessage()}\n";
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo " Results: {$passed} passed, {$failed} failed\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($failed > 0) {
    echo "\nFailures:\n";
    foreach ($errors as $err) {
        echo "  - {$err}\n";
    }
    exit(1);
}
exit(0);
