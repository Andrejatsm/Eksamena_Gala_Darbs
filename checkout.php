<?php
// 1. Sākam sesiju, lai varētu nodot informāciju uz success.php
session_start();

require __DIR__ . '/vendor/autoload.php'; // Stripe
require __DIR__ . '/db.php'; // Datubāzes pieslēgums

// Tavi Stripe atslēgas iestatījumi
\Stripe\Stripe::setApiKey('sk_test_51S7XQt3b1XY7a31CCbstqHPSNYoEFGXr5zcqQaaB5t25CYs3mFuYzOl1GB9jQ0Hzh7MJC8Gc1XneHycmqUYbqn5O00hVcvezVP');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
        header("Location: login.php");
        exit();
    }

    $psihologs_vards = $_POST['psihologs_vards'];
    $cena_h = 50.00; // Fixed session price 
    $psychologist_account_id = isset($_POST['psychologist_account_id']) ? (int)$_POST['psychologist_account_id'] : 0;
    $slot_id = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;

    // 2. Iegūstam slot details un validējam
    if ($slot_id > 0) {
        $stmt = $conn->prepare("
            SELECT starts_at, ends_at 
            FROM availability_slots 
            WHERE id = ? AND psychologist_account_id = ?
        ");
        $stmt->bind_param("ii", $slot_id, $psychologist_account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($slot = $result->fetch_assoc()) {
            $_SESSION['booking_slot_id'] = $slot_id;
            $_SESSION['booking_scheduled_at'] = $slot['starts_at'];
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid slot']);
            exit();
        }
        $stmt->close();
    }

    // Store psychologist info in session
    if ($psychologist_account_id > 0) {
        $_SESSION['last_paid_psychologist_account_id'] = $psychologist_account_id;
    } else {
        $stmt = $conn->prepare("SELECT account_id FROM psychologist_profiles WHERE full_name = ?");
        $stmt->bind_param("s", $psihologs_vards);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $_SESSION['last_paid_psychologist_account_id'] = (int)$row['account_id'];
        }
        $stmt->close();
    }

    // 3. Stripe Apmaksas loģika
    $cena_centos = intval((float)$cena_h * 100); // Pārvēršam uz centiem

    // Build redirect URLs based on current host and install path.
    // Use SCRIPT_NAME to avoid any URL fragments or query strings.
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $baseUrl = $scheme . '://' . $host . $basePath;

    // Ensure we're always using forward slashes (Stripe rejects backslashes).
    $success_url = str_replace('\\', '/', $baseUrl . '/success.php');
    $cancel_url = str_replace('\\', '/', $baseUrl . '/dashboard.php');

    // Validate URL strings before sending to Stripe (Stripe rejects invalid URLs)
    if (!filter_var($success_url, FILTER_VALIDATE_URL) || !filter_var($cancel_url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Not a valid URL',
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
        ]);
        exit();
    }

    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $cena_centos,
                    'product_data' => [
                        'name' => 'Konsultācija: ' . $psihologs_vards,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
        ]);

        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
        
    } catch (Exception $e) { // Izmantojam Exception, lai noķertu Stripe kļūdas
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
        ]);
    }
}
?>