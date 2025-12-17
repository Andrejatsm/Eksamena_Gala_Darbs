<?php
// 1. Sākam sesiju, lai varētu nodot informāciju uz success.php
session_start();

require 'vendor/autoload.php'; // Stripe
require 'db.php'; // Datubāzes pieslēgums

// Tavi Stripe atslēgas iestatījumi
\Stripe\Stripe::setApiKey('sk_test_51S7XQt3b1XY7a31CCbstqHPSNYoEFGXr5zcqQaaB5t25CYs3mFuYzOl1GB9jQ0Hzh7MJC8Gc1XneHycmqUYbqn5O00hVcvezVP');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $psihologs_vards = $_POST['psihologs_vards'];
    $cena_h = $_POST['cena']; 

    // 2. Iegūstam Psihologa ID no datubāzes pēc vārda
    // Tas ir nepieciešams, lai success.php zinātu, kam izveidot pieteikumu
    $stmt = $conn->prepare("SELECT id FROM psychologists WHERE vards_uzvards = ?");
    $stmt->bind_param("s", $psihologs_vards);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Saglabājam ID sesijā - šo nolasīs success.php
        $_SESSION['last_paid_psychologist_id'] = $row['id'];
    }
    $stmt->close();

    // 3. Stripe Apmaksas loģika
    $cena_centos = intval((float)$cena_h * 100); // Pārvēršam uz centiem

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
            'success_url' => 'http://localhost/saprasts/success.php',
            'cancel_url' => 'http://localhost/saprasts/dashboard.php',
        ]);

        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
        
    } catch (Exception $e) { // Izmantojam Exception, lai noķertu Stripe kļūdas
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>