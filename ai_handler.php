<?php
require 'db.php';
header('Content-Type: application/json');

// 1. Saņemam datus
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Lūdzu, ievadiet jautājumu.']);
    exit;
}

// 2. Iegūstam ārstus kontekstam
$sql = "SELECT vards_uzvards, specializacija, apraksts FROM psychologists";
$result = $conn->query($sql);
$doctors_text = "";
if ($result) {
    while($row = $result->fetch_assoc()) {
        $doctors_text .= $row['vards_uzvards'] . " (" . $row['specializacija'] . "), ";
    }
}

// TAVA API ATSLĒGA
$apiKey = 'AIzaSyBpYXI6x2fkNNSGDUk4FbI_L0qFhg-FsVY'; 

// --- ŠEIT IR GALVENĀS IZMAIŅAS (Gudrāks Prompts) ---
// Mēs iedodam AI "kontekstu" par lapas saitēm un noteikumiem.
$prompt = "
Tu esi vietnes 'Saprasts' virtuālais asistents.
Tavā rīcībā ir šāda informācija:
1. Pieejamie psihologi: [$doctors_text]
2. Vietnes saites:
   - Reģistrācija: <a href='register.php'>register.php</a>
   - Ielogošanās: <a href='login.php'>login.php</a>
   - Sākumlapa: <a href='index.php'>index.php</a>

Tavi uzdevumi:
- Ja lietotājs sūdzas par pašsajūtu, iesaki KONKRĒTU speciālistu no saraksta.
- Ja lietotājs jautā, kā pieteikties, reģistrēties vai ielogoties, iedod atbilstošo saiti no saraksta.
- Atbildi īsi, laipni un latviski vari arī angliski.
- Nekad neizdomā saites, kas nav sarakstā.

Lietotāja jautājums: '$userMessage'
";

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "safetySettings" => [
        ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE"],
        ["category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE"],
        ["category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE"],
        ["category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE"]
    ]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(['reply' => 'Kļūda savienojumā.']);
    exit;
}
curl_close($ch);

$json = json_decode($response, true);

// Kļūdu apstrāde
if (isset($json['error'])) {
    // Ja modelis pārslogots, pasakām to lietotājam saprotamāk
    if (strpos($json['error']['message'], 'overloaded') !== false) {
        echo json_encode(['reply' => 'AI serveri pašlaik ir pārslogoti. Lūdzu, mēģiniet vēlreiz pēc minūtes.']);
    } else {
        echo json_encode(['reply' => 'Tehniska kļūda: ' . $json['error']['message']]);
    }
    exit;
}

$aiReply = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'Neizdevās saņemt atbildi.';
echo json_encode(['reply' => $aiReply]);
?>