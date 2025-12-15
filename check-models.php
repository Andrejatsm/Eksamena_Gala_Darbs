<?php
// check_models.php - Pārbauda pieejamos Google AI modeļus
header('Content-Type: text/plain');

$apiKey = 'AIzaSyBpYXI6x2fkNNSGDUk4FbI_L0qFhg-FsVY'; 
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // XAMPP fix
$response = curl_exec($ch);

if ($response === false) {
    die('Kļūda savienojumā: ' . curl_error($ch));
}
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['error'])) {
    die('API Kļūda: ' . $data['error']['message']);
}

echo "PIEEJAMIE MODEĻI TAVAI ATSLĒGAI:\n";
echo "=================================\n";

if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        // Mēs meklējam tikai tos modeļus, kas atbalsta 'generateContent' (čatu)
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            // Noņemam "models/" priekšā, lai iegūtu tīru ID
            $cleanName = str_replace("models/", "", $model['name']);
            echo "Modelis: " . $cleanName . "\n";
            echo "Apraksts: " . $model['description'] . "\n";
            echo "---------------------------------\n";
        }
    }
} else {
    echo "Netika atrasts neviens modelis. Dīvaini. Pārbaudi API atslēgas tiesības.";
}
?>