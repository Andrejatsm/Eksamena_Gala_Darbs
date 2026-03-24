<?php
require 'db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$message = $input['message'] ?? '';

if (empty($email) || empty($message)) {
    echo json_encode(['message' => 'Nepilnīgi dati.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
$name = 'Anonīms'; // Since no name field
$stmt->bind_param("sss", $name, $email, $message);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Ziņa nosūtīta veiksmīgi!']);
} else {
    echo json_encode(['message' => 'Kļūda saglabājot ziņu.']);
}

$stmt->close();
$conn->close();
?>