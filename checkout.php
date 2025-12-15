<?php
require 'vendor/autoload.php'; // Ielādē Stripe bibliotēku
// Šeit ievieto savu Secret Key no Stripe paneļa
\Stripe\Stripe::setApiKey('sk_test_TAVS_SECRET_KEY_SHEIT');

header('Content-Type: application/json');

// Mēs sagaidām datus no POST pieprasījuma (Cena un Nosaukums)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $psihologs_vards = $_POST['psihologs_vards'];
    $cena_h = $_POST['cena']; // Cenai jābūt skaitlim

    // Stripe prasa cenu centos (piemēram, 50.00 EUR -> 5000 centi)
    $cena_centos = intval($cena_h * 100);

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
            // Kur novirzīt pēc veiksmīgas vai neveiksmīgas apmaksas
            'success_url' => 'http://localhost/saprasts/success.php',
            'cancel_url' => 'http://localhost/saprasts/dashboard.php',
        ]);

        // Pārsūtam lietotāju uz Stripe maksājumu lapu
        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
    } catch (Error $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>