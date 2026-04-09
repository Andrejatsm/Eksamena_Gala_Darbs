<?php
// 1. Sākam sesiju, lai varētu nodot informāciju uz success.php
session_start();

require __DIR__ . '/../vendor/autoload.php'; // Stripe
require __DIR__ . '/../includes/db.php'; // Datubāzes pieslēgums

// Stripe API atslēga no vides mainīgā (.env fails).
$stripeKey = getenv('STRIPE_SECRET_KEY') ?: '';
if ($stripeKey === '') {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe nav konfigurēts.']);
    exit();
}
\Stripe\Stripe::setApiKey($stripeKey);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'user') {
        header("Location: ../auth/login.php");
        exit();
    }

    $psihologs_vards = trim((string)($_POST['psihologs_vards'] ?? ''));
    $cena_h = 50.00; // Fiksēta sesijas cena
    $psychologist_account_id = isset($_POST['psychologist_account_id']) ? (int)$_POST['psychologist_account_id'] : 0;
    $slot_id = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;

    // Pārbaudām, vai lietotājs maksā tieši par esošu un konkrētam psihologam piederošu slotu.
    if ($slot_id > 0) {
        $stmt = $conn->prepare(
            "SELECT starts_at, ends_at, consultation_type, is_booked
             FROM availability_slots
             WHERE id = ? AND psychologist_account_id = ?"
        );
        $stmt->bind_param("ii", $slot_id, $psychologist_account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($slot = $result->fetch_assoc()) {
            // Pārbaudām vai slots jau ir rezervēts (cits lietotājs to nopircis)
            if ((int)$slot['is_booked'] === 1) {
                http_response_code(409);
                echo json_encode(['error' => 'Šis laika slots jau ir rezervēts.']);
                exit();
            }
            // Pārbaudām vai šis pats lietotājs jau ir pierakstījies uz šo laiku
            $dupStmt = $conn->prepare(
                "SELECT id FROM appointments
                 WHERE user_account_id = ? AND psychologist_account_id = ? AND scheduled_at = ?
                 AND status NOT IN ('cancelled') LIMIT 1"
            );
            $dupStmt->bind_param("iis", $_SESSION['account_id'], $psychologist_account_id, $slot['starts_at']);
            $dupStmt->execute();
            $dupExists = $dupStmt->get_result()->num_rows > 0;
            $dupStmt->close();
            if ($dupExists) {
                http_response_code(409);
                echo json_encode(['error' => 'Jūs jau esat pierakstīts uz šo laiku.']);
                exit();
            }
            $_SESSION['booking_slot_id'] = $slot_id;
            $_SESSION['booking_scheduled_at'] = $slot['starts_at'];
            $_SESSION['booking_consultation_type'] = $slot['consultation_type'] ?? 'online';
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Nederīgs laika slots']);
            exit();
        }
        $stmt->close();
    }

    // Normalizējam psihologa ID arī tad, ja frontend to nav nodevis tieši.
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
        $psychologist_account_id = isset($_SESSION['last_paid_psychologist_account_id'])
            ? (int)$_SESSION['last_paid_psychologist_account_id']
            : 0;
    }

    if ($psychologist_account_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Neizdevās noteikt psihologu šim maksājumam.']);
        exit();
    }

    // Stripe summu pieņem mazākajās naudas vienībās, tāpēc 50.00 EUR pārvēršam par 5000 centiem.
    $cena_centos = intval((float)$cena_h * 100);

    // URL veidojam dinamiski no pašreizējās instalācijas vietas, lai checkout strādā arī pēc mapju pārvietošanas.
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $baseUrl = $scheme . '://' . $host . $basePath;
    $rootUrl = $scheme . '://' . $host . rtrim(dirname($basePath), '/\\');

    // Nodrošinām tikai slīpsvītras uz priekšu (Stripe neatbalsta backslash URL).
    $success_base_url = str_replace('\\', '/', $baseUrl . '/success.php');
    $success_url = $success_base_url . '?session_id={CHECKOUT_SESSION_ID}';
    $cancel_url = str_replace('\\', '/', $rootUrl . '/pages/dashboard.php');

    // Pirms sūtām uz Stripe, pārliecināmies, ka pāradresācijas adreses tiešām ir derīgi URL.
    if (!filter_var($success_base_url, FILTER_VALIDATE_URL) || !filter_var($cancel_url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Nederīgs URL',
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
        ]);
        exit();
    }

    try {
        // Stripe izveido vienreizēju checkout sesiju un atgriež adresi, uz kuru lietotāju pārsūtām apmaksai.
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
            'client_reference_id' => (string)((int)$_SESSION['account_id']),
            'metadata' => [
                'user_account_id' => (string)((int)$_SESSION['account_id']),
                'psychologist_account_id' => (string)$psychologist_account_id,
                'slot_id' => (string)$slot_id,
                'consultation_type' => (string)($_SESSION['booking_consultation_type'] ?? 'online'),
                'scheduled_at' => (string)($_SESSION['booking_scheduled_at'] ?? ''),
            ],
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
        ]);

        // Saglabājam checkout sesijas ID kā papildu server-side verifikācijas enkuru success lapai.
        $_SESSION['last_checkout_session_id'] = (string)($checkout_session->id ?? '');

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