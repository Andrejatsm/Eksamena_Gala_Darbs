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
$sql = "SELECT p.full_name, p.specialization, p.description
        FROM psychologist_profiles p
        INNER JOIN accounts a ON a.id = p.account_id
        WHERE a.role = 'psychologist' AND a.status = 'active' AND p.approved_at IS NOT NULL";
$result = $conn->query($sql);
$doctors_text = "";
if ($result) {
    while($row = $result->fetch_assoc()) {
        $doctors_text .= $row['full_name'] . " (" . $row['specialization'] . "), ";
    }
}

// TAVA API ATSLĒGA
$apiKey = 'AIzaSyABkRLH7hJExQCMXA4OMwOjzjgNPzMcIIc'; 

$prompt = "
Tu esi 'Saprasts' virtuālais asistents - draudzīgs un profesionāls AI palīgs psihiskās veselības platformā.

**Tava loma:**
- Palīdzēt lietotājiem atrast piemērotu psihologu
- Atbildēt uz jautājumiem par platformu
- Sniegt atbalstu emocionālos jautājumos
- Veicināt pozitīvu un konfidenciālu komunikāciju

**Pieejamie psihologi:**
" . $doctors_text . "

**Platformas iespējas:**
- Anonīmas konsultācijas
- Tiešsaistes un klātienes tikšanās
- Pašnovērtējuma testi
- Raksti un resursi
- 24/7 AI atbalsts

**Saites:**
- Reģistrācija: register.php
- Ielogošanās: login.php
- Sākumlapa: index.php
- Speciālistu meklēšana: fetch_psychologists.php

**Norādījumi:**
- Atbildi latviski, ja lietotājs raksta latviski
- Būt laipnam, empātiskam un profesionālam
- Ja lietotājs izsaka emocionālas grūtības, iesaki konkrētu speciālistu no saraksta
- Piedāvā reģistrēties vai ielogoties, ja nepieciešams
- Nekad neizdomā saites vai informāciju
- Ja nevari palīdzēt, piedāvā sazināties ar administratoru

Lietotāja ziņojums: '$userMessage'
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